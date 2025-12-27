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
    public function index()
{
    // ✅ Use the explicit 'seller' guard for both ID and User instance
    $sellerId = Auth::guard('seller')->id();
    $seller = Auth::guard('seller')->user();

    // Security Check: Redirect if not authenticated
    if (!$seller) {
        return redirect()->route('seller.login');
    }

    // --- SAFE NOTIFICATION CLEARING ---
    // Marks order notifications as read when the seller visits this list
    $seller->unreadNotifications
        ->where('data.type', 'order')
        ->markAsRead();

    // ✅ Relationship Check: 
    // Ensure 'items' is defined in your Order model and 'product' in your OrderItem model
    $orders = Order::whereHas('items.product', function ($query) use ($sellerId) {
            $query->where('seller_id', $sellerId);
        })
        ->with(['items.product'])
        ->latest()
        ->paginate(10);

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
}