<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Staff;
use App\Notifications\SellerDashboardNotification;
use App\Notifications\CustomerOrderNotification;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;


class CheckoutController extends Controller
{
    // Centralized exchange rate (1 LKR = 0.0032 USD)
    protected $exchangeRate = 0.0032;

    public function index()
    {
        $selectedIds = session('checkout_cart_ids');

        if (!$selectedIds || !is_array($selectedIds) || count($selectedIds) === 0) {
            return redirect()->route('cart.show')->with('error', 'Please select items to checkout.');
        }

         $user = Auth::user(); 

    $cartItems = Cart::where('user_id', Auth::id())
        ->whereIn('id', $selectedIds)
        ->with(['product', 'variant'])
        ->get();

    return view('frontend.checkout', compact('cartItems', 'user'));
}

    public function placeOrder(Request $request)
    {
        // 1. VALIDATION (Added address2 to prevent errors)
        $request->validate([
            'fname'        => 'required|string|max:255',
            'lname'        => 'nullable|string|max:255',
            'email'        => 'required|email|max:255',
            'phone'        => 'required|string|min:8|max:20', 
            'full_phone'   => 'required|string', 
            'country'      => 'required|string', 
            'address1'     => 'required|string|max:255',
            'address2'     => 'nullable|string|max:255', 
            'city'         => 'required|string|max:255',
            'state'        => 'required|string|max:255',
            'zipcode'      => 'required|string|max:20',
            'message'      => 'nullable|string|max:1000',
            'payment_mode' => 'required|string',
        ]);

        $selectedIds = session('checkout_cart_ids');
        $currency = session('currency', 'LKR');

        // 2. DELIVERY LOGIC
        $deliveryChargeLKR = ($request->country === 'Sri Lanka') ? 500 : 5000;

        // 3. FETCH CART
       // 3. FETCH CART
$selectedIds = session('checkout_cart_ids');

// --- THE SAFETY GATE ---
if (!$selectedIds || !is_array($selectedIds)) {
    return redirect()->route('cart.show')->with('error', 'Your session has expired. Please re-select your items.');
}

$cartItems = Cart::where('user_id', Auth::id())
    ->whereIn('id', $selectedIds)
    ->with(['product', 'variant'])
    ->get();

if ($cartItems->isEmpty()) {
    return redirect()->route('cart.show')->with('error', 'Your cart is empty.');
}

        $productTotalLKR = 0;
        $stripeLineItems = [];

        foreach ($cartItems as $item) {
            $priceLKR = $item->variant ? $item->variant->price : $item->product->price;
            $productTotalLKR += ($priceLKR * $item->product_qty);

            $imageUrl = $item->product->image ? url('storage/' . $item->product->image) : null;

            // STRIPE UNIT PRICE (Crucial: Stripe only takes integers/cents)
            $unitAmount = ($currency === 'USD') 
                ? (int)round(($priceLKR * $this->exchangeRate) * 100) 
                : (int)round($priceLKR * 100);

            $stripeLineItems[] = [
                'price_data' => [
                    'currency' => strtolower($currency),
                    'product_data' => [
                        'name' => $item->product->name . ($item->variant ? ' (' . $item->variant->unit_label . ')' : ''),
                        'images' => $imageUrl ? [$imageUrl] : [],
                    ],
                    'unit_amount' => $unitAmount,
                ],
                'quantity' => $item->product_qty,
            ];
        }

        // 4. DELIVERY FOR STRIPE
        $deliveryStripe = ($currency === 'USD') 
            ? (int)round(($deliveryChargeLKR * $this->exchangeRate) * 100) 
            : (int)round($deliveryChargeLKR * 100);

        $stripeLineItems[] = [
            'price_data' => [
                'currency' => strtolower($currency),
                'product_data' => ['name' => 'Shipping & Delivery Fee'],
                'unit_amount' => $deliveryStripe,
            ],
            'quantity' => 1,
        ];

        $grandTotalLKR = $productTotalLKR + $deliveryChargeLKR;

        // --- STRIPE GATEWAY ---
        if ($request->payment_mode == 'Stripe') {
            try {
                Stripe::setApiKey(env('STRIPE_SECRET'));
                $checkout_session = Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => $stripeLineItems, 
                    'mode' => 'payment',
                    'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('checkout.index'),
                    'metadata' => [
                        'user_id' => Auth::id(),
                        'request_data' => json_encode($request->all()), 
                        'cart_ids' => json_encode($selectedIds),
                        'paid_currency' => $currency
                    ]
                ]);
                return redirect()->away($checkout_session->url);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Stripe Error: ' . $e->getMessage());
            }
        }
        
        // --- COD GATEWAY ---
        return $this->saveOrder($request->all(), $selectedIds, $grandTotalLKR, 'COD', $currency);
    }

    protected function saveOrder($formData, $selectedIds, $total, $paymentId = null, $currency = 'LKR')
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $user->update([
                'fname'    => $formData['fname'],
                'lname'    => $formData['lname'] ?? $user->lname,
                'phone'    => $formData['full_phone'] ?? $formData['phone'],
                'address1' => $formData['address1'],
                'address2' => $formData['address2'] ?? null,
                'city'     => $formData['city'],
                'state'    => $formData['state'],
                'zipcode'  => $formData['zipcode'],
                'country'  => $formData['country'],
            ]);

            $order = Order::create([
                'user_id'      => Auth::id(),
                'fname'        => $formData['fname'],
                'lname'        => $formData['lname'] ?? null,
                'email'        => $formData['email'],
                'phone'        => $formData['full_phone'] ?? $formData['phone'],
                'country'      => $formData['country'],
                'address1'     => $formData['address1'],
                'address2'     => $formData['address2'] ?? null,
                'city'         => $formData['city'],
                'state'        => $formData['state'],
                'zipcode'      => $formData['zipcode'],
                'message'      => $formData['message'] ?? null,
                'total_price'  => $total,
                'payment_mode' => ($paymentId && $paymentId !== 'COD') ? "Stripe ($currency)" : "COD ($currency)",
                'tracking_no'  => 'TRAD-' . strtoupper(uniqid()),
                'payment_id'   => ($paymentId && $paymentId !== 'COD') ? $paymentId : null,
                'status'       => 0,
                'currency'     => $currency,
            ]);

            Auth::user()->notify(new CustomerOrderNotification($order));

           
             // ✅ WRAP THIS IN A TRY-CATCH TO PREVENT CRASHES
                try {
                    // We load the relationships here to ensure data is ready
                    $orderData = $order->load(['items.product', 'items.variant']);
                    \Illuminate\Support\Facades\Mail::to($order->email)->send(new \App\Mail\OrderConfirmation($orderData));
                } catch (\Exception $e) {
                    // If the mail server is slow or fails, we log it but DON'T stop the order
                    \Illuminate\Support\Facades\Log::error("Order Mail Error: " . $e->getMessage());
                }


            $cartItems = Cart::whereIn('id', $selectedIds)->with(['product', 'variant'])->get();
            $sellersToNotify = [];

            foreach ($cartItems as $item) {
                $priceLKR = $item->variant ? $item->variant->price : $item->product->price;
                
                // Save price based on currency paid
                $finalItemPrice = ($currency === 'USD') 
                                    ? ($priceLKR * $this->exchangeRate) 
                                    : $priceLKR;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id ?? null,
                    'qty'        => $item->product_qty,
                    'price'      => $finalItemPrice,
                ]);

                $item->product->decrement('stock', $item->product_qty);

                if ($item->product->seller_id) { 
                    $sellersToNotify[] = $item->product->seller_id; 
                }
            }

            foreach (array_unique($sellersToNotify) as $id) {
                $seller = Staff::find($id);
                if ($seller) { 
                    $seller->notify(new SellerDashboardNotification('order', 'New order: ' . $order->tracking_no, $order->id)); 
                }
            }

            Cart::whereIn('id', $selectedIds)->delete();
            session()->forget(['checkout_cart_ids']);
            DB::commit();

            return redirect()->route('home')->with('order_success', true);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Order Error: " . $e->getMessage());
            // This will show you exactly what went wrong instead of just redirecting
            return redirect()->route('cart.show')->with('error', 'Order failed: ' . $e->getMessage());
        }
    }

    public function stripeSuccess(Request $request)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $session = Session::retrieve($request->session_id);
            
            $formData = json_decode($session->metadata->request_data, true);
            $selectedIds = json_decode($session->metadata->cart_ids, true);
            $paidCurrency = $session->metadata->paid_currency ?? 'USD';

            return $this->saveOrder($formData, $selectedIds, ($session->amount_total / 100), $session->payment_intent, $paidCurrency);
        } catch (\Exception $e) {
            return redirect()->route('cart.show')->with('error', 'Payment Verification Failed.');
        }
    }

}