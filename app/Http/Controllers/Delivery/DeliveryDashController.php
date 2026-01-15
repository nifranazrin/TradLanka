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
        
        $startDate = Carbon::create(2026, 1, 1);
        $endDate = Carbon::now();

        // 1. Stats: Today vs Total
        $pendingTotal = Order::where('delivery_boy_id', $riderId)->whereIn('status', [4, 10])->count();
        $deliveredTotal = Order::where('delivery_boy_id', $riderId)->where('status', 5)->count();
        $failedTotal = Order::where('delivery_boy_id', $riderId)->whereIn('status', [6, 9])->count();

        $pendingToday = Order::where('delivery_boy_id', $riderId)->whereIn('status', [4, 10])->whereDate('updated_at', $today)->count();
        
        // FIX: Change created_at to updated_at or delivered_at to show 0 if nothing was delivered today
        $deliveredToday = Order::where('delivery_boy_id', $riderId)
            ->where('status', 5)
            ->whereDate('updated_at', $today) 
            ->count();

        $failedToday = Order::where('delivery_boy_id', $riderId)->whereIn('status', [6, 9])->whereDate('updated_at', $today)->count();

        // 2. Financial Logic: COD Only
        // Delivered COD: Filtered by $riderId to fix the 104k error
        $deliveredCodTotal = Order::where('delivery_boy_id', $riderId)
            ->where('status', 5)
            ->where(function($query) {
                $query->where('payment_mode', 'COD')
                      ->orWhere('payment_mode', 'cod')
                      ->orWhere('payment_mode', 'Cash on Delivery');
            })
            ->sum('total_price') ?? 0;

        // Pending COD: Correctly captures your Rs. 830.00 active task
        $pendingCodTotal = Order::where('delivery_boy_id', $riderId)
            ->whereIn('status', [4, 10])
            ->where(function($q) {
                $q->where('payment_mode', 'LIKE', '%COD%')
                  ->orWhere('payment_mode', 'LIKE', '%Cash%');
            })
            ->sum('total_price') ?? 0;

        $fullCodPotential = $deliveredCodTotal + $pendingCodTotal;

        // 3. Recent Assigned Tasks
        $activeDeliveries = Order::where('delivery_boy_id', $riderId)
            ->whereIn('status', [4, 10])
            ->latest()->take(5)->get();

        // 4. Optimized Chart Data
        $chartData = Order::where('delivery_boy_id', $riderId)
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(updated_at) as date'), 'status', DB::raw('count(*) as count'))
            ->groupBy('date', 'status')->get();

        $dates = $deliveredSeries = $failedSeries = [];
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
            'deliveredCodTotal', 'pendingCodTotal', 'fullCodPotential', 'activeDeliveries',
            'dates', 'deliveredSeries', 'failedSeries'
        ));
    }
}