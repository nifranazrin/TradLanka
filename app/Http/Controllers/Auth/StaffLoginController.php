<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Staff;

class StaffLoginController extends Controller
{
    /**
     * Show staff login form (Admin / Seller / Delivery)
     */
    public function showLoginForm()
    {
        return view('auth.staff-login');
    }

    /**
     * Handle login request
     */
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

        // ✅ Proper password check using Hash::check()
        if (!Hash::check($request->password, $staff->password)) {
            return back()->with('error', 'Incorrect password.');
        }

        // ✅ Added: check if inactive account
        if (strtolower($staff->role) === 'seller' && $staff->status !== 'active') {
            return back()->with('error', 'Your account is not active. Contact admin.');
        }

        // ✅ Determine guard based on role
        $guard = match (strtolower($staff->role)) {
            'admin'    => 'admin',
            'seller'   => 'seller',
            'delivery' => 'delivery',
            default    => 'web',
        };

        // ✅ Log in via correct guard
        Auth::guard($guard)->login($staff);

        // ✅ Regenerate session for security
        $request->session()->regenerate();

        // ✅ Store useful session data
        session([
            'staff_id'   => $staff->id,
            'staff_name' => $staff->name,
            'staff_role' => strtolower($staff->role),
        ]);

        // Clear other cached role session values
        session()->forget(['seller_name', 'delivery_name']);

        // ✅ Redirect to the proper dashboard
        return match ($guard) {
            'admin'    => redirect()->route('admin.dashboard')->with('success', 'Welcome, Admin!'),
            'seller'   => redirect()->route('seller.dashboard')->with('success', 'Welcome, Seller!'),
            'delivery' => redirect()->route('delivery.dashboard')->with('success', 'Welcome, Delivery Person!'),
            default    => redirect()->route('staff.login')->with('error', 'Invalid role: ' . $staff->role),
        };
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        // ✅ Logout from all possible guards
        foreach (['admin', 'seller', 'delivery', 'web'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        // ✅ Clear and regenerate session
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('staff.login')->with('success', 'Logged out successfully.');
    }
}
