<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Notifications\OrderDeliveredNotification;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderDeliveredMail;
use Illuminate\Support\Facades\Log;

class DeliveryOrderController extends Controller 
{
    /**
     * Display active deliveries for the rider.
     */
    public function myDeliveries(Request $request)
    {
        $riderId = Auth::guard('delivery')->id();

        $query = Order::with(['items.product'])
            ->where('delivery_boy_id', $riderId)
              // ✅ Include 4 (Assigned) and 10 (Arrived in Country) as active tasks
            ->whereIn('status', [4, 10]);// Only active tasks

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tracking_no', 'LIKE', "%$search%")
                  ->orWhere('fname', 'LIKE', "%$search%")
                  ->orWhere('lname', 'LIKE', "%$search%")
                  ->orWhere('city', 'LIKE', "%$search%");
            });
        }

        $orders = $query->latest()->get();
        return view('delivery.orders.index', compact('orders'));
    }

    public function taskHistory(Request $request)
{
    $riderId = Auth::guard('delivery')->id();

    // Clear notifications for these orders
    Order::where('delivery_boy_id', $riderId)
        ->where('rider_seen', 0)
        ->update(['rider_seen' => 1]);

    $query = Order::with(['items.product'])
        ->where('delivery_boy_id', $riderId)
        ->whereIn('status', [5, 6, 8, 9]); // 5:Delivered, 6:Failed, 8:Pending Review, 9:Final Fail

    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('tracking_no', 'LIKE', "%$search%")
              ->orWhere('fname', 'LIKE', "%$search%")
              ->orWhere('lname', 'LIKE', "%$search%")
              ->orWhere('city', 'LIKE', "%$search%");
        });
    }

    // Latest shows most recent first, but includes all dates
    $orders = $query->latest()->paginate(10)->appends(['search' => $request->search]);
    return view('delivery.orders.history', compact('orders'));
}

    /**
 * ✅ NEW: Handle intermediate milestones for International/Stripe orders.
 */
public function updateMilestone(Request $request, $id)
{
    $riderId = Auth::guard('delivery')->id();
    $order = Order::where('id', $id)
        ->where('delivery_boy_id', $riderId)
        ->firstOrFail();

    // Move to Status 10 (Arrived at Destination Country)
    // This allows the multi-step tracking you requested for USD orders
      // Only allow specific milestones
       $order->update([
        'status' => 10 // Hardcode to Arrived in Destination Country
    ]);

    return redirect()->back()->with('success', 'International tracking milestone updated!');
}
    /**
     * Generate and download the PDF performance report.
     */
    public function downloadReport()
    {
        $riderId = Auth::guard('delivery')->id();
        
        // ✅ Included Status 8 in the report data
        $tasks = Order::where('delivery_boy_id', $riderId)
            ->whereIn('status', [5, 6, 8, 9])
            ->orderBy('status', 'asc') 
            ->orderBy('updated_at', 'desc')
            ->get();

        // Financial Calculation: Only Delivered (Status 5) AND COD orders
        $totalLKR = $tasks->where('status', 5)->filter(function($order) {
            $payMode = strtoupper($order->payment_mode);
            return str_contains($payMode, 'COD') && !str_contains($payMode, 'USD');
        })->sum('total_price');

        $totalUSD = $tasks->where('status', 5)->filter(function($order) {
            $payMode = strtoupper($order->payment_mode);
            return str_contains($payMode, 'COD') && str_contains($payMode, 'USD');
        })->sum('total_price');

        $stats = [
            'delivered' => $tasks->where('status', 5)->count(),
            // ✅ Combined 6, 8, and 9 for the total failed/reported count
            'failed'    => $tasks->whereIn('status', [6, 8, 9])->count(),
            'total_lkr' => $totalLKR,
            'total_usd' => $totalUSD,
            'rider_name'=> Auth::guard('delivery')->user()->name,
            'date'      => now()->format('d M Y, h:i A')
        ];

        $pdf = Pdf::loadView('delivery.orders.report_pdf', compact('tasks', 'stats'));
        return $pdf->download('TradLanka_Performance_Report.pdf');
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
    
    // Find the order assigned to this specific rider
    $order = Order::where('id', $id)
        ->where('delivery_boy_id', $riderId)
        ->firstOrFail();

    // Update status to 5 (Delivered) and set the timestamp
    $order->update([
        'status' => 5, 
        'delivered_at' => now()
    ]); 

    // ✅ NEW: Trigger the Order Delivered Email
    try {
        Mail::to($order->email)->send(new OrderDeliveredMail($order));
    } catch (\Exception $e) {
        // ✅ Removed backslash since Log is now imported at the top
        Log::error("Delivered email failed for Order #{$order->tracking_no}: " . $e->getMessage());
    }

    // Existing system notification
    $order->user->notify(new OrderDeliveredNotification($order));
    
    return redirect()->route('delivery.my-deliveries')->with('success', 'Order completed and confirmation email sent!');
}
    /**
     * ✅ UPDATED: Mark as Failed (moves to Pending Approval)
     */
    public function markAsFailed(Request $request, $id)
    {
        $riderId = Auth::guard('delivery')->id();
        $order = Order::where('id', $id)
            ->where('delivery_boy_id', $riderId)
            ->firstOrFail();

        // ✅ Updated to Status 8 (Pending Admin Approval)
        // This ensures it doesn't "auto-fail" but stays in history as 'Pending'
        $order->update([
            'status' => 8, 
            'cancel_reason' => $request->reason ?? 'Rider reported: Delivery failure'
        ]); 

        return redirect()->route('delivery.my-deliveries')->with('warning', 'Order reported to Admin for review.');
    }

    /**
     *  NEW: Mark all notifications as read.
     * This clears the bell dropdown and the sidebar red dots.
     */
    public function markAllRead()
    {
        $riderId = Auth::guard('delivery')->id();

        // 1. Clear Order notifications (Sidebar circles + Bell)
        Order::where('delivery_boy_id', $riderId)
            ->where('rider_seen', 0)
            ->update(['rider_seen' => 1]);

        // 2. Clear Chat notifications (Sidebar circle + Bell)
        \App\Models\Message::where('receiver_id', $riderId)
            ->where('receiver_type', 'delivery')
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return back()->with('success', 'All notifications cleared');
    }
}