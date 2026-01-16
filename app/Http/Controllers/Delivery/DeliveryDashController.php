<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryDashController extends Controller
{
    public function dashboard()
    {
        $riderId = Auth::guard('delivery')->id();
        $today = Carbon::today();
        
        // Data range for performance trend charts
        $startDate = Carbon::create(2026, 1, 1);
        $endDate = Carbon::now();

        // --- 1. STATS: Today vs Total ---
        $pendingTotal = Order::where('delivery_boy_id', $riderId)->whereIn('status', [4, 10])->count();
        $deliveredTotal = Order::where('delivery_boy_id', $riderId)->where('status', 5)->count();
        $failedTotal = Order::where('delivery_boy_id', $riderId)->whereIn('status', [6, 9])->count();

        $pendingToday = Order::where('delivery_boy_id', $riderId)->whereIn('status', [4, 10])->whereDate('updated_at', $today)->count();
        $deliveredToday = Order::where('delivery_boy_id', $riderId)->where('status', 5)->whereDate('updated_at', $today)->count();
        $failedToday = Order::where('delivery_boy_id', $riderId)->whereIn('status', [6, 9])->whereDate('updated_at', $today)->count();

        // --- 2. FINANCIAL LOGIC: COD Only ---
        $codQuery = function($query) {
            $query->where(function($q) {
                $q->where('payment_mode', 'LIKE', '%COD%')
                  ->orWhere('payment_mode', 'LIKE', '%Cash%');
            });
        };

        $deliveredCodTotal = Order::where('delivery_boy_id', $riderId)->where('status', 5)->where($codQuery)->sum('total_price') ?? 0;
        $pendingCodTotal = Order::where('delivery_boy_id', $riderId)->whereIn('status', [4, 10])->where($codQuery)->sum('total_price') ?? 0;
        $cancelledCodTotal = Order::where('delivery_boy_id', $riderId)->whereIn('status', [6, 9])->where($codQuery)->sum('total_price') ?? 0;

        // --- 3. REVENUE BY CITY ---
        $topCitiesRevenue = Order::where('delivery_boy_id', $riderId)
            ->where('status', 5)
            ->select('city', 'currency', DB::raw('SUM(total_price) as total_amount'))
            ->groupBy('city', 'currency')
            ->orderBy('total_amount', 'desc')
            ->get();

        $topLocalCity = $topCitiesRevenue->where('currency', 'LKR')->first();
        $topInternationalCity = $topCitiesRevenue->where('currency', 'USD')->first();

        // --- 4. MAP DATA (Pre-segmented for Dynamic Filter) ---
        // Active Tasks Pins (Blue Pointers) - Added ID for Routing
        $activeDeliveries = Order::where('delivery_boy_id', $riderId)
            ->whereIn('status', [4, 10])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('id', 'tracking_no', 'city', 'latitude', 'longitude', 'currency', 'fname', 'lname', 'total_price')
            ->get();

        // Hotspots (Red Circles)
        $deliveryHotspots = Order::where('delivery_boy_id', $riderId)
            ->where('status', 5)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select('city', 'currency', 'latitude as lat', 'longitude as lng', DB::raw('count(*) as count'))
            ->groupBy('city', 'currency', 'latitude', 'longitude')
            ->get();

        // --- 5. CHART DATA: Performance Trend ---
        $dates = []; $deliveredSeries = []; $failedSeries = [];
        $chartData = Order::where('delivery_boy_id', $riderId)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(updated_at) as date'), 'status', DB::raw('count(*) as count'))
            ->groupBy('date', 'status')->get();

        $tempDate = $startDate->copy();
        while ($tempDate <= $endDate) {
            $dateStr = $tempDate->format('Y-m-d');
            $dates[] = $tempDate->format('M d'); 
            $deliveredSeries[] = $chartData->where('date', $dateStr)->where('status', 5)->first()->count ?? 0;
            $failedSeries[] = $chartData->where('date', $dateStr)->whereIn('status', [6, 9])->sum('count') ?? 0;
            $tempDate->addDay();
        }

        return view('delivery.dashboard', compact(
            'pendingToday', 'pendingTotal', 'deliveredToday', 'deliveredTotal', 'failedToday', 'failedTotal',
            'deliveredCodTotal', 'pendingCodTotal', 'cancelledCodTotal',
            'activeDeliveries', 'deliveryHotspots', 'topCitiesRevenue',
            'topLocalCity', 'topInternationalCity',
            'dates', 'deliveredSeries', 'failedSeries'
        ));
    }
}