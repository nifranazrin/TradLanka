<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Staff; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;   
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderCancelledMail;

class OrderController extends Controller
{
    /**
     * Display orders at Head Office (Both Waiting and Assigned)
     */
    public function reviewOrders()
{
    // ✅ ADD 0 and 1 to the list so new COD and Paid orders show up
    $orders = Order::whereIn('status', [ 1, 3, 4, 5, 8,9, 6])
        ->latest()
        ->paginate(15);

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
     

   public function finalizeRefund($id)
{
    $order = Order::with('items.product')->findOrFail($id);
    $payMode = strtoupper($order->payment_mode);
    $isCod = str_contains($payMode, 'COD');

    // Only allow finalization if the status is 8 (Pending Review) or 9 (Final Fail)
    if ($order->status == 8 || $order->status == 9) {
        $previousStatus = $order->status; 

        DB::beginTransaction();
        try {
            // Restore stock levels for each item in the order
            foreach ($order->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock', $item->qty);
                }
            }

            // Update status to 6 (Cancelled) and reset rider notification
            $order->update([
                'status' => 6, 
                'rider_seen' => 0 
            ]); 

            // ✅ NEW: Trigger the Order Cancelled Email
            try {
                Mail::to($order->email)->send(new OrderCancelledMail($order));
            } catch (\Exception $e) {
                // Log Gmail SMTP errors without crashing the database transaction
                Log::error("Cancellation email failed for Order #{$order->tracking_no}: " . $e->getMessage());
            }

            DB::commit();

            if ($previousStatus == 9) {
                return back()->with('success', 'Confirmed: Delivery Cancelled. Stock Restored & Email Sent.');
            }
            
            return back()->with('success', 'Refund Processed successfully & Email Sent.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    return back()->with('error', 'Action not allowed.');
}
}