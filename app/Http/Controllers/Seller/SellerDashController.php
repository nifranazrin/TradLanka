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
use Illuminate\Support\Facades\DB;


class SellerDashController extends Controller
{
    /**
     * Apply seller auth middleware
     */
    public function __construct()
    {
        $this->middleware('auth:seller');
    }

public function dashboard()
    {
        $sellerId = Auth::guard('seller')->id();

        // 1. Product Statistics (Including Approved & Rejected breakdown)
        $productQuery = Product::where('seller_id', $sellerId);
        
        $totalProducts = (clone $productQuery)->count();
        $approvedProducts = (clone $productQuery)->whereIn('status', ['approved', 'active', 'reapproved'])->count();
        $rejectedProducts = (clone $productQuery)->where('status', 'rejected')->count();

        // 2. Orders Today Breakdown (LKR vs USD)
        $todayQuery = Order::whereHas('items.product', function ($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })->whereDate('created_at', today());

        $ordersToday = (clone $todayQuery)->count();
        $ordersTodayLocal = (clone $todayQuery)->where('currency', 'LKR')->count();
        $ordersTodayForeign = (clone $todayQuery)->where('currency', 'USD')->count();

        // 3. Pending Deliveries Breakdown (Status 1, 2, 3)
        $pendingQuery = Order::whereHas('items.product', function ($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        })->whereIn('status', [1, 2, 3]);

        $pendingDeliveries = (clone $pendingQuery)->count();
        $pendingLocal = (clone $pendingQuery)->where('currency', 'LKR')->count();
        $pendingForeign = (clone $pendingQuery)->where('currency', 'USD')->count();

        // 4. Total Orders Breakdown (LKR vs USD)
        $ordersQuery = Order::whereHas('items.product', function ($q) use ($sellerId) {
            $q->where('seller_id', $sellerId);
        });

        $totalOrders = (clone $ordersQuery)->count();
        $localOrders = (clone $ordersQuery)->where('currency', 'LKR')->count();
        $foreignOrders = (clone $ordersQuery)->where('currency', 'USD')->count();

        // 5. Recent Orders for Table
        $recentOrders = (clone $ordersQuery)->latest()->take(5)->get();

        // 6. Top Selling Products (Sum of units sold)
        $topProducts = Product::where('seller_id', $sellerId)
            ->withCount(['orderItems as total_sold' => function($query) {
                $query->select(DB::raw('IFNULL(sum(qty), 0)')); 
            }])
            ->orderBy('total_sold', 'desc')->take(5)->get();

        return view('seller.dashboard', compact(
            'totalProducts', 'approvedProducts', 'rejectedProducts',
            'ordersToday', 'ordersTodayLocal', 'ordersTodayForeign',
            'pendingDeliveries', 'pendingLocal', 'pendingForeign',
            'totalOrders', 'localOrders', 'foreignOrders',
            'recentOrders', 'topProducts'
        ));
    }

    /**
     * AJAX DATA for Lively Charts & Monthly View
     */
    public function getChartData(Request $request)
    {
        $sellerId = Auth::guard('seller')->id();
        $viewType = $request->query('view', '8days'); 
        
        $baseOrderQuery = Order::whereHas('items.product', function($q) use($sellerId){
            $q->where('seller_id', $sellerId);
        });

        $labels = []; $total = []; $success = []; $canceled = [];
        $iterations = ($viewType === 'monthly') ? 30 : 8;

        for ($i = ($iterations - 1); $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M d'); // Admin style: "Jan 12"
            
            $dayQuery = (clone $baseOrderQuery)->whereDate('created_at', $date->format('Y-m-d'));
            
            $total[] = (clone $dayQuery)->count();
            $success[] = (clone $dayQuery)->where('status', 5)->count(); // Status 5 = Success
            $canceled[] = (clone $dayQuery)->where('status', 6)->count(); // Status 6 = Canceled
        }

        return response()->json([
            // Product Breakdown
            'totalProducts' => Product::where('seller_id', $sellerId)->count(),
            'approvedProducts' => Product::where('seller_id', $sellerId)->whereIn('status', ['approved', 'active', 'reapproved'])->count(),
            'rejectedProducts' => Product::where('seller_id', $sellerId)->where('status', 'rejected')->count(),
            
            // Order Today Breakdown (Added Local/Foreign)
            'ordersToday' => (clone $baseOrderQuery)->whereDate('created_at', today())->count(),
            'ordersTodayLocal' => (clone $baseOrderQuery)->whereDate('created_at', today())->where('currency', 'LKR')->count(),
            'ordersTodayForeign' => (clone $baseOrderQuery)->whereDate('created_at', today())->where('currency', 'USD')->count(),
            
            // Pending Breakdown
            'pendingDeliveries' => (clone $baseOrderQuery)->whereIn('status', [1, 2, 3])->count(),
            'pendingLocal' => (clone $baseOrderQuery)->whereIn('status', [1, 2, 3])->where('currency', 'LKR')->count(),
            'pendingForeign' => (clone $baseOrderQuery)->whereIn('status', [1, 2, 3])->where('currency', 'USD')->count(),
            
            // Total Order Breakdown
            'totalOrders' => (clone $baseOrderQuery)->count(),
            'localOrders' => (clone $baseOrderQuery)->where('currency', 'LKR')->count(),
            'foreignOrders' => (clone $baseOrderQuery)->where('currency', 'USD')->count(),
            
            // Pie Chart Data
            'pie' => [
                'processing' => (clone $baseOrderQuery)->where('status', 0)->count(),
                'shipped' => (clone $baseOrderQuery)->where('status', 1)->count(),
                'delivered' => (clone $baseOrderQuery)->where('status', 5)->count()
            ],
            
            // Triple Line Chart Data
            'line' => [
                'labels' => $labels,
                'total' => $total,
                'success' => $success,
                'canceled' => $canceled
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
