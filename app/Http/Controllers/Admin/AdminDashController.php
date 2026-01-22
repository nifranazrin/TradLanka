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
        $totalProducts = Product::whereIn('status', ['approved', 'reapproved'])->count();
        $totalSellers = Staff::where('role', 'seller')->count();
        $pendingRequests = UserRequest::where('status', 'pending')->count(); 
        $totalReviews = \App\Models\Review::count();

        $latestOrdersNotify = Order::where('status', 0)->latest()->take(3)->get();
        $latestProductsNotify = Product::where('status', 'pending')->latest()->take(3)->get();
        $latestReviewsNotify = \App\Models\Review::latest()->take(3)->get();
        $latestSellerRequestsNotify = UserRequest::where('status', 'pending')->latest()->take(3)->get();
            
        // 2. Activity Counts
        $todaysOrders = Order::whereDate('created_at', $today)->count();
        $visitorCount = DB::table('sessions')->count(); 
        $totalOrdersAllTime = Order::count(); 

        // 3. SYNCHRONIZED REVENUE LOGIC
        $pendingStatuses = [0, 1, 2, 3, 4, 10]; 
        $successStatus = 5; 

        // --- LKR CALCULATIONS ---
        $salesLkr = Order::where('status', $successStatus)->where('currency', 'LKR')->sum('total_price') ?? 0;
        $successLKRCount = Order::where('status', $successStatus)->where('currency', 'LKR')->count();

        $pendingLkr = Order::whereIn('status', $pendingStatuses)->where('currency', 'LKR')->sum('total_price') ?? 0;
        $pendingLKRCount = Order::whereIn('status', $pendingStatuses)->where('currency', 'LKR')->count();

        // --- USD CALCULATIONS ---
        $salesUsd = Order::where('status', $successStatus)->where('currency', 'USD')->sum('total_price') ?? 0;
        $successUSDCount = Order::where('status', $successStatus)->where('currency', 'USD')->count();

        $pendingUsd = Order::whereIn('status', $pendingStatuses)->where('currency', 'USD')->sum('total_price') ?? 0;
        $pendingUSDCount = Order::whereIn('status', $pendingStatuses)->where('currency', 'USD')->count();

        // Contextual Monthly Total (Current Month only)
        $orderCount = Order::whereMonth('created_at', $now->month)
                           ->whereYear('created_at', $now->year)
                           ->count();

        // 4. Top Selling Products (Delivered Only)
        $topProducts = Product::select('id', 'name', 'image')
            ->withCount(['orderItems as total_sold' => function($query) use ($successStatus) {
                $query->join('orders', 'order_items.order_id', '=', 'orders.id')
                      ->where('orders.status', $successStatus)
                      ->select(DB::raw('IFNULL(sum(order_items.qty), 0)'));
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
            ->take(5)
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
        $chartStatuses = array_merge([$successStatus], $pendingStatuses);

        // --- Last 8 Days Data ---
        $days = [];
        $revenueData = []; 
        $salesData = [];   

        for ($i = 7; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('d M');

            $revenueData[] = Order::whereDate('created_at', $date)
                                    ->where('status', $successStatus)
                                    ->sum('total_price') ?? 0;

            $salesData[] = Order::whereDate('created_at', $date)
                                    ->whereIn('status', $chartStatuses)
                                    ->sum('total_price') ?? 0;
        }

        // --- Last 6 Months Data ---
        $months = [];
        $monthlyRevenue = [];
        $monthlySalesData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $months[] = $monthDate->format('F');
            
            $monthlyRevenue[] = Order::whereMonth('created_at', $monthDate->month)
                                        ->whereYear('created_at', $monthDate->year)
                                        ->where('status', $successStatus)
                                        ->sum('total_price') ?? 0;
                                        
            $monthlySalesData[] = Order::whereMonth('created_at', $monthDate->month)
                                            ->whereYear('created_at', $monthDate->year)
                                            ->whereIn('status', $chartStatuses)
                                            ->sum('total_price') ?? 0;
        }

        // --- 9. GEOGRAPHIC ANALYTICS (Corrected for Maps & City Donut) ---

        // Local Revenue (LKR) by City
        $cityRevenue = Order::where('status', $successStatus)
            ->where('currency', 'LKR')
            ->select('city', DB::raw('SUM(total_price) as total_revenue'))
            ->groupBy('city')
            ->orderBy('total_revenue', 'desc')
            ->take(5)
            ->get();
        $topLocalCity = $cityRevenue->first();

        // International Revenue (USD) by Country
        $intlRevenue = Order::where('status', $successStatus)
            ->where('currency', 'USD')
            ->select('country as city', DB::raw('SUM(total_price) as total_revenue'))
            ->groupBy('country')
            ->orderBy('total_revenue', 'desc')
            ->take(5)
            ->get();
        $topInternationalCity = $intlRevenue->first();

        // Map Pins: Active Deliveries (Blue Pins)
        $activeDeliveries = Order::whereIn('status', [0, 1, 3, 4]) // From New to Shipping
    ->whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->select('tracking_no', 'city', 'latitude', 'longitude', 'currency', 'status')
    ->get();

        // Map Hotspots: Delivered Sales (Red Circles)
        $deliveryHotspots = Order::where('status', 5)
    ->whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->select('city', 'currency', 'latitude as lat', 'longitude as lng', DB::raw('count(*) as count'))
    ->groupBy('city', 'currency', 'latitude', 'longitude')
    ->get();

        // --- 10. RETURN STATEMENT ---
        return view('admin.dashboard', compact(
            'totalCategories', 'totalProducts', 'totalSellers', 'pendingRequests',
            'orderCount', 'todaysOrders', 'visitorCount', 'totalOrdersAllTime',
            'salesLkr', 'pendingLkr', 'successLKRCount', 'pendingLKRCount',
            'salesUsd', 'pendingUsd', 'successUSDCount', 'pendingUSDCount',
            'topProducts', 'topCategories', 'statusCounts', 'recentOrders', 
            'days', 'revenueData', 'salesData', 'months', 'monthlyRevenue', 'monthlySalesData',
            'totalReviews', 'latestOrdersNotify', 'latestProductsNotify', 
            'latestReviewsNotify', 'latestSellerRequestsNotify',
            'cityRevenue', 'intlRevenue', 'activeDeliveries', 'deliveryHotspots', 
            'topLocalCity', 'topInternationalCity'
        ));
    }

}