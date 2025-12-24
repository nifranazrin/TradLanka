<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; 
use Illuminate\Support\Facades\Auth; // Import Auth
use App\Models\Category; 

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 1. Existing Category Logic
        View::composer('*', function ($view) {
            $view->with('globalCategories', Category::whereNull('parent_id')
                ->with('subcategories')
                ->get());
        });

        // 2. NEW: Seller Notification Counts
        // This makes '$notif_counts' available in your sidebar and header
        View::composer('*', function ($view) {
            if (Auth::guard('seller')->check()) {
                $seller = Auth::guard('seller')->user();
                
                // Fetch unread notifications from your database table
                $unread = $seller->unreadNotifications;

                $view->with('notif_counts', [
                    'products' => $unread->where('data.type', 'product')->count(),
                    'orders'   => $unread->where('data.type', 'order')->count(),
                    'inquiries' => $unread->where('data.type', 'inquiry')->count(),
                    'messages' => $unread->where('data.type', 'message')->count(),
                    'total'    => $unread->count(),
                ]);
            }
        });
    }
}