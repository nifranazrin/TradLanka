<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class AdminProductController extends Controller
{
    // Display all products with their sellers.
    public function index()
    {
        $products = Product::with('seller')->latest()->get();
        return view('admin.products.index', compact('products'));
    }

    // Show single product details.
    public function show($id)
    {
        $product = Product::with('seller')->findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    // Approve or Re-Approve a product.
    public function approve($id)
    {
        $product = Product::findOrFail($id);

        //  Safe improvement: ensure approved items become active and visible on frontend
        if ($product->status === 'reapproval_pending') {
            $product->status = 'reapproved';
            $product->is_active = 1;       // make sure product is visible
            $product->approved_at = now(); // optional tracking
            $message = '✅ Product re-approved successfully!';
        } else {
            $product->status = 'approved';
            $product->is_active = 1;       // make it visible
            $product->approved_at = now(); // optional tracking
            $message = '✅ Product approved successfully!';
        }

        $product->save();

        return back()->with('success', $message);
    }

    // Reject a product.
    public function reject($id)
    {
        $product = Product::findOrFail($id);
        $product->status = 'rejected';
        $product->is_active = 0; // hide rejected ones from frontend
        $product->save();

        return back()->with('error', '❌ Product rejected successfully.');
    }
}
