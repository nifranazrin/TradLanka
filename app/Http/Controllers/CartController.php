<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class CartController extends Controller
{
     // Show the cart page
     
    public function showCart()
    {
        $cart = session()->get('cart', []);
        return view('cart.view', compact('cart'));
    }

    //Add a product to cart (AJAX)
    
    public function addToCart(Request $request)
    {
        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found.',
            ]);
        }

        $cart = session()->get('cart', []);

        // If product already in cart, increase quantity
        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += 1;
        } else {
            // Otherwise, add new item
            $cart[$product->id] = [
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->image,
                'quantity' => 1,
            ];
        }

        // Save to session
        session()->put('cart', $cart);

        return response()->json([
            'status' => 'success',
            'message' => $product->name . ' added to your cart!',
            'cartCount' => count($cart),
        ]);
    }

   
    // Remove one item from cart
   
    public function removeFromCart($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        // For normal (non-AJAX) request
        if (request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Item removed.',
                'cartCount' => count($cart),
            ]);
        }

        return redirect()->back()->with('success', 'Item removed from cart.');
    }

    // Clear the entire cart
     
    public function clearCart()
    {
        session()->forget('cart');

        if (request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Cart cleared.',
                'cartCount' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'Cart cleared successfully.');
    }
}
