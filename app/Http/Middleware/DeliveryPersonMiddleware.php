<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DeliveryPersonMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. MUST check the 'delivery' guard because that is what your 
        // StaffLoginController uses to log in the rider.
        if (!Auth::guard('delivery')->check()) {
            // ✅ MANUALLY redirect to staff login to stop the customer login redirect.
            return redirect()->route('staff.login')
                ->with('error', 'Please login as delivery staff.');
        }

        // 2. Double-check the role for extra security
        if (strtolower(Auth::guard('delivery')->user()->role) !== 'delivery') {
            return redirect()->route('staff.login')
                ->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}