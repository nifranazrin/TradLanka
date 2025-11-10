<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

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
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/home';
   public function redirectTo()
{
    $user = Auth::user();

    switch ($user->user_role) {
        case '0': // Customer
            return '/user/dashboard';
        case '1': // Seller
            return '/seller/dashboard';
        case '2': // Admin
            return '/admin/dashboard';
        case '3': // Delivery Person
            return '/delivery/dashboard';
        default:
            return '/home';
    }
}


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        
    }
}
