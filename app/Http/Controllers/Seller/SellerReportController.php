<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// Import the PDF Facade for the download feature
use Barryvdh\DomPDF\Facade\Pdf;

class SellerReportController extends Controller
{
    public function __construct()
    {
        // Ensures only logged-in sellers can access these reports
        $this->middleware('auth:seller');
    }

    /**
     * Display the main selection page for reports
     */
    public function index()
    {
        return view('seller.reports.index');
    }

    /**
     * Generate Inventory & Stock Analytics for the Web View
     */
    public function inventoryResults(Request $request)
    {
        $sellerId = Auth::guard('seller')->id();
        $reportType = $request->input('report_type');

        $query = Product::where('seller_id', $sellerId);

        if ($reportType == 'low_stock') {
            // Shows all products with stock less than 5
            $products = $query->where('stock', '<', 5)
                              ->orderBy('stock', 'asc')
                              ->get();
        } 
        elseif ($reportType == 'top_selling') {
            // FIXED: Using 'qty' instead of 'quantity' based on your database error
            // Removed .take(10) to show all moving stock on the page
            $products = $query->withSum('items as total_sold', 'qty') 
                              ->orderBy('total_sold', 'desc')
                              ->get(); 
        }
        elseif ($reportType == 'slow_moving') {
            // Shows all products with NO sales in the last 60 days
            $products = $query->whereDoesntHave('items', function($q) {
                $q->where('created_at', '>=', now()->subDays(60));
            })->get();
        } else {
            return redirect()->route('seller.reports.index');
        }

        return view('seller.reports.inventory_results', compact('products', 'reportType'));
    }

    /**
     * Generate and Download Branded PDF Report
     */
    public function downloadPDF(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        $reportType = $request->query('report_type');
        
        $query = Product::where('seller_id', $seller->id);

        // Fetch same data as web view but for PDF generation
        if ($reportType == 'low_stock') {
            $products = $query->where('stock', '<', 5)->orderBy('stock', 'asc')->get();
        } elseif ($reportType == 'top_selling') {
            $products = $query->withSum('items as total_sold', 'qty')
                              ->orderBy('total_sold', 'desc')->get();
        } else {
            $products = $query->whereDoesntHave('items', function($q) {
                $q->where('created_at', '>=', now()->subDays(60));
            })->get();
        }

        // Stats for the branded header
        $stats = [
            'date' => now()->format('d M Y, h:i A'),
            'seller_name' => $seller->name,
            'report_title' => ucwords(str_replace('_', ' ', $reportType))
        ];

        // Generate PDF using the custom PDF blade view
        $pdf = Pdf::loadView('seller.reports.pdf', compact('products', 'reportType', 'stats'));
        
        // Setup A4 Paper size
        $pdf->setPaper('a4', 'portrait');

        // Trigger download
        return $pdf->download('TradLanka_' . $reportType . '_Report_' . now()->format('Ymd') . '.pdf');
    }

    /**
     * Submit the report record to the admin
     */
    public function submitToAdmin(Request $request)
    {
        $sellerId = Auth::guard('seller')->id();
        $reportType = $request->input('report_type');
        
        // Define friendly names for reports
        $reportNames = [
            'top_selling' => 'Top Selling Products Report',
            'low_stock'   => 'Low Stock Alert Report',
            'slow_moving' => 'Slow Moving Stock Report'
        ];

        // Insert into the submitted_reports table
        DB::table('submitted_reports')->insert([
            'seller_id'    => $sellerId,
            'report_type'  => $reportType,
            'report_name'  => $reportNames[$reportType] ?? 'Inventory Report',
            'submitted_at' => now(),
            'status'       => 'pending',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Redirect back with success message
        return back()->with('success', 'Report successfully submitted to Admin!');
    }
}