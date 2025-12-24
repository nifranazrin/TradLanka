<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Where to redirect users after login.
     *
     * @return string
     */
    public function redirectTo()
    {
        $user = Auth::user();

        switch ($user->user_role) {
            case '1': // Seller
                return '/seller/dashboard';
            
            case '2': // Admin
                return '/admin/dashboard';
            
            case '3': // Delivery Person
                return '/delivery/dashboard';
            
            case '0': // Customer
            default:
                // CORRECTED: Send customers to the Home/Shop page so they can continue buying.
                // Do NOT send them to '/user/dashboard' immediately.
                return '/'; 
        }
    }
}