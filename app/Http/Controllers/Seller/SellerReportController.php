<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SellerReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:seller');
    }

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

        // ✅ ONLY approved, reapproved, or active products
        $query = Product::where('seller_id', $sellerId)
                        ->whereIn('status', ['approved', 'reapproved', 'active']);

        if ($reportType == 'low_stock') {
            // ✅ Updated: Shows products with stock 5 or less
            $products = $query->where('stock', '<=', 5)
                              ->orderBy('stock', 'asc')
                              ->get();
        } 
        elseif ($reportType == 'top_selling') {
            // ✅ Updated: Only products with more than 10 sales
            $products = $query->withSum('items as total_sold', 'qty') 
                              ->having('total_sold', '>', 10)
                              ->orderBy('total_sold', 'desc')
                              ->get(); 
        }
        elseif ($reportType == 'slow_moving') {
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
        
        // ✅ Applying same status safety filter for PDF
        $query = Product::where('seller_id', $seller->id)
                        ->whereIn('status', ['approved', 'reapproved', 'active']);

        if ($reportType == 'low_stock') {
            // ✅ Sync with web view: 5 or less
            $products = $query->where('stock', '<=', 5)->orderBy('stock', 'asc')->get();
        } elseif ($reportType == 'top_selling') {
            // ✅ Sync with web view: above 10 sales
            $products = $query->withSum('items as total_sold', 'qty')
                              ->having('total_sold', '>', 10)
                              ->orderBy('total_sold', 'desc')->get();
        } else {
            $products = $query->whereDoesntHave('items', function($q) {
                $q->where('created_at', '>=', now()->subDays(60));
            })->get();
        }

        $stats = [
            'date' => now()->format('d M Y, h:i A'),
            'seller_name' => $seller->name,
            'report_title' => ucwords(str_replace('_', ' ', $reportType))
        ];

        $pdf = Pdf::loadView('seller.reports.pdf', compact('products', 'reportType', 'stats'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('TradLanka_' . $reportType . '_Report_' . now()->format('Ymd') . '.pdf');
    }

    public function submitToAdmin(Request $request)
    {
        $sellerId = Auth::guard('seller')->id();
        $reportType = $request->input('report_type');
        
        $reportNames = [
            'top_selling' => 'Top Selling Products Report',
            'low_stock'   => 'Low Stock Alert Report',
            'slow_moving' => 'Slow Moving Stock Report'
        ];

        DB::table('submitted_reports')->insert([
            'seller_id'    => $sellerId,
            'report_type'  => $reportType,
            'report_name'  => $reportNames[$reportType] ?? 'Inventory Report',
            'submitted_at' => now(),
            'status'       => 'pending',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return back()->with('success', 'Report successfully submitted to Admin!');
    }
}