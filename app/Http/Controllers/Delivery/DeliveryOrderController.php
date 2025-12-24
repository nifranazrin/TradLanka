<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DeliveryOrderController extends Controller 
{
    /**
     * ✅ ACTIVE TASKS
     * Only show orders with Status 4 (Out for Delivery)
     */
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

    /**
     * ✅ TASK HISTORY
     * Show orders that are already processed (Status 5 and 6)
     */
    public function taskHistory()
    {
        $riderId = Auth::guard('delivery')->id();

        $orders = Order::with(['items.product'])
            ->where('delivery_boy_id', $riderId)
            ->whereIn('status', [5, 6]) // 5 = Delivered, 6 = Failed
            ->latest()
            ->paginate(10); // History can be long, so we use pagination

        return view('delivery.orders.history', compact('orders'));
    }

    /**
     * Show order details
     */
    public function show($id)
    {
        $riderId = Auth::guard('delivery')->id();

        $order = Order::with(['items.product', 'items.variant'])
            ->where('id', $id)
            ->where('delivery_boy_id', $riderId)
            ->firstOrFail();

        return view('delivery.orders.show', compact('order'));
    }

    /**
     * Mark order as successfully Delivered (Status 5)
     */
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

        // Redirect back to active tasks; the order will now appear in History
        return redirect()->route('delivery.my-deliveries')->with('success', 'Order completed and moved to history!');
    }

    /**
     * Mark order as Not Received / Failed (Status 6)
     */
    public function markAsFailed(Request $request, $id)
    {
        $riderId = Auth::guard('delivery')->id();

        $order = Order::where('id', $id)
            ->where('delivery_boy_id', $riderId)
            ->firstOrFail();

        $order->update([
            'status' => 6, 
            'cancel_reason' => $request->reason ?? 'Customer unavailable'
        ]); 

        return redirect()->route('delivery.my-deliveries')->with('success', 'Order marked as failed and moved to history.');
    }
}