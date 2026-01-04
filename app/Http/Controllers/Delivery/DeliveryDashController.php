<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; 

class DeliveryDashController extends Controller
{
    /**
     * Display the main delivery dashboard with real-time stats and charts.
     */
    public function dashboard()
    {
        $riderId = Auth::guard('delivery')->id();
        $today = Carbon::today();
        $oneMonthAgo = Carbon::now()->subDays(30);

        // --- Today vs Total Stats ---
        // Pending Tasks (Status 4)
        $pendingToday = Order::where('delivery_boy_id', $riderId)->where('status', 4)->whereDate('updated_at', $today)->count();
        $pendingTotal = Order::where('delivery_boy_id', $riderId)->where('status', 4)->count();

        // Delivered Tasks (Status 5)
        $deliveredToday = Order::where('delivery_boy_id', $riderId)->where('status', 5)->whereDate('updated_at', $today)->count();
        $deliveredTotal = Order::where('delivery_boy_id', $riderId)->where('status', 5)->count();

        // Failed Tasks (Status 6)
        $failedToday = Order::where('delivery_boy_id', $riderId)->where('status', 6)->whereDate('updated_at', $today)->count();
        $failedTotal = Order::where('delivery_boy_id', $riderId)->where('status', 6)->count();

        // Recent table list and current cash held
        $activeDeliveries = Order::where('delivery_boy_id', $riderId)->whereIn('status', [4, 6])->latest()->take(5)->get();
        $cashToCollect = Order::where('delivery_boy_id', $riderId)->where('status', 4)->sum('total_price');

        
        $monthlyData = Order::where('delivery_boy_id', $riderId)
            ->where('updated_at', '>=', $oneMonthAgo)
            ->whereIn('status', [5, 6])
            ->select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) as delivered'),
                DB::raw('SUM(CASE WHEN status = 6 THEN 1 ELSE 0 END) as failed')
            )
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $dates = $monthlyData->pluck('date');
        $deliveredSeries = $monthlyData->pluck('delivered');
        $failedSeries = $monthlyData->pluck('failed');

        return view('delivery.dashboard', compact(
            'pendingToday', 'pendingTotal', 
            'deliveredToday', 'deliveredTotal', 
            'failedToday', 'failedTotal', 
            'activeDeliveries', 'cashToCollect',
            'dates', 'deliveredSeries', 'failedSeries'
        ));
    }

   
}