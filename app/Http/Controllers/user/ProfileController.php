<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;

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

        // Calculate pending reviews for the sidebar badge
        $toReviewCount = $this->getPendingReviewsCount($user->id);

        // Fetch latest 5 orders with products loaded for the image display
        $orders = Order::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->with('orderItems.product') 
            ->get();

        return view('user.profile.index', compact('user', 'orders', 'toReviewCount'));
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
            'address2' => 'nullable|string|max:255',
            'city'     => 'required|string|max:255',
            'state'    => 'required|string|max:255',
            'zipcode'  => 'required|string|max:20',
            'country'  => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
        ]);

        $user = Auth::user();
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

        // Calculate pending reviews for the sidebar badge
        $toReviewCount = $this->getPendingReviewsCount($user->id);

        $orders = Order::where('user_id', $user->id)
            ->latest()
            ->with('orderItems.product')
            ->get();

        return view('user.profile.orders', compact('user', 'orders', 'toReviewCount'));
    }

    /**
     * View Single Order Details
     */
    public function viewOrder($id)
    {
        $user = Auth::user();

        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->with('orderItems.product') 
            ->first();

        if (!$order) {
            return redirect()->route('user.orders.index')->with('error', 'Order not found');
        }

        return view('user.profile.order-details', compact('user', 'order'));
    }

    /**
     * Handle Order Cancellation Request
     */
    public function cancelOrder($id)
    {
        $user = Auth::user();
        
        // Find cancellable order (Status 0, 1, or 2)
        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['0', '1', '2']) 
            ->first();

        if (!$order) {
            return redirect()->back()->with('error', 'Order cannot be cancelled at this stage.');
        }

        try {
            $order->status = '7'; // Cancellation Requested
            $order->save();

            return redirect()->back()->with('status', 'Your cancellation request has been sent for approval.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong. Please try again later.');
        }
    }

    /**
     * Helper: Count items in Delivered orders that have no review from this user
     */
  private function getPendingReviewsCount($userId)
{
    return OrderItem::whereHas('order', function ($query) use ($userId) {
            $query->where('user_id', $userId)->where('status', '5'); 
        })
        // This is the fix: It ignores OrderItems where the product is deleted
        ->whereHas('product') 
        ->whereDoesntHave('product.reviews', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->count();
}
}