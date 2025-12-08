<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Staff;

class StaffLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.staff-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $staff = Staff::where('email', $request->email)->first();

        if (!$staff) {
            return back()->with('error', 'Email not found.');
        }

        // Check password correctly (hashed)
        if (!Hash::check($request->password, $staff->password)) {
            return back()->with('error', 'Incorrect password.');
        }

        //  Seller-specific login restriction
        if (strtolower($staff->role) === 'seller') {
            $status = strtolower($staff->status);

            // Allow login only if status is 'active' or 'approved'
            if (!in_array($status, ['active', 'approved'])) {
                return back()->with('error', 'Your account is not active. Please contact admin.');
            }
        }

        //  Determine the correct guard
        $guard = match (strtolower($staff->role)) {
            'admin'    => 'admin',
            'seller'   => 'seller',
            'delivery' => 'delivery',
            default    => 'web',
        };

        //  Log in through correct guard
        Auth::guard($guard)->login($staff);

        //  Secure session regeneration
        $request->session()->regenerate();

        //  Store session info
        session([
            'staff_id'   => $staff->id,
            'staff_name' => $staff->name,
            'staff_role' => strtolower($staff->role),
        ]);

        // Clear irrelevant session values
        session()->forget(['seller_name', 'delivery_name']);

        //  Redirect by role
        return match ($guard) {
            'admin'    => redirect()->route('admin.dashboard')->with('success', 'Welcome, Admin!'),
            'seller'   => redirect()->route('seller.dashboard')->with('success', 'Welcome, Seller!'),
            'delivery' => redirect()->route('delivery.dashboard')->with('success', 'Welcome, Delivery Person!'),
            default    => redirect()->route('staff.login')->with('error', 'Invalid role: ' . $staff->role),
        };
    }

    public function logout()
    {
        // Logout from all guards
        foreach (['admin', 'seller', 'delivery', 'web'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        // Clear session securely
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('staff.login')->with('success', 'Logged out successfully.');
    }
}
