<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // 1. ADD TO CART
    public function addProduct(Request $request)
    {
        $product_id         = $request->input('product_id');
        $product_qty        = $request->input('product_qty');
        // ✅ NEW: Get the variant/size ID (it might be null for simple products)
        $product_variant_id = $request->input('product_variant_id'); 

        if (Auth::check()) {
            $user_id = Auth::id();
            $prod_check = Product::where('id', $product_id)->first();

            if ($prod_check) {

                // ✅ UPDATED CHECK: Match both Product ID AND Variant ID
                // If I have "Tea 100g" in cart, and I add "Tea 1kg", it should be a NEW row.
                $cartItem = Cart::where('product_id', $product_id)
                                ->where('product_variant_id', $product_variant_id) // <--- CRITICAL CHANGE
                                ->where('user_id', $user_id)
                                ->first();

                if ($cartItem) {
                    // Update existing item quantity
                    $cartItem->product_qty += $product_qty;
                    $cartItem->save();

                    $message = $prod_check->name . ' quantity updated!';
                } else {
                    // Create new item
                    $cartItem = new Cart();
                    $cartItem->product_id         = $product_id;
                    $cartItem->user_id            = $user_id;
                    $cartItem->product_qty        = $product_qty;
                    $cartItem->product_variant_id = $product_variant_id; // ✅ SAVE THE SIZE ID
                    $cartItem->save();

                    $message = $prod_check->name . ' added to cart!';
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
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'guest',
                    'url'    => route('login')
                ]);
            } else {
                return redirect()->route('login')
                    ->with('status', 'Please login to add items to cart');
            }
        }
    }

    // 2. VIEW CART PAGE
    public function viewCart()
    {
        $cartItems = Cart::where('user_id', Auth::id())
                         ->with(['product', 'variant']) // ✅ LOAD VARIANT DATA
                         ->latest()
                         ->get();

        return view('frontend.cart', compact('cartItems'));
    }

    // 3. UPDATE QUANTITY
    public function updateCart(Request $request)
    {
        $cart_id = $request->input('cart_id');
        $qty     = $request->input('qty');

        if (Auth::check()) {
            $cart = Cart::where('id', $cart_id)
                        ->where('user_id', Auth::id())
                        ->first();

            if ($cart) {
                $cart->product_qty = $qty;
                $cart->save();
                return response()->json(['status' => 'success']);
            }
        }

        return response()->json(['status' => 'error']);
    }

    // 4. DELETE ITEMS
    public function deleteCart(Request $request)
    {
        $ids = $request->input('ids');

        if (Auth::check()) {
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
        ]);
    }
}