<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Staff;

class SellerMiddleware
{
    public function handle($request, Closure $next)
    {
        // Check if user session exists
        if (session()->has('staff_id')) {
            $staff = Staff::find(session('staff_id'));

            // Make sure user is a seller
            if ($staff && strtolower($staff->role) === 'seller') {
                return $next($request);
            }
        }

        // If not seller → redirect to login page
        return redirect()->route('staff.login')->with('error', 'Access denied. Seller only.');
    }
}
