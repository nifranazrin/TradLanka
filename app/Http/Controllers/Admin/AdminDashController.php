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
       
        // 2. Activity Counts
        $todaysOrders = Order::whereDate('created_at', $today)->count();
        $visitorCount = DB::table('sessions')->count(); 
        
        //  ADDED: Total Lifetime Orders (All time, no date filters)
        $totalOrdersAllTime = Order::count(); 

        // 3. SYNCHRONIZED REVENUE LOGIC
        // Global logic to match SQL Audit (Total success regardless of date)
        $pendingStatuses = [0, 1, 2, 3, 4, 10]; 
        $successStatus = 5; 

        // --- LKR CALCULATIONS ---
        $salesLkr = Order::where('status', $successStatus)->where('currency', 'LKR')->sum('total_price');
        $successLKRCount = Order::where('status', $successStatus)->where('currency', 'LKR')->count();

        $pendingLkr = Order::whereIn('status', $pendingStatuses)->where('currency', 'LKR')->sum('total_price');
        $pendingLKRCount = Order::whereIn('status', $pendingStatuses)->where('currency', 'LKR')->count();

        // --- USD CALCULATIONS ---
        $salesUsd = Order::where('status', $successStatus)->where('currency', 'USD')->sum('total_price');
        $successUSDCount = Order::where('status', $successStatus)->where('currency', 'USD')->count();

        $pendingUsd = Order::whereIn('status', $pendingStatuses)->where('currency', 'USD')->sum('total_price');
        $pendingUSDCount = Order::whereIn('status', $pendingStatuses)->where('currency', 'USD')->count();

        // Contextual Monthly Total (Current Month only)
        $orderCount = Order::whereMonth('created_at', $now->month)
                           ->whereYear('created_at', $now->year)
                           ->count();

        // 4. Top Selling Products (Filtered to Status 5 - Delivered Only)
        $topProducts = Product::select('id', 'name')
            ->withCount(['orderItems as total_sold' => function($query) use ($successStatus) {
                $query->join('orders', 'order_items.order_id', '=', 'orders.id')
                      ->where('orders.status', $successStatus)
                      ->select(DB::raw('sum(qty)'));
            }])
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        // 5. Top Categories (Revenue Based)
        $topCategories = Category::select('categories.id', 'categories.name')
            ->join('products', 'categories.id', '=', 'products.category_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', $successStatus)
            ->select('categories.name', DB::raw('SUM(order_items.price * order_items.qty) as total_revenue'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->take(4)
            ->get();

        // 6. Status Logic for Bar Chart
        $statusCounts = [
            'Pending'    => Order::where('status', '0')->count(),
            'Accepted'   => Order::where('status', '1')->count(),
            'At Office'  => Order::where('status', '3')->count(),
            'With Rider' => Order::where('status', '4')->count(),
            'Delivered'  => Order::where('status', '5')->count(),
            'Refunded'   => Order::where('status', '6')->count(),
            'Refund Req' => Order::where('status', '8')->count(),
        ];

        // 7. Recent Orders
        $recentOrders = Order::latest()->take(5)->get();

       // 8. DYNAMIC REVENUE ANALYTICS (Days vs Months)
$successStatus = 5; // Delivered
$pendingStatuses = [0, 1, 2, 3, 4, 10]; // All active order types
$chartStatuses = array_merge([$successStatus], $pendingStatuses);

// --- Last 8 Days Data ---
$days = [];
$revenueData = []; // Confirmed Revenue (Line 1)
$salesData = [];   // Total Sales Volume (Line 2)

for ($i = 7; $i >= 0; $i--) {
    $date = Carbon::now()->subDays($i);
    $days[] = $date->format('d M');

    // 1. Confirmed Revenue (Only Delivered/Status 5)
    $revenueData[] = Order::whereDate('created_at', $date)
                            ->where('status', $successStatus)
                            ->sum('total_price');

    // 2. Total Sales Volume (All Active/Potential Revenue)
    $salesData[] = Order::whereDate('created_at', $date)
                            ->whereIn('status', $chartStatuses)
                            ->sum('total_price');
}

// --- Last 6 Months Data ---
$months = [];
$monthlyRevenue = [];
$monthlySalesData = [];

for ($i = 5; $i >= 0; $i--) {
    $monthDate = Carbon::now()->subMonths($i);
    $months[] = $monthDate->format('F');
    
    // 1. Confirmed Monthly Revenue
    $monthlyRevenue[] = Order::whereMonth('created_at', $monthDate->month)
                                ->whereYear('created_at', $monthDate->year)
                                ->where('status', $successStatus)
                                ->sum('total_price');
                                
    // 2. Total Monthly Sales Volume
    $monthlySalesData[] = Order::whereMonth('created_at', $monthDate->month)
                                    ->whereYear('created_at', $monthDate->year)
                                    ->whereIn('status', $chartStatuses)
                                    ->sum('total_price');
}
        //  Passed 'totalOrdersAllTime' to the view
      return view('admin.dashboard', compact(
    'totalCategories', 
    'totalProducts', 
    'totalSellers', 
    'pendingRequests',
    'orderCount', 
    'todaysOrders', 
    'visitorCount', 
    'totalOrdersAllTime',
    'salesLkr', 
    'pendingLkr', 
    'successLKRCount', 
    'pendingLKRCount',
    'salesUsd', 
    'pendingUsd', 
    'successUSDCount', 
    'pendingUSDCount',
    'topProducts', 
    'topCategories', 
    'statusCounts', 
    'recentOrders', 
    'days', 
    'revenueData', 
    'salesData',          // ✅ Added for the dual-line chart
    'months', 
    'monthlyRevenue', 
    'monthlySalesData',   // ✅ Added for the dual-line chart
    'totalReviews'
));
        
    }
}