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
     * This ensures the dashboard and the PDF always show the same numbers.
     */
    private function getReportData(Request $request)
    {
        $pendingStatuses = [0, 1, 2, 3, 4, 10]; 
        $successStatus = 5; 
        $failedStatus = 6;  

        $query = Order::query();

        // Apply single-day filter if present
        if ($request->filled('filter_date')) {
            $query->whereDate('created_at', $request->filter_date);
        }

        $allOrders = $query->latest()->get();

        return [
            // LKR Data
            'successLKR' => $allOrders->where('status', $successStatus)->where('currency', 'LKR')->sum('total_price'),
            'successLKRCount' => $allOrders->where('status', $successStatus)->where('currency', 'LKR')->count(),
            'pendingLKR' => $allOrders->whereIn('status', $pendingStatuses)->where('currency', 'LKR')->sum('total_price'),
            'pendingLKRCount' => $allOrders->whereIn('status', $pendingStatuses)->where('currency', 'LKR')->count(),
            
            // USD Data
            'successUSD' => $allOrders->where('status', $successStatus)->where('currency', 'USD')->sum('total_price'),
            'successUSDCount' => $allOrders->where('status', $successStatus)->where('currency', 'USD')->count(),
            'pendingUSD' => $allOrders->whereIn('status', $pendingStatuses)->where('currency', 'USD')->sum('total_price'),
            'pendingUSDCount' => $allOrders->whereIn('status', $pendingStatuses)->where('currency', 'USD')->count(),
            
            // Payment Mode Pipeline (Pending Only)
            'totalCOD' => $allOrders->whereIn('status', $pendingStatuses)
                ->filter(fn($o) => stripos($o->payment_mode, 'COD') !== false)->count(),
            'totalStripe' => $allOrders->whereIn('status', $pendingStatuses)
                ->filter(fn($o) => stripos($o->payment_mode, 'Stripe') !== false)->count(),
            
            // Failure Data
            'failedCount' => $allOrders->where('status', $failedStatus)->count(),
            'totalRefundValueLKR' => $allOrders->where('status', $failedStatus)->where('currency', 'LKR')->sum('total_price'),
            
            // Table List (Finalized Outcomes)
            'tableOrders' => $allOrders->whereIn('status', [$successStatus, $failedStatus]),
            'filterDate' => $request->filter_date
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
        $data = $this->getReportData($request);
        
        // Generate PDF using the separate slimmed-down blade file
        $pdf = Pdf::loadView('admin.reports.sales_pdf', $data)->setPaper('a4', 'portrait');
        
        $filename = 'Sales-Report-' . ($data['filterDate'] ?? 'All-Time') . '.pdf';
        return $pdf->download($filename);
    }
}