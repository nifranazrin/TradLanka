<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cart; // ✅ Added this to access your carts table
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthPopupController extends Controller
{
    /**
     * 1. Handle Login from Popup
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            // Check for intent BEFORE regenerating session
            $isCartIntent = session()->has('add_to_cart_pid');
            $user = Auth::user();

            // ✅ CRITICAL: Actually save the item to the DB if intent exists
            if ($isCartIntent) {
                $this->syncCartItem($user->id);
            }

            $request->session()->regenerate();

            return response()->json([
                'status' => 'success',
                'message' => $isCartIntent ? 'Welcome Back! Item successfully added to your cart.' : 'Welcome back!',
                'is_cart_login' => $isCartIntent 
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'The provided credentials do not match our records.'
        ], 422);
    }

    /**
     * 2. Handle Registration from Popup
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:3|confirmed', 
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_role' => 0, 
        ]);

        Auth::login($user);

        // Check for intent
        $isCartIntent = session()->has('add_to_cart_pid');

        // ✅ Actually save the item for the new user
        if ($isCartIntent) {
            $this->syncCartItem($user->id);
        }

        $request->session()->regenerate();

        return response()->json([
            'status' => 'success',
            'message' => $isCartIntent ? 'Account created & Item added to cart!' : 'Registration successful!',
            'is_cart_login' => $isCartIntent 
        ]);
    }

    /**
     * ✅ PRIVATE HELPER: Sync Session Item to Database
     * This ensures the database is updated immediately upon login/register
     */
    private function syncCartItem($userId)
    {
        $pid = session('add_to_cart_pid');
        $qty = session('add_to_cart_qty', 1);
        $vid = session('add_to_cart_vid');

        // Check if item already exists in DB cart for this user to avoid duplicates
        $existing = Cart::where('user_id', $userId)
                        ->where('product_id', $pid)
                        ->where('product_variant_id', $vid)
                        ->first();

        if ($existing) {
            $existing->product_qty += $qty;
            $existing->save();
        } else {
            Cart::create([
                'user_id' => $userId,
                'product_id' => $pid,
                'product_qty' => $qty,
                'product_variant_id' => $vid
            ]);
        }

        // IMPORTANT: Clear session now that it is safely in the database
        session()->forget(['add_to_cart_pid', 'add_to_cart_qty', 'add_to_cart_vid']);
    }
}