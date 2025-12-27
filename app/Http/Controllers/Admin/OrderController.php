<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Staff; 

class OrderController extends Controller
{
    /**
     * Display orders at Head Office (Both Waiting and Assigned)
     */
    public function reviewOrders()
    {
        // ✅ UPDATED: Include status 4 so orders don't disappear after assignment
        // Status 3 = At Head Office, Status 4 = Out for Delivery
        $orders = Order::whereIn('status', [3, 4])
            ->latest()
            ->paginate(15);

        // Fetch internal delivery staff from 'staff' table where role is 'delivery'
        $deliveryPartners = Staff::where('role', 'delivery')->get(); 

        return view('admin.orders.index', compact('orders', 'deliveryPartners'));
    }



    public function acceptOrder($id)
    {
        $order = Order::findOrFail($id);
        
        // Update status to 1 so the customer sees "Order Received"
        $order->update(['status' => 1]);

        return redirect()->back()->with('success', 'Order accepted successfully. The customer timeline is now updated.');
    }
    
    /**
     * Show Order Details for Admin Review
     */
    public function show($id)
    {
        // Eager load items, products, and variants for the detail view
        $order = Order::with(['items.product', 'items.variant'])->findOrFail($id);

        // Fetch delivery partners for the assignment dropdown on the show page
        $deliveryPartners = Staff::where('role', 'delivery')->get();

        return view('admin.orders.show', compact('order', 'deliveryPartners'));
    }

    /**
     * Assign order to a Rider and update status to 4
     */
    public function assignOrder(Request $request, $id)
    {
        // Validate that the rider exists in the staff table
        $request->validate([
            'rider_id' => 'required|exists:staff,id',
        ]);

        $order = Order::findOrFail($id);

        // ✅ DATABASE SYNC: Using 'delivery_boy_id' as seen in your DB table
        // Setting status to 4 makes it visible to the Delivery Person
        $order->update([
            'status' => 4,
            'delivery_boy_id' => $request->rider_id, 
        ]);

        return redirect()->route('admin.orders.review')->with('success', 'Order assigned and handed over to delivery partner successfully.');
    }
}