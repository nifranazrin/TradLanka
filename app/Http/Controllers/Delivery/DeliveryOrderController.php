<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Notifications\OrderDeliveredNotification;

class DeliveryOrderController extends Controller 
{
   
    // Add Request at the top of your method
public function myDeliveries(Request $request)
{
    $riderId = Auth::guard('delivery')->id();

    // Start the query with relationships
    $query = Order::with(['items.product'])
        ->where('delivery_boy_id', $riderId)
        ->where('status', 4); // Only show active tasks

    // Add Search Logic
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
    // Ensure Request $request is added to the parameters
public function taskHistory(Request $request)
{
    $riderId = Auth::guard('delivery')->id();

    // Start the query with relationships
    $query = Order::with(['items.product'])
        ->where('delivery_boy_id', $riderId)
        ->whereIn('status', [5, 6, 9]); // Completed, Failed, or Reported

    //  Add Search Logic for History
    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('tracking_no', 'LIKE', "%$search%")
              ->orWhere('fname', 'LIKE', "%$search%")
              ->orWhere('lname', 'LIKE', "%$search%")
              ->orWhere('city', 'LIKE', "%$search%");
        });
    }

    // Use appends() to keep the search query active when clicking through pages
    $orders = $query->latest()->paginate(10)->appends(['search' => $request->search]);

    return view('delivery.orders.history', compact('orders'));
}

 /**
     * Generate and download the PDF performance report.
     */
     public function downloadReport()
{
    $riderId = Auth::guard('delivery')->id();
    
    // Fetch and sort: status 5 (Delivered) first, then others
    $tasks = Order::where('delivery_boy_id', $riderId)
        ->whereIn('status', [5, 6, 9])
        ->orderBy('status', 'asc') 
        ->orderBy('updated_at', 'desc')
        ->get();

    // Financial Calculation: Only Delivered (Status 5) AND COD orders
    $totalLKR = $tasks->where('status', 5)->filter(function($order) {
        $payMode = strtoupper($order->payment_mode);
        // Sum if it is COD and NOT an International/USD payment
        return str_contains($payMode, 'COD') && !str_contains($payMode, 'USD');
    })->sum('total_price');

    $totalUSD = $tasks->where('status', 5)->filter(function($order) {
        $payMode = strtoupper($order->payment_mode);
        // Sum if it is COD and IS an International/USD payment
        return str_contains($payMode, 'COD') && str_contains($payMode, 'USD');
    })->sum('total_price');

    $stats = [
        'delivered' => $tasks->where('status', 5)->count(),
        'failed'    => $tasks->whereIn('status', [6, 9])->count(),
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