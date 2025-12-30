<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Notifications\OrderDeliveredNotification;

class DeliveryOrderController extends Controller 
{
   
    public function myDeliveries()
    {
        $riderId = Auth::guard('delivery')->id();

        // We pull RAW database values so the Blade Safety Logic can detect the symbol
        $orders = Order::with(['items.product'])
            ->where('delivery_boy_id', $riderId)
            ->where('status', 4) // Only show active tasks
            ->latest()
            ->get();

        return view('delivery.orders.index', compact('orders'));
    }

     public function taskHistory()
{
    $riderId = Auth::guard('delivery')->id();

    $orders = Order::with(['items.product'])
        ->where('delivery_boy_id', $riderId)
        ->whereIn('status', [5, 6, 9]) 
        ->latest()
        ->paginate(10);

    return view('delivery.orders.history', compact('orders'));
}
  
    public function show($id)
    {
        $riderId = Auth::guard('delivery')->id();

        $order = Order::with(['items.product', 'items.variant'])
            ->where('id', $id)
            ->where('delivery_boy_id', $riderId)
            ->firstOrFail();

        return view('delivery.orders.show', compact('order'));
    }

   
    public function markAsDelivered($id)
    {
        $riderId = Auth::guard('delivery')->id();

        $order = Order::where('id', $id)
            ->where('delivery_boy_id', $riderId)
            ->firstOrFail();

        $order->update([
            'status' => 5, 
            'delivered_at' => now()
        ]); 

       $order->user->notify(new OrderDeliveredNotification($order));

        return redirect()->route('delivery.my-deliveries')->with('success', 'Order completed and moved to history!');
    }

   
    public function markAsFailed(Request $request, $id)
{
    $riderId = Auth::guard('delivery')->id();

    $order = Order::where('id', $id)
        ->where('delivery_boy_id', $riderId)
        ->firstOrFail();

    // ✅ Update to Status 9 (Reported Failed)
    $order->update([
        'status' => 9, 
        'cancel_reason' => $request->reason ?? 'Rider reported: Customer not received'
    ]); 

    return redirect()->route('delivery.my-deliveries')->with('warning', 'Order reported to Admin for review.');
}
}