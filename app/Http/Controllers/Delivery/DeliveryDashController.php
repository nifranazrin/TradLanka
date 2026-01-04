<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DeliveryDashController extends Controller
{
    public function dashboard()
    {
        $riderId = Auth::guard('delivery')->id();
        $today = Carbon::today();
        
        // Dynamic analysis starting exactly Jan 01
        $startDate = Carbon::create(2026, 1, 1);
        $endDate = Carbon::now();

        // Stats: Today vs Total
        $pendingTotal = Order::where('delivery_boy_id', $riderId)->whereIn('status', [4, 10])->count();
        $deliveredTotal = Order::where('delivery_boy_id', $riderId)->where('status', 5)->count();
        $failedTotal = Order::where('delivery_boy_id', $riderId)->whereIn('status', [6, 9])->count();

        $pendingToday = Order::where('delivery_boy_id', $riderId)->whereIn('status', [4, 10])->whereDate('updated_at', $today)->count();
        $deliveredToday = Order::where('delivery_boy_id', $riderId)->where('status', 5)->whereDate('updated_at', $today)->count();
        $failedToday = Order::where('delivery_boy_id', $riderId)->whereIn('status', [6, 9])->whereDate('updated_at', $today)->count();

        // Financial Logic: Unified COD Box
        $deliveredCodTotal = Order::where('delivery_boy_id', $riderId)->where('status', 5)->sum('total_price');
        $pendingCodTotal = Order::where('delivery_boy_id', $riderId)->whereIn('status', [4, 10])->sum('total_price');
        
        // Summing both for the header box
        $fullCodPotential = $deliveredCodTotal + $pendingCodTotal;

        // Recent Orders Table
        $activeDeliveries = Order::where('delivery_boy_id', $riderId)
            ->whereIn('status', [4, 10])
            ->latest()->take(5)->get();

        // Chart Data (Jan 01 to Now)
        $dates = $deliveredSeries = $failedSeries = [];
        $tempDate = $startDate->copy();
        while ($tempDate <= $endDate) {
            $dateStr = $tempDate->format('Y-m-d');
            $dates[] = $tempDate->format('M d'); 
            $deliveredSeries[] = Order::where('delivery_boy_id', $riderId)->where('status', 5)->whereDate('updated_at', $dateStr)->count();
            $failedSeries[] = Order::where('delivery_boy_id', $riderId)->whereIn('status', [6, 9])->whereDate('updated_at', $dateStr)->count();
            $tempDate->addDay();
        }

        return view('delivery.dashboard', compact(
            'pendingToday', 'pendingTotal', 'deliveredToday', 'deliveredTotal', 'failedToday', 'failedTotal',
            'deliveredCodTotal', 'pendingCodTotal', 'fullCodPotential', 'activeDeliveries',
            'dates', 'deliveredSeries', 'failedSeries'
        ));
    }
}