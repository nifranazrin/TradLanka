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
    /**
     * Display active deliveries for the rider.
     */
    public function myDeliveries(Request $request)
    {
        $riderId = Auth::guard('delivery')->id();

        $query = Order::with(['items.product'])
            ->where('delivery_boy_id', $riderId)
            ->where('status', 4); // Only active tasks

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

    /**
     * Display completed and reported tasks in History.
     */
    public function taskHistory(Request $request)
    {
        $riderId = Auth::guard('delivery')->id();

        $query = Order::with(['items.product'])
            ->where('delivery_boy_id', $riderId)
            // ✅ Included 8 (Pending) so it shows up in history while waiting for Admin
            ->whereIn('status', [5, 6, 8, 9]); 

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tracking_no', 'LIKE', "%$search%")
                  ->orWhere('fname', 'LIKE', "%$search%")
                  ->orWhere('lname', 'LIKE', "%$search%")
                  ->orWhere('city', 'LIKE', "%$search%");
            });
        }

        $orders = $query->latest()->paginate(10)->appends(['search' => $request->search]);
        return view('delivery.orders.history', compact('orders'));
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
}