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
    $orders = Order::whereIn('status', [3, 4, 5, 8, 9, 6, 10])
        ->orderByRaw("CASE 
            WHEN status = 3 THEN 1 
            WHEN status = 8 OR status = 9 THEN 2
            WHEN status = 10 THEN 3
            WHEN status = 4 THEN 4
            ELSE 5 
        END ASC")
        ->orderBy('created_at', 'desc') 
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

       
        $deliveryPartners = Staff::where('role', 'delivery')->get();

        return view('admin.orders.show', compact('order', 'deliveryPartners'));
    }

    /**
     * Assign order to a Rider and update status to 4
     */
   public function assignOrder(Request $request, $id)
{
    $request->validate(['rider_id' => 'required|exists:staff,id']);
    $order = Order::findOrFail($id);

    // Prevent assignment if the order isn't physically at the office yet
    if ($order->status != 3) {
        return redirect()->back()->with('error', 'Order must be at Head Office before assignment.');
    }

    $order->update([
        'status' => 4,
        'delivery_boy_id' => $request->rider_id, 
    ]);

    return redirect()->route('admin.orders.review')->with('success', 'Order handed over to rider.');
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

            if ($order->user) {
        $order->user->notify(new \App\Notifications\OrderCancelledNotification($order));
    }

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