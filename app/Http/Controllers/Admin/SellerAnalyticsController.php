<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;

class SellerAnalyticsController extends Controller
{
    /**
     * Display a list of all reports submitted by sellers
     */
    public function index()
    {
        $reports = DB::table('submitted_reports')
            ->join('staff', 'submitted_reports.seller_id', '=', 'staff.id') 
            ->select('submitted_reports.*', 'staff.name as seller_name')
            ->orderBy('submitted_reports.created_at', 'desc')
            ->get();

        return view('admin.reports.seller_submissions', compact('reports'));
    }

    /**
     * Generate the PDF for the admin to view
     */
    public function viewReport($id)
{
    $report = DB::table('submitted_reports')->where('id', $id)->first();
    
    if (!$report) {
        return back()->with('error', 'Report not found.');
    }

    // Update status to 'viewed' when admin opens it
    DB::table('submitted_reports')->where('id', $id)->update(['status' => 'viewed']);

    $seller = DB::table('staff')->where('id', $report->seller_id)->first();
    $reportType = $report->report_type;
    
    // 1. Base Query: Only show approved or active products
    $query = Product::where('seller_id', $report->seller_id)
                    ->whereIn('status', ['approved', 'reapproved', 'active']);

    // 2. Apply Identical Logic to Seller's Controller
    if ($reportType == 'low_stock') {
        // Sync with seller: Stock 5 or less
        $products = $query->where('stock', '<=', 5)->orderBy('stock', 'asc')->get();
    } 
    elseif ($reportType == 'top_selling') {
        // FIX: Must include the threshold of more than 10 sales
        $products = $query->withSum('items as total_sold', 'qty')
                          ->having('total_sold', '>', 10)
                          ->orderBy('total_sold', 'desc')
                          ->get();
    } 
    else {
        // Slow moving: No sales in last 60 days
        $products = $query->whereDoesntHave('items', function($q) {
            $q->where('created_at', '>=', now()->subDays(60));
        })->get();
    }

    $stats = [
        'date' => date('d M Y, h:i A', strtotime($report->submitted_at)),
        'seller_name' => $seller->name,
        'report_title' => $report->report_name
    ];

    // Streams the PDF directly to the browser
    $pdf = Pdf::loadView('seller.reports.pdf', compact('products', 'reportType', 'stats'));
    return $pdf->stream('TradLanka_Report.pdf'); 
}
}