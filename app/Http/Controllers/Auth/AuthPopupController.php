<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
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
        // Validate credentials
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt Login (Standard Web Guard)
        if (Auth::attempt($credentials, $request->remember)) {
            // Security: Regenerate session
            $request->session()->regenerate();

            /** * ✅ Check if this login was triggered by an "Add to Cart" action */
            $isCartIntent = session()->has('add_to_cart_pid');

            return response()->json([
                'status' => 'success',
                'message' => $isCartIntent ? 'Login successful & Item added to cart!' : 'Welcome back!',
                'is_cart_login' => $isCartIntent // ✅ Send this flag to the frontend
            ]);
        }

        // FAIL: Return consistent error status for AJAX catch block
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
        // Validate
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                // Keep min:3 for your testing environment
                'password' => 'required|string|min:3|confirmed', 
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors()
            ], 422);
        }

        // Create User matching your database schema
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_role' => 0, // ✅ 0 = Customer
        ]);

        // Login Immediately and regenerate session
        Auth::login($user);
        $request->session()->regenerate();

        /** * ✅ ADDED: Check intent for new registrations so they get the right message */
        $isCartIntent = session()->has('add_to_cart_pid');

        return response()->json([
            'status' => 'success',
            'message' => $isCartIntent ? 'Account created & Item added to cart!' : 'Registration successful!',
            'is_cart_login' => $isCartIntent // ✅ Send this flag to the frontend
        ]);
    }
}