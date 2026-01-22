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
        
        if (!Auth::guard('delivery')->check()) {
          
            return redirect()->route('staff.login')
                ->with('error', 'Please login as delivery staff.');
        }

        
        if (strtolower(Auth::guard('delivery')->user()->role) !== 'delivery') {
            return redirect()->route('staff.login')
                ->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}