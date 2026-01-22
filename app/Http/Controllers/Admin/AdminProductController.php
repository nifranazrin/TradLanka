<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Staff; 
use App\Notifications\SellerDashboardNotification;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Auth; 

class AdminProductController extends Controller
{
    // Display all products with their sellers.
    public function index()
    {
       
        $products = Product::with(['seller', 'images'])
            ->orderByRaw("CASE 
                WHEN status IN ('pending', 'reapproval_pending') THEN 1 
                ELSE 0 
            END DESC")
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.products.index', compact('products'));
    }

    // Show single product details.
    public function show($id)
    {
        $product = Product::with(['seller', 'images'])->findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    // Approve or Re-Approve a product.
    public function approve($id)
    {
        $product = Product::findOrFail($id);

        if ($product->status === 'reapproval_pending') {
            $product->status = 'reapproved';
            $message = '✅ Product re-approved successfully!';
            $notifText = "Your product '{$product->name}' has been re-approved.";
        } else {
            $product->status = 'approved';
            $message = '✅ Product approved successfully!';
            $notifText = "Your product '{$product->name}' has been approved!";
        }

        $product->is_active = 1;
        $product->approved_at = now();
        $product->save();

        // ---  NOTIFICATION TRIGGER ---
        try {
            $seller = Staff::find($product->seller_id);
            if ($seller) {
               
                $seller->notify(new SellerDashboardNotification('product', $notifText, $product->id));
            }
        } catch (\Exception $e) {
           
            Log::error('Notification Error: ' . $e->getMessage());
        }

        return back()->with('success', $message);
    }

    // Reject a product.
    public function reject($id)
    {
        $product = Product::findOrFail($id);
        $product->status = 'rejected';
        $product->is_active = 0;
        $product->save();

        // --- SAFE NOTIFICATION TRIGGER ---
        try {
            $seller = Staff::find($product->seller_id);
            if ($seller) {
                $seller->notify(new SellerDashboardNotification('product', "Your product '{$product->name}' was rejected.", $product->id));
            }
        } catch (\Exception $e) {
            Log::error('Notification Error: ' . $e->getMessage());
        }

        return back()->with('error', '❌ Product rejected successfully.');
    }
}