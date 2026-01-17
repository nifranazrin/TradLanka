<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf; // Ensure you have dompdf installed

class CustomerManagementController extends Controller
{
    /**
     * Display a listing of customers with order stats and global filters.
     */
    public function index(Request $request)
    {
        try {
            $query = $this->applyFilters($request);

            $customers = $query->orderBy('total_spent', 'desc')
                               ->orderBy('created_at', 'desc')
                               ->get();

            $countriesList = $this->getCountriesList();

            return view('admin.customers.index', compact('customers', 'countriesList'));

        } catch (\Exception $e) {
            Log::error("Customer Index Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Could not load customer data.');
        }
    }

    /**
     * Download the filtered customer list as a PDF.
     */
    public function downloadPDF(Request $request)
    {
        try {
            // Apply the exact same filters used in the index view
            $query = $this->applyFilters($request);
            $customers = $query->orderBy('total_spent', 'desc')->get();

            $stats = [
                'report_title' => $request->market == 'local' ? 'Local Market Analysis' : ($request->market == 'international' ? 'International Market Analysis' : 'Global Customer Analysis'),
                'date' => now()->format('d M Y, h:i A'),
                'market' => $request->market ?? 'global'
            ];

            // Load the PDF view with stats and filtered customers
            $pdf = Pdf::loadView('admin.customers.report_pdf', compact('customers', 'stats'));
            
            return $pdf->download('TradLanka_Customer_Report_' . now()->format('Ymd') . '.pdf');

        } catch (\Exception $e) {
            Log::error("PDF Download Error: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF.');
        }
    }

    /**
     * Shared filter logic to keep index and download consistent.
     */
    private function applyFilters(Request $request)
    {
        $query = User::where('user_role', 0)
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total_price');

        if ($request->filled('market')) {
            if ($request->market == 'local') {
                $query->where('country', 'Sri Lanka');
            } else {
                $query->where('country', '!=', 'Sri Lanka');
            }
        }

        if ($request->filled('country_name')) {
            $query->where('country', $request->country_name);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'LIKE', "%$search%")
                  ->orWhere('name', 'LIKE', "%$search%")
                  ->orWhere('email', 'LIKE', "%$search%")
                  ->orWhere('country', 'LIKE', "%$search%")
                  ->orWhereHas('orders', function($orderQuery) use ($search) {
                      $orderQuery->where('tracking_no', 'LIKE', "%$search%");
                  });
            });
        }

        return $query;
    }

    private function getCountriesList()
    {
        return [
            'Sri Lanka' => 'lk', 'United Arab Emirates' => 'ae', 'Saudi Arabia' => 'sa',
            'Qatar' => 'qa', 'Oman' => 'om', 'Kuwait' => 'kw', 'United Kingdom' => 'gb',
            'France' => 'fr', 'Germany' => 'de', 'Italy' => 'it', 'Netherlands' => 'nl',
            'United States' => 'us', 'Canada' => 'ca', 'Australia' => 'au',
            'New Zealand' => 'nz', 'India' => 'in', 'Singapore' => 'sg',
            'Malaysia' => 'my', 'Japan' => 'jp', 'South Korea' => 'kr', 'Maldives' => 'mv'
        ];
    }

    public function show($id)
    {
        try {
            $customer = User::with(['orders' => fn($q) => $q->latest(), 'orders.items.product'])
                ->where('user_role', 0)
                ->findOrFail($id);

            return view('admin.customers.show', compact('customer'));
        } catch (\Exception $e) {
            Log::error("Customer Show Error: " . $e->getMessage());
            return redirect()->route('admin.customers.index')->with('error', 'Customer not found.');
        }
    }
}