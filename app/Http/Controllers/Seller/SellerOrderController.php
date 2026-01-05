<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth; // Added for safe Auth handling

class SellerOrderController extends Controller
{
    /**
     * Seller Order List
     */
       public function index(Request $request) // Ensure Request is injected
{
    $sellerId = Auth::guard('seller')->id();
    $seller = Auth::guard('seller')->user();
    $search = $request->input('search'); // Get search term

    if (!$seller) {
        return redirect()->route('staff.login');
    }

    // Mark notifications as read
    $seller->unreadNotifications->where('data.type', 'order')->markAsRead();

    // Query with search filter
    $orders = Order::whereHas('items.product', function ($query) use ($sellerId) {
            $query->where('seller_id', $sellerId);
        })
        ->with(['items.product'])
        ->when($search, function ($query, $search) {
            return $query->where(function($q) use ($search) {
                $q->where('tracking_no', 'LIKE', "%{$search}%")
                  ->orWhere('fname', 'LIKE', "%{$search}%")
                  ->orWhere('lname', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%");
            });
        })
          // PRIORITIZE STATUSES: 0, 1, and 2 will appear first
        ->orderByRaw("CASE 
            WHEN status = '0' THEN 1 
            WHEN status = '1' THEN 2 
            WHEN status = '2' THEN 3 
            ELSE 4 END ASC")
        ->orderBy('created_at', 'desc') // Then show the newest within those groups
        ->paginate(10)
        ->appends(['search' => $search]);

    return view('seller.orders.index', compact('orders'));
}

    /**
     * Show Order Details (Seller View)
     */
    public function show(Order $order)
    {
        $sellerId = auth('seller')->id();

        // ✅ FIXED: Load 'items.variant' so size (100g/1kg) shows up
        $order->load([
            'items.product' => function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            },
            'items.variant' 
        ]);

        // Filter: Remove items that don't belong to this seller from the view collection
        $order->setRelation('items', $order->items->filter(function ($item) {
            return $item->product !== null;
        }));

        if ($order->items->isEmpty()) {
            abort(403, 'Unauthorized access to order.');
        }

        return view('seller.orders.show', compact('order'));
    }

    /**
     * Update Order Status (STRICT SELLER FLOW)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer',
        ]);

        $sellerId = auth('seller')->id();

        // Ensure seller owns items in this order
        $order = Order::where('id', $id)
            ->whereHas('items.product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->firstOrFail();

        $currentStatus = (int) $order->status;
        $nextStatus    = (int) $request->status;

        $allowedTransitions = [
            0 => [1], // New → Received
            1 => [2], // Received → Packed
            2 => [3], // Packed → Handover to Head Office
        ];

        // Check if transition is valid
        if (
            !isset($allowedTransitions[$currentStatus]) ||
            !in_array($nextStatus, $allowedTransitions[$currentStatus])
        ) {
            return back()->with('error', 'Invalid order status change.');
        }

        // Update status
        $order->update([
            'status' => $nextStatus,
        ]);

        if ($nextStatus === 3) {
            return back()->with(
                'handover_success',
                'Order handed over to Head Office successfully. Our team will assign a delivery partner shortly.'
            );
        }

        return back()->with('success', 'Order status updated successfully.');
    }

    /**
     * Download Order PDF (Seller Only)
     */
    public function downloadPdf(Order $order)
    {
        $sellerId = auth('seller')->id();

        $order->load([
            'items.product' => function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            },
            'items.variant'
        ]);

        $order->setRelation('items', $order->items->filter(function ($item) {
            return $item->product !== null;
        }));

        if ($order->items->isEmpty()) {
            abort(403);
        }

        $pdf = Pdf::loadView('seller.orders.pdf', compact('order'));

        return $pdf->download('order-' . $order->tracking_no . '.pdf');
    }

    public function approveCancellation($id)
    {
        $sellerId = Auth::guard('seller')->id();

        // 1. Find the order and verify the seller owns items in it
        $order = Order::where('id', $id)
            ->whereHas('items.product', function ($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->firstOrFail();

        // 2. Only allow approval if it is currently "Requested" (Status 7)
        if ($order->status == 7) {
            $order->update([
                'status' => 8 // Move to "Seller Approved Cancellation"
            ]);

            // ✅ The order is now flagged for the Admin Head Office to refund money
            return back()->with('success', '✅ Cancellation approved. The request has been sent to the Head Office for final refund.');
        }

        return back()->with('error', 'This order is not in a cancellation request stage.');
    }
}