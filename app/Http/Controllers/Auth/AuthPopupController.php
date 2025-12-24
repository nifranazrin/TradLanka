<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Assuming your customer model is User
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthPopupController extends Controller
{
    // 1. Handle Login from Popup
    public function login(Request $request)
    {
        // Validate
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt Login (Standard Web Guard)
        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful!'
            ]);
        }

        // Fail
        return response()->json([
            'message' => 'The provided credentials do not match our records.'
        ], 422);
    }

    // 2. Handle Registration from Popup
 public function register(Request $request)
    {
        // Validate
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                
                // CHANGED: 'min:8' -> 'min:3' (Easier for testing)
                'password' => 'required|string|min:3|confirmed', 
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

       

        // Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Login Immediately
        Auth::login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful!'
        ]);
    }
}