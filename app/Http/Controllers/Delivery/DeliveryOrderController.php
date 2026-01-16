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
              // Include 4 (Assigned) and 10 (Arrived in Country) as active tasks
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
    $query = Order::with(['items.product'])->where('delivery_boy_id', $riderId);

    // Filter by Status
    if ($request->has('status') && $request->status !== 'all') {
        if ($request->status == 'delivered') {
            $query->where('status', 5);
        } elseif ($request->status == 'failed') {
            $query->whereIn('status', [6, 8, 9]);
        }
    } else {
        $query->whereIn('status', [5, 6, 8, 9]);
    }

    // Filter by Country
    if ($request->filled('country')) {
        $query->where('country', $request->country);
    }

    // Filter by Currency
    if ($request->has('currency') && $request->currency !== 'all') {
        $query->where('currency', $request->currency);
    }

    // Existing Search
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('tracking_no', 'LIKE', "%$search%")
              ->orWhere('fname', 'LIKE', "%$search%")
              ->orWhere('city', 'LIKE', "%$search%");
        });
    }

    $orders = $query->latest()->paginate(10)->withQueryString();
    return view('delivery.orders.history', compact('orders'));
}

    /**
 * NEW: Handle intermediate milestones for International/Stripe orders.
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
    public function downloadReport(Request $request)
{
    $riderId = Auth::guard('delivery')->id();
    $query = Order::where('delivery_boy_id', $riderId);

    // APPLY THE SAME FILTERS AS taskHistory
    if ($request->status == 'delivered') $query->where('status', 5);
    elseif ($request->status == 'failed') $query->whereIn('status', [6, 8, 9]);
    else $query->whereIn('status', [5, 6, 8, 9]);

    if ($request->filled('country')) $query->where('country', $request->country);
    if ($request->has('currency') && $request->currency !== 'all') $query->where('currency', $request->currency);

    $tasks = $query->orderBy('updated_at', 'desc')->get();

    // Recalculate stats based on filtered data
    $stats = [
        'delivered' => $tasks->where('status', 5)->count(),
        'failed'    => $tasks->whereIn('status', [6, 8, 9])->count(),
        'total_lkr' => $tasks->where('status', 5)->where('currency', 'LKR')->sum('total_price'),
        'total_usd' => $tasks->where('status', 5)->where('currency', 'USD')->sum('total_price'),
        'rider_name'=> Auth::guard('delivery')->user()->name,
        'date'      => now()->format('d M Y, h:i A')
    ];

    $pdf = Pdf::loadView('delivery.orders.report_pdf', compact('tasks', 'stats'));
    return $pdf->download('Filtered_Performance_Report.pdf');
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
    
    // ✅ CRITICAL FIX: Load 'items.product' to prevent "property on null" error in email
    $order = Order::with('items.product')
        ->where('id', $id)
        ->where('delivery_boy_id', $riderId)
        ->firstOrFail();

    // Update status to 5 (Delivered)
    $order->update([
        'status' => 5, 
        'delivered_at' => now()
    ]); 

    // ✅ Trigger the Email
    try {
        Mail::to($order->email)->send(new \App\Mail\OrderDeliveredMail($order));
    } catch (\Exception $e) {
        // This will log the specific Gmail or data error if it fails
        Log::error("Delivered email failed for Order #{$order->tracking_no}: " . $e->getMessage());
    }

    // Existing system notification
    if ($order->user) {
        $order->user->notify(new \App\Notifications\OrderDeliveredNotification($order));
    }
    
    return redirect()->route('delivery.my-deliveries')->with('success', 'Order completed and email sent!');
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