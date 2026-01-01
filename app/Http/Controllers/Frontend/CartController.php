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
     */
     public function addProduct(Request $request)
    {
        $product_id         = $request->input('product_id');
        $product_qty        = $request->input('product_qty');
        $product_variant_id = $request->input('product_variant_id'); 

        // ✅ KEEP YOUR ORIGINAL LOGIN CHECK
        if (Auth::check()) {
            $user_id = Auth::id();
            $prod_check = Product::where('id', $product_id)->first();

            if ($prod_check) {
                
                // ✅ WISHLIST LOGIC: Check if stock is 0
                // If 0, it becomes a Heart item (Wishlist)
                $isWishlist = ($prod_check->quantity <= 0) ? 1 : 0;

                // Match both Product ID AND Variant ID
                $cartItem = Cart::where('product_id', $product_id)
                                ->where('product_variant_id', $product_variant_id)
                                ->where('user_id', $user_id)
                                ->first();

                if ($cartItem) {
                    // Update existing item
                    $cartItem->product_qty += $product_qty;
                    $cartItem->is_wishlist = $isWishlist; // Update status
                    $cartItem->save();

                    $message = $isWishlist ? $prod_check->name . ' wishlist updated!' : $prod_check->name . ' quantity updated!';
                } else {
                    // Create new item
                    $cartItem = new Cart();
                    $cartItem->product_id         = $product_id;
                    $cartItem->user_id            = $user_id;
                    $cartItem->product_qty        = $product_qty;
                    $cartItem->product_variant_id = $product_variant_id;
                    $cartItem->is_wishlist        = $isWishlist; // ✅ SAVE THE HEART STATUS
                    $cartItem->save();

                    $message = $isWishlist ? $prod_check->name . ' added to Wishlist!' : $prod_check->name . ' added to cart!';
                }

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
            // ✅ KEEP YOUR ORIGINAL POPUP TRIGGER
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
        $cartItems = Cart::where('user_id', Auth::id())
                         ->with(['product', 'variant']) 
                         ->latest()
                         ->get();

        return view('frontend.cart', compact('cartItems'));
    }

    /**
     * 3. UPDATE QUANTITY
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

    /**
     * 4. DELETE ITEMS
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
                    'message' => 'Items deleted successfully'
                ]);
            }
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Something went wrong'
        ], 400);
    }
}