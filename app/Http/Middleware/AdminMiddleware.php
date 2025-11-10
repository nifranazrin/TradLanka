<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        $staff = Auth::guard('admin')->user();

        // Fallback to session-based system if guard not used
        if (!$staff && session()->has('staff_id')) {
            $staff = Staff::find(session('staff_id'));
        }

        // Allow only if role is admin
        if ($staff && strtolower($staff->role) === 'admin') {
            return $next($request);
        }

        return redirect()->route('staff.login')->with('error', 'Access denied. Admins only.');
    }
}
