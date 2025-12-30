<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;

class ReportController extends Controller
{
    /**
     * ✅ REPORT 1: Inventory & Stock Recovery
     * Tracks current levels and items returned from failed deliveries
     */
    public function inventoryReport()
    {
        // Get all products to show current stock levels
        $currentStock = Product::select('id', 'name', 'stock', 'price')
            ->orderBy('stock', 'asc') // Low stock first
            ->get();

        // Get orders that were finalized as "Failed/Cancelled" (Status 6)
        // This shows the history of stock being put back into the system
        $recoveredOrders = Order::with('items.product')
            ->where('status', 6)
            ->whereNotNull('cancel_reason') 
            ->latest()
            ->paginate(15);

        return view('admin.reports.inventory', compact('currentStock', 'recoveredOrders'));
    }

            public function salesReport(Request $request)
        {
            // Fetch all successfully delivered (Status 5) or Paid orders
            $orders = Order::whereIn('status', [5, 4, 3])->get();

            // Grouping Sales by Currency
            $totalLKR = $orders->filter(function($order) {
                return str_contains(strtoupper($order->payment_mode), 'LKR') || $order->currency == 'LKR';
            })->sum('total_price');

            $totalUSD = $orders->filter(function($order) {
                return str_contains(strtoupper($order->payment_mode), 'USD') || $order->currency == 'USD';
            })->sum('total_price');

            // Grouping by Payment Method
            $stripeSales = $orders->filter(fn($o) => !str_contains(strtoupper($o->payment_mode), 'COD'))->sum('total_price');
            $codSales = $orders->filter(fn($o) => str_contains(strtoupper($o->payment_mode), 'COD'))->sum('total_price');

            return view('admin.reports.sales', compact('totalLKR', 'totalUSD', 'stripeSales', 'codSales', 'orders'));
        }
}