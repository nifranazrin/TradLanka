<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\InquiryReplyMail;


class SellerDashController extends Controller
{
    /**
     * Apply seller auth middleware
     */
    public function __construct()
    {
        $this->middleware('auth:seller');
    }

    /**
     * ===============================
     * Seller Dashboard (Statistics)
     * ===============================
     */
    public function dashboard()
    {
        $sellerId = Auth::guard('seller')->id();

        // Total Products owned by seller
        $totalProducts = Product::where('seller_id', $sellerId)->count();

        // Orders Today (Filtered by seller's products in the order)
        $ordersToday = Order::whereHas('items.product', function ($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })->whereDate('created_at', today())->count();

        // Pending Deliveries (Status 1, 2, or 3)
        $pendingDeliveries = Order::whereHas('items.product', function ($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })->whereIn('status', [1, 2, 3])->count();

        // Monthly Revenue (Delivered only - Status 4)
        $monthlyRevenue = Order::where('status', 4)
            ->whereHas('items.product', function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            })->whereMonth('created_at', now()->month)
            ->sum('total_price');

        // Recent Orders for the table
        $recentOrders = Order::whereHas('items.product', function ($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })->latest()->take(5)->get();

        // Initial Chart Placeholders
        $chartLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $chartData   = [0, 0, 0, 0, 0, 0, 0];

        return view('seller.dashboard', compact(
            'totalProducts',
            'ordersToday',
            'pendingDeliveries',
            'monthlyRevenue',
            'recentOrders',
            'chartLabels',
            'chartData'
        ));
    }

    /**
     * LIVE AJAX DATA for Lively Charts and Cards
     * This ensures the dashboard updates without refreshing
     */
    public function getChartData()
{
    $sellerId = Auth::guard('seller')->id();

    // Pie Chart Data
    $processing = Order::where('status', 0)->whereHas('items.product', function($q) use($sellerId){
        $q->where('seller_id', $sellerId);
    })->count();
    $shipped = Order::where('status', 1)->whereHas('items.product', function($q) use($sellerId){
        $q->where('seller_id', $sellerId);
    })->count();
    $delivered = Order::where('status', 4)->whereHas('items.product', function($q) use($sellerId){
        $q->where('seller_id', $sellerId);
    })->count();

    // Line Chart Data (Last 7 Days)
    $labels = []; $sales = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i);
        $labels[] = $date->format('D');
        $sales[] = Order::whereDate('created_at', $date->format('Y-m-d'))
            ->whereHas('items.product', function($q) use($sellerId){
                $q->where('seller_id', $sellerId);
            })->count();
    }

    return response()->json([
        'totalProducts'     => Product::where('seller_id', $sellerId)->count(),
        'ordersToday'       => Order::whereDate('created_at', today())->whereHas('items.product', function($q) use($sellerId){ $q->where('seller_id', $sellerId); })->count(),
        'pendingDeliveries' => Order::whereIn('status', [1, 2, 3])->whereHas('items.product', function($q) use($sellerId){ $q->where('seller_id', $sellerId); })->count(),
        'monthlyRevenue'    => number_format(Order::where('status', 4)->whereMonth('created_at', now()->month)->whereHas('items.product', function($q) use($sellerId){ $q->where('seller_id', $sellerId); })->sum('total_price'), 2),
        'pie' => [
            'processing' => $processing,
            'shipped'    => $shipped,
            'delivered'  => $delivered
        ],
        'line' => [
            'labels' => $labels,
            'data'   => $sales
        ]
    ]);
}
    /**
     * =====================================
     * Seller Inquiries (Global + Claimed)
     * =====================================
     */
       public function inquiries()
{
    $sellerId = Auth::guard('seller')->id();

    $inquiries = ContactMessage::where(function ($query) use ($sellerId) {
            $query->where('seller_id', $sellerId)
                  ->orWhereNull('seller_id');
        })
        // Keeps Pending at the top
        ->orderByRaw("CASE WHEN status = 'pending' THEN 1 ELSE 2 END ASC")
        ->latest()
        ->paginate(10); // Use paginate to enable the < 1 2 3 > buttons

    return view('seller.inquiries.index', compact('inquiries'));
}

//Reviews

public function reviews(Request $request)
{
    $sellerId = Auth::guard('seller')->id();
    $starFilter = $request->input('rating');

    // Filter reviews belonging to this seller
    $query = \App\Models\Review::whereHas('product', function ($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })
        ->with(['product', 'user']) // Eager load for product slug and name
        ->latest();

    // Apply Star Rating Filter
    if ($starFilter) {
        $query->where('rating', $starFilter);
    }

    // Fetch ALL results as requested
    $reviews = $query->get();

    return view('seller.reviews.index', compact('reviews'));
}
    /**
     * ===============================
     * Mark ALL notifications as read
     * ===============================
     */
    public function markAllRead()
    {
        $seller = Auth::guard('seller')->user();

        if ($seller) {
            $seller->unreadNotifications->markAsRead();
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * ======================================
     * Mark SINGLE notification as read
     * ======================================
     */
    public function readNotification($id)
{
    $seller = Auth::guard('seller')->user();

    if (!$seller) {
        abort(403);
    }

    // ✅ Correct way to get notification
    $notification = $seller->notifications
        ->where('id', $id)
        ->firstOrFail();

    // Mark as read
    $notification->markAsRead();

    // Redirect based on type
    $type = $notification->data['type'] ?? null;

    switch ($type) {
        case 'message':
        case 'chat':
            return redirect()->route('seller.chat.index');

        case 'inquiry':
            return redirect()->route('seller.inquiries');

        case 'order':
            return redirect()->route('seller.orders.index');

        case 'product':
            return redirect()->route('seller.products.index');

        default:
            return redirect()->back();
    }
}



    /**
     * ======================================
     * Reply to Inquiry (CLAIMS inquiry)
     * ======================================
     */
    public function replyToInquiry(Request $request, $id)
    {
        $request->validate([
            'reply_message' => 'required|string|min:3',
        ]);

        $sellerId = Auth::guard('seller')->id();

        $inquiry = ContactMessage::where('id', $id)
            ->where(function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId)
                  ->orWhereNull('seller_id');
            })
            ->firstOrFail();

        // Prevent double reply
        if ($inquiry->status === 'replied') {
            return redirect()->back()
                ->with('error', 'This inquiry has already been replied.');
        }

        try {
            Mail::to($inquiry->email)->send(
                new InquiryReplyMail(
                    $request->reply_message,
                    $inquiry->first_name
                )
            );
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Email could not be sent. Please try again.');
        }

        // Claim & update inquiry
        $inquiry->update([
            'seller_id'     => $sellerId,
            'status'        => 'replied',
            'reply_message' => $request->reply_message,
            'replied_at'    => now(),
        ]);

        return redirect()->back()->with('success', 'Reply sent successfully!');
    }

    /**
     * ======================================
     * Manually mark inquiry as replied
     * ======================================
     */
    public function markReplied($id)
    {
        $sellerId = Auth::guard('seller')->id();

        $message = ContactMessage::where('id', $id)
            ->where(function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId)
                  ->orWhereNull('seller_id');
            })
            ->firstOrFail();

        $message->update([
            'seller_id'  => $sellerId,
            'status'     => 'replied',
            'replied_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Inquiry marked as replied!');
    }


   
}
