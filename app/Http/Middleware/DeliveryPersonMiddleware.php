<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class DeliveryPersonMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::guard('delivery')->check() && Auth::guard('delivery')->user()->user_role == '3') {
            return $next($request);
        }

        return redirect('/staff/login')->with('error', 'You are not allowed to access this page.');
    }
}

