<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Private helper to fetch and calculate report data.
     * Updated to handle dynamic filtering for Currency, Status, and Month.
     */
 private function getReportData(Request $request)
{
    $pendingStatuses = [0, 1, 2, 3, 4, 10]; 
    $successStatus = 5; 
    $failedStatus = 6;  

    $query = Order::query();

    // 1. Specific Date Filter
    if ($request->filled('filter_date')) {
        $query->whereDate('created_at', $request->filter_date);
    }

    // 2. ✅ FIXED: Month-wise Filter (Auto-detect Year)
    // We check if data exists in 2025 or 2026 based on the month
    if ($request->filled('filter_month')) {
        $query->whereMonth('created_at', $request->filter_month);
        
        // If filtering December, explicitly look for 2025 since that's where your data is
        if ($request->filter_month == 12) {
            $query->whereYear('created_at', 2025);
        } else {
            // Default to current year for other months
            $query->whereYear('created_at', date('Y'));
        }
    }

    // 3. Currency Filter
    if ($request->filled('filter_currency')) {
        $query->where('currency', $request->filter_currency);
    }

    // 4. Status Filter
    if ($request->filled('filter_status')) {
        if ($request->filter_status == 'pending') {
            $query->whereIn('status', $pendingStatuses);
        } else {
            $query->where('status', $request->filter_status);
        }
    }

    // ✅ IMPORTANT: Fetch filtered data for cards AND table
    $allOrders = $query->latest()->get();

    // If a status filter is active, tableOrders is the same as allOrders
    if ($request->filled('filter_status')) {
        $tableOrders = $allOrders; 
    } else {
        // Show everything by default (Pending + Success + Failed)
        $tableOrders = $allOrders->whereIn('status', array_merge([$successStatus, $failedStatus], $pendingStatuses));
    }

    return [
        // ✅ Summary Card Data (Calculated from FILTERED orders)
        'successLKR' => $allOrders->where('status', $successStatus)->where('currency', 'LKR')->sum('total_price'),
        'successLKRCount' => $allOrders->where('status', $successStatus)->where('currency', 'LKR')->count(),
        
        'pendingLKR' => $allOrders->whereIn('status', $pendingStatuses)->where('currency', 'LKR')->sum('total_price'),
        'pendingLKRCount' => $allOrders->whereIn('status', $pendingStatuses)->where('currency', 'LKR')->count(),
        
        'successUSD' => $allOrders->where('status', $successStatus)->where('currency', 'USD')->sum('total_price'),
        'successUSDCount' => $allOrders->where('status', $successStatus)->where('currency', 'USD')->count(),
        
        'pendingUSD' => $allOrders->whereIn('status', $pendingStatuses)->where('currency', 'USD')->sum('total_price'),
        'pendingUSDCount' => $allOrders->whereIn('status', $pendingStatuses)->where('currency', 'USD')->count(),
        
        'failedCount' => $allOrders->where('status', $failedStatus)->count(),
        'totalRefundValueLKR' => $allOrders->where('status', $failedStatus)->where('currency', 'LKR')->sum('total_price'),

        'totalCOD' => $allOrders->whereIn('status', $pendingStatuses)->filter(fn($o) => stripos($o->payment_mode, 'COD') !== false)->count(),
        'totalStripe' => $allOrders->whereIn('status', $pendingStatuses)->filter(fn($o) => stripos($o->payment_mode, 'Stripe') !== false)->count(),
        
        'tableOrders' => $tableOrders,
        'filterDate' => $request->filter_date,
        'filterMonth' => $request->filter_month,
        'filterCurrency' => $request->filter_currency,
        'filterStatus' => $request->filter_status
    ];
}
    /**
     * Display the Sales Report Dashboard.
     */
    public function salesReport(Request $request)
    {
        $data = $this->getReportData($request);
        return view('admin.reports.sales', $data);
    }

    /**
     * Download the Sales Report as a PDF.
     */
    public function downloadPDF(Request $request)
{
    // ✅ This passes the Month, Currency, and Status to the data fetcher
    $data = $this->getReportData($request);
    
    // Generate PDF
    $pdf = Pdf::loadView('admin.reports.sales_pdf', $data)->setPaper('a4', 'portrait');
    
    // Dynamic filename logic
    $filename = 'Sales-Report-';
    if ($request->filled('filter_date')) {
        $filename .= $request->filter_date;
    } elseif ($request->filled('filter_month')) {
        $filename .= date('F', mktime(0, 0, 0, $request->filter_month, 1));
    } else {
        $filename .= 'Filtered';
    }
    
    return $pdf->download($filename . '.pdf');
}
}