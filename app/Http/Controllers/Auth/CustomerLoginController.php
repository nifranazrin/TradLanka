<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.customer-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('web')->attempt($credentials)) {
            if (Auth::user()->user_role === '0') {
                return redirect()->intended('/user/dashboard');
            }
            Auth::logout();
            return back()->withErrors(['email' => 'Unauthorized role.']);
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        return redirect('/login');
    }
}