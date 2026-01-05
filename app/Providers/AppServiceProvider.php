<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; 
use Illuminate\Support\Facades\Auth; 
use App\Models\Category; 
use App\Models\Order;
use App\Models\Message;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {

        Paginator::useBootstrapFive();
        // 1. Existing Category Logic (Global)
        View::composer('*', function ($view) {
            $view->with('globalCategories', Category::whereNull('parent_id')
                ->with('subcategories')
                ->get());
        });

        // 2. Existing Seller Notification Counts
        View::composer('*', function ($view) {
            if (Auth::guard('seller')->check()) {
                $seller = Auth::guard('seller')->user();
                $unread = $seller->unreadNotifications;

                $view->with('notif_counts', [
                    'products'  => $unread->where('data.type', 'product')->count(),
                    'orders'    => $unread->where('data.type', 'order')->count(),
                    'inquiries' => $unread->where('data.type', 'inquiry')->count(),
                    'reviews'   => $unread->where('data.type', 'review')->count(),
                    'messages'  => $unread->where('data.type', 'message')->count(),
                    'total'     => $unread->count(),
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
    }
}