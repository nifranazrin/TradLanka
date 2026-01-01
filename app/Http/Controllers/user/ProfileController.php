<?php

namespace App\Http\Controllers\User;
use Illuminate\Support\Facades\DB;
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
      /**
     * Update Address (Handle Form Submit)
     */
    public function updateAddress(Request $request)
    {
        // 1. Validate all fields including the new ones
        $request->validate([
            'address1' => 'required|string|max:500',
            'address2' => 'nullable|string|max:255',
            'city'     => 'required|string|max:255',
            'state'    => 'required|string|max:255',
            'zipcode'  => 'required|string|max:20',
            'country'  => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
        ]);

        $user = Auth::user();

        // 2. Save all fields to the user profile
        $user->address1 = $request->address1;
        $user->address2 = $request->address2;
        $user->city     = $request->city;
        $user->state    = $request->state;
        $user->zipcode  = $request->zipcode;
        $user->country  = $request->country;
        $user->phone    = $request->phone;
        
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

    public function cancelOrder($id)
{
    $user = Auth::user(); //
    
    // 1. Find the order only if it belongs to this user and is in a cancellable stage
    // Cancellable Stages: 0 (Placed), 1 (Received), 2 (Packed)
    $order = Order::where('id', $id)
        ->where('user_id', $user->id)
        ->whereIn('status', ['0', '1', '2']) 
        ->first();

    // 2. If the order is already at status 4 (Head Office) or above, deny the request
    if (!$order) {
        return redirect()->back()->with('error', 'Order cannot be cancelled at this stage. It may already be at the Head Office or shipped.');
    }

    try {
        // 3. Update Status to 7 (Cancellation Requested)
        // NOTE: We do NOT restore stock here. Stock is restored ONLY after Admin final approval.
        $order->status = '7';
        $order->save();

        // 4. ✅ OPTIONAL: Notify the Seller here so they see it in their dashboard
        // Notification::send($order->seller, new \App\Notifications\CancellationRequest($order));

        return redirect()->back()->with('status', 'Your cancellation request has been sent to the Seller for approval.');
        
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Something went wrong. Please try again later.');
    }
}
}