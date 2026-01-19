<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Display the form to request a password reset link for Customers.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        // This ensures the customer sees the Orange/Gold theme 
        // located in your frontend folder instead of the Staff maroon theme.
        return view('frontend.auth.passwords.email');
    }

    /**
     * Constant path to redirect users after they have requested a reset link.
     */
    protected function redirectTo()
    {
        return route('home');
    }
}