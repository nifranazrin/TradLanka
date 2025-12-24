<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class ProfileController extends Controller
{
    /**
     * Force customer authentication
     */
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    /**
     * Profile dashboard
     */
    public function index()
    {
        $user = Auth::user();

        $orders = Order::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->with('orderItems')
            ->get();

        return view('user.profile.index', compact('user', 'orders'));
    }

    /**
     * Edit personal profile (Show Form)
     */
    public function editProfile()
    {
        $user = Auth::user();
        return view('user.profile.edit-profile', compact('user'));
    }

    /**
     * Update personal profile (Handle Form Submit)
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Address book (Show Form)
     */
    public function address()
    {
        $user = Auth::user();
        return view('user.profile.address', compact('user'));
    }

    /**
     * Update Address (Handle Form Submit)
     */
    public function updateAddress(Request $request)
    {
        $request->validate([
            'address1' => 'required|string|max:500',
            'city'     => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
        ]);

        $user = Auth::user();
        $user->address1 = $request->address1;
        $user->city = $request->city;
        $user->phone = $request->phone;
        $user->save();

        return redirect()->back()->with('success', 'Address updated successfully!');
    }

    /**
     * All orders page
     */
    public function orders()
    {
        $user = Auth::user();

        $orders = Order::where('user_id', $user->id)
            ->latest()
            ->with('orderItems')
            ->get();

        return view('user.profile.orders', compact('user', 'orders'));
    }

    /**
     * View Single Order Details
     * ✅ Added this method to fix your "View Details" button
     */
    public function viewOrder($id)
    {
        $user = Auth::user();

        // Fetch order only if it belongs to the logged-in user
        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->with('orderItems.product') // Load items and products
            ->first();

        if (!$order) {
            return redirect()->route('user.orders.index')->with('error', 'Order not found');
        }

        return view('user.profile.order-details', compact('user', 'order'));
    }
}