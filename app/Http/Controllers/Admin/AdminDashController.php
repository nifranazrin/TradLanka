<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\UserRequest;
use App\Models\Staff;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashController extends Controller
{
    /**
     * Display the Admin Dashboard with real-time analytics.
     */
    public function dashboard()
    {
        $now = Carbon::now();
        $today = Carbon::today();

        // 1. Core System Statistics
        $totalCategories = Category::count();
        $totalProducts = Product::count();
        $totalSellers = Staff::where('role', 'seller')->count();
        $pendingRequests = UserRequest::where('status', 'pending')->count(); 
        $totalReviews = \App\Models\Review::count();
       
        // 2. Lively Activity Counts (Includes ALL payment modes)
        $orderCount = Order::whereMonth('created_at', $now->month)->count();
        $todaysOrders = Order::whereDate('created_at', $today)->count();
        $visitorCount = DB::table('sessions')->count(); 

        // 3. IMPROVED Revenue Logic (Explicitly checking for COD and Stripe)
        // Calculates LKR revenue: Matches currency LKR OR payment mode 'COD'
        $salesLkr = Order::whereMonth('created_at', $now->month)
            ->where(function($query) {
                $query->where('currency', 'LKR')
                      ->orWhere('payment_mode', 'COD')
                      ->orWhere('payment_mode', 'Cash on Delivery');
            })->sum('total_price');

        // Calculates USD revenue: Matches currency USD OR payment mode containing 'Stripe'
        $salesUsd = Order::whereMonth('created_at', $now->month)
            ->where(function($query) {
                $query->where('currency', 'USD')
                      ->orWhere('payment_mode', 'LIKE', '%Stripe%')
                      ->orWhere('payment_mode', 'LIKE', '%USD%');
            })->sum('total_price');

        // 4. Top Selling Products
        $topProducts = Product::select('id', 'name')
            ->withCount(['orderItems as total_sold' => function($query) {
                $query->select(DB::raw('sum(qty)'));
            }])
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        // 5. Top Categories (Revenue Based)
        $topCategories = Category::select('categories.id', 'categories.name')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->select('categories.name', DB::raw('SUM(order_items.price * order_items.qty) as total_revenue'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->take(4)
            ->get();

        // 6. EXPANDED Order Status Logic for Pie Chart
        // Added status 3, 6, and 8 so your chart is 100% accurate
        $statusCounts = [
            'Pending (New)' => Order::where('status', '0')->count(),
            'Accepted'      => Order::where('status', '1')->count(),
            'At Office'     => Order::where('status', '3')->count(),
            'With Rider'    => Order::where('status', '4')->count(),
            'Delivered'     => Order::where('status', '5')->count(),
            'Refunded'      => Order::where('status', '6')->count(),
            'Refund Req.'   => Order::where('status', '8')->count(),
        ];

        // 7. Recent Orders (Both COD and Stripe will show here)
        $recentOrders = Order::latest()->take(5)->get();

        // 8. Revenue Analytics Data (Last 8 Days)
        $days = [];
        $revenueData = [];
        for ($i = 7; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('d M');
            $revenueData[] = Order::whereDate('created_at', $date)->sum('total_price');
        }

        // ✅ Pass 'totalReviews' to the view
    return view('admin.dashboard', compact(
        'totalCategories', 'totalProducts', 'totalSellers', 'pendingRequests',
        'orderCount', 'todaysOrders', 'visitorCount', 'salesLkr', 'salesUsd',
        'topProducts', 'topCategories', 'statusCounts', 'recentOrders', 
        'days', 'revenueData', 'totalReviews'
    ));
}
}