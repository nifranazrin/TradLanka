<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use App\Models\Category; 
use App\Models\Order;
use App\Models\Message;
use Illuminate\Pagination\Paginator;
use App\Observers\OrderObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Order::observe(OrderObserver::class);

        Paginator::useBootstrapFive();
        // 1. Existing Category Logic (Global)
        View::composer('*', function ($view) {
            $view->with('globalCategories', Category::whereNull('parent_id')
                ->with('subcategories')
                ->get());
        });

      

// 2. SELLER NOTIFICATION COUNTS & DETAILED BELL DATA
\Illuminate\Support\Facades\View::composer('*', function ($view) {
    if (\Illuminate\Support\Facades\Auth::guard('seller')->check()) {
        $seller = \Illuminate\Support\Facades\Auth::guard('seller')->user();
        $sellerId = $seller->id;

        

        // --- 1. DETAILED DATA FOR DROPDOWN (Recent First) ---
        $latestOrdersNotify = \App\Models\Order::whereHas('items.product', function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            })->whereIn('status', [0, 1])->latest()->take(3)->get();

        $latestProductsNotify = $seller->unreadNotifications()
            ->where('data->type', 'product')->latest()->take(3)->get();

        $latestInquiriesNotify = \Illuminate\Support\Facades\DB::table('contact_messages')
            ->where('status', 'pending')
            ->where(function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId)->orWhereNull('seller_id');
            })->latest()->take(3)->get();

        // FIXED: Fetch real Chat messages instead of empty collect()
        $latestChatsNotify = \App\Models\Message::where('receiver_id', $sellerId)
            ->where('receiver_type', 'seller')
            ->where('is_read', 0)->latest()->take(3)->get();

        // FIXED: Fetch real Reviews instead of empty collect()
         $latestReviewsNotify = \App\Models\Review::whereHas('product', function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            })
            ->where('status', 1)
            ->whereDate('created_at', \Carbon\Carbon::today()) // ✅ Simple: Only count today's reviews
            ->latest()->take(3)->get();


        // --- 2. COUNTS FOR SIDEBAR BADGES ---
        $productCount = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('notifiable_id', $sellerId)->whereNull('read_at')
            ->where('data', 'like', '%"type":"product"%')->count();

        $inquiryCount = \Illuminate\Support\Facades\DB::table('contact_messages')->where('status', 'pending')->count();
        $chatCount    = $latestChatsNotify->count();
        $reviewCount  = $latestReviewsNotify->count();
        $orderCount   = $latestOrdersNotify->count();

        $total = $productCount + $orderCount + $inquiryCount + $chatCount + $reviewCount;

        $view->with([
            'latestOrdersNotify'    => $latestOrdersNotify,
            'latestProductsNotify'  => $latestProductsNotify,
            'latestInquiriesNotify' => $latestInquiriesNotify,
            'latestReviewsNotify'   => $latestReviewsNotify,
            'latestChatsNotify'     => $latestChatsNotify,
            'notif_counts' => [
                'product' => $productCount,
                'order'   => $orderCount,
                'inquiry' => $inquiryCount,
                'chat'    => $chatCount,
                'reviews' => $reviewCount,
                'total'   => $total
            ],
            'totalAlerts' => $total
        ]);
    }
});

        // 3. NEW: Delivery Person Notification Counts & Bell Data
View::composer('layouts.delivery', function ($view) {
    if (Auth::guard('delivery')->check()) {
        $riderId = Auth::guard('delivery')->id();

        // --- SIDEBAR COUNTS (Keep your existing logic) ---
        $activeCount = Order::where('delivery_boy_id', $riderId)
            ->whereIn('status', [4, 10])
            ->count();

        $historyCount = Order::where('delivery_boy_id', $riderId)
            ->whereIn('status', [6, 9])
            ->where('rider_seen', 0)
            ->count();

        $unreadMessagesCount = Message::where('receiver_id', $riderId)
            ->where('receiver_type', 'delivery')
            ->where('is_read', 0)
            ->count();

        // --- BELL NOTIFICATION LIST (New logic for the dropdown) ---
        // Get unread orders
        $unreadOrders = Order::where('delivery_boy_id', $riderId)
            ->where('rider_seen', 0)
            ->whereIn('status', [4, 6, 9, 10])
            ->latest()
            ->get();

        // Get unread messages
        $unreadChatMessages = Message::where('receiver_id', $riderId)
            ->where('receiver_type', 'delivery')
            ->where('is_read', 0)
            ->latest()
            ->get();

        // Merge both into a single collection for the bell
        $allNotifications = collect();
        
        foreach($unreadOrders as $order) {
            $allNotifications->push([
                'type' => in_array($order->status, [4, 10]) ? 'task' : 'alert',
                'title' => in_array($order->status, [4, 10]) ? 'New Task Assigned' : 'Admin Finalized Order',
                'body' => "Order #$order->tracking_no",
                'url' => in_array($order->status, [4, 10]) ? route('delivery.my-deliveries') : route('delivery.task-history')
            ]);
        }

        foreach($unreadChatMessages as $msg) {
            $allNotifications->push([
                'type' => 'chat',
                'title' => 'New Message',
                'body' => \Illuminate\Support\Str::limit($msg->message, 40),
                'url' => route('delivery.chat.index') // Adjust to your chat route name
            ]);
        }

        $view->with([
            'delivery_active_count'     => $activeCount,
            'delivery_history_count'    => $historyCount,
            'delivery_unread_messages'  => $unreadMessagesCount,
            'bellNotifications'         => $allNotifications, // For the bell dropdown
            'totalNotificationCount'    => $allNotifications->count() // For the bell badge
        ]);
    }
});



// 4. ADMIN NOTIFICATION COUNTS & DETAILED BELL DATA
\Illuminate\Support\Facades\View::composer('layouts.admin', function ($view) {
    // Standardize admin detection
    $admin = \Illuminate\Support\Facades\Auth::guard('admin')->user() 
             ?? \App\Models\Staff::find(session('staff_id'));
    
    // --- 1. DETAILED DATA FOR DROPDOWN (With Eager Loading) ---
    $latestOrdersNotify = \App\Models\Order::where('status', 0)->latest()->take(3)->get();
    
    $latestProductsNotify = \App\Models\Product::whereIn('status', ['pending', 'reapproval_pending'])
                                                ->latest()->take(3)->get();
    
    // Eager load 'user' to pull the reviewer's name instead of blank
    $latestReviewsNotify = \App\Models\Review::with('user')->where('status', 0)
                                              ->latest()->take(3)->get();
                                              
    $latestSellerRequestsNotify = \App\Models\UserRequest::where('status', 'pending')
                                                         ->latest()->take(3)->get();

    $latestChatsNotify = collect();
    if ($admin) {
        // Eager load 'sender' (Staff) specifically
        $latestChatsNotify = \App\Models\Message::with('sender')
            ->where('receiver_id', $admin->id)
            ->where('receiver_type', 'admin')
            ->where('is_read', 0)
            ->latest()->take(3)->get();
    }

    // --- 2. COUNTS FOR BADGES ---
    $pendingApplications = \App\Models\UserRequest::where('status', 'pending')->count();
    $pendingProducts     = \App\Models\Product::whereIn('status', ['pending', 'reapproval_pending'])->count();
    $newReviews          = \App\Models\Review::where('status', 0)->count();
    $pendingOrders       = \App\Models\Order::where('status', 0)->count();
    $pendingReports      = \Illuminate\Support\Facades\DB::table('submitted_reports')
                                ->where('status', 'pending')->count();

    // Pass variables globally to fix 500 errors on other pages
    $view->with([
        'admin'               => $admin,
        'pendingApplications' => $pendingApplications,
        'pendingProducts'     => $pendingProducts,
        'newReviews'          => $newReviews, 
        'unreadMessages'      => $latestChatsNotify->count(),
        'pendingOrders'       => $pendingOrders,
        'pendingReports'      => $pendingReports,
        
        'latestOrdersNotify'  => $latestOrdersNotify,
        'latestProductsNotify'=> $latestProductsNotify,
        'latestReviewsNotify' => $latestReviewsNotify,
        'latestSellerRequestsNotify' => $latestSellerRequestsNotify,
        'latestChatsNotify'   => $latestChatsNotify,
        
        'totalAlerts'         => $pendingApplications + $pendingProducts + $newReviews + 
                                 $latestChatsNotify->count() + $pendingOrders + $pendingReports
    ]);
});


// 5. USER PENDING REVIEWS COUNT (Global for Sidebar)
\Illuminate\Support\Facades\View::composer(['layouts.frontend', 'user.profile.*'], function ($view) {
    if (Auth::check()) {
        $userId = Auth::id();

        $toReviewCount = \App\Models\OrderItem::whereHas('order', function ($query) use ($userId) {
                $query->where('user_id', $userId)->where('status', 5); 
            })
            ->whereHas('product') //  ONLY count if the product still exists in the DB
            ->get()
            ->filter(function ($item) use ($userId) {
                return !\App\Models\Review::where('user_id', $userId)
                    ->where('product_id', $item->product_id)
                    ->where('created_at', '>=', $item->created_at) 
                    ->exists();
            })->count();

        $view->with('toReviewCount', $toReviewCount);
    }
});



    }
}