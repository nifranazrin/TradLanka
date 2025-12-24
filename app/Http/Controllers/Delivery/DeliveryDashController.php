<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DeliveryDashController extends Controller
{
    public function dashboard()
{
    $riderId = Auth::guard('delivery')->id();

    $pendingCount = Order::where('delivery_boy_id', $riderId)->where('status', 4)->count();
    $deliveredTodayCount = Order::where('delivery_boy_id', $riderId)
        ->where('status', 5)
        ->whereDate('updated_at', now()) 
        ->count();
    $failedCount = Order::where('delivery_boy_id', $riderId)->where('status', 6)->count();

    $activeDeliveries = Order::where('delivery_boy_id', $riderId)
        ->whereIn('status', [4, 6])
        ->latest()->take(5)->get();

    // REMOVED ALL CONVERSION LOGIC HERE TO KEEP PRICES AS RUPEES
    
    return view('delivery.dashboard', compact(
        'pendingCount', 'deliveredTodayCount', 'failedCount', 'activeDeliveries'
    ));
}
   
}