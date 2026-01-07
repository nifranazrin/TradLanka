<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Exception;

class CartController extends Controller
{
    /**
     * 1. ADD TO CART
     * Handles both AJAX and standard form submissions
     */
    public function addProduct(Request $request)
    {
        $product_id         = $request->input('product_id');
        $product_qty        = $request->input('product_qty');
        $product_variant_id = $request->input('product_variant_id'); 

        // ✅ Check if customer is authenticated
        if (Auth::check()) {
            $user_id = Auth::id();
            $prod_check = Product::where('id', $product_id)->first();

            if ($prod_check) {
                // Check if this specific product/variant combo is already in this user's cart
                $cartItem = Cart::where('product_id', $product_id)
                                ->where('product_variant_id', $product_variant_id)
                                ->where('user_id', $user_id)
                                ->first();

                if ($cartItem) {
                    // Update quantity of existing item
                    $cartItem->product_qty += $product_qty;
                    $cartItem->save();

                    $message = $prod_check->name . ' quantity updated in cart!';
                } else {
                    // Create new cart entry
                    $cartItem = new Cart();
                    $cartItem->product_id         = $product_id;
                    $cartItem->user_id            = $user_id;
                    $cartItem->product_qty        = $product_qty;
                    $cartItem->product_variant_id = $product_variant_id;
                    $cartItem->save();

                    $message = $prod_check->name . ' added to cart!';
                }

                // Get the total number of unique items for the header badge
                $newCartCount = Cart::where('user_id', $user_id)->count();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status'     => 'success',
                        'message'    => $message,
                        'cart_count' => $newCartCount
                    ]);
                } else {
                    return redirect()->back()->with('status', $message);
                }
            }
        } else {
            // ✅ Handle Guest status for the AJAX popup
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'guest',
                    'url'    => route('login')
                ]);
            } else {
                return redirect()->route('login')->with('status', 'Please login to add items to cart');
            }
        }
    }

    /**
     * 2. VIEW CART PAGE
     */
    public function viewCart()
    {
        // Eager load products and variants to prevent errors if a product is missing
        $cartItems = Cart::where('user_id', Auth::id())
                         ->with(['product', 'variant']) 
                         ->latest()
                         ->get();

        return view('frontend.cart', compact('cartItems'));
    }

    /**
     * 3. UPDATE QUANTITY (Used in Cart Page)
     */
    public function updateCart(Request $request)
    {
        if (Auth::check()) {
            $cart_id = $request->input('cart_id');
            $qty     = $request->input('qty');

            $cart = Cart::where('id', $cart_id)
                        ->where('user_id', Auth::id())
                        ->first();

            if ($cart) {
                $cart->product_qty = $qty;
                $cart->save();
                return response()->json(['status' => 'success']);
            }
        }

        return response()->json(['status' => 'error'], 403);
    }

    public function saveIntent(Request $request) 
{
    // Save to session so AuthPopupController can find it later
    session([
        'add_to_cart_pid' => $request->product_id,
        'add_to_cart_qty' => $request->product_qty,
        'add_to_cart_vid' => $request->product_variant_id ?? null
    ]);

    return response()->json(['status' => 'intent_saved']);
}

    /**
     * 4. DELETE ITEMS (Supports Bulk Delete)
     */
    public function deleteCart(Request $request)
    {
        if (Auth::check()) {
            $ids = $request->input('ids');

            if (is_array($ids) && count($ids) > 0) {
                Cart::whereIn('id', $ids)
                    ->where('user_id', Auth::id())
                    ->delete();

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Items removed from cart successfully'
                ]);
            }
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Unable to delete items'
        ], 400);
    }
}