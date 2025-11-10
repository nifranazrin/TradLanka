<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    // Show seller's products
    public function index()
    {
        $sellerId = Auth::guard('seller')->id();

        $products = Product::where('seller_id', $sellerId)
            ->with('category')
            ->latest()
            ->get();

        $categories = Category::where('status', 1)->get();

        return view('seller.products.index', compact('products', 'categories'));
    }

    // Store a new product
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name) . '-' . uniqid();
        $product->category_id = $request->category_id;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->description = $request->description;

        // Assign seller ID
        $product->seller_id = Auth::guard('seller')->id() ?? session('staff_id');

        // Default status: pending until admin approves
        $product->status = 'pending';

        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->save();

        return redirect()->back()->with('success', '✅ Product added successfully! Wait for admin approval.');
    }

    // View single product details
    public function show($id)
    {
        $product = Product::where('seller_id', Auth::guard('seller')->id())->findOrFail($id);
        return view('seller.products.show', compact('product'));
    }

    // Show edit form (always allowed for the seller)
    public function edit($id)
    {
        $product = Product::where('seller_id', Auth::guard('seller')->id())->findOrFail($id);
        $categories = Category::where('status', 1)->get();

        // Sellers can now edit any product (approved or pending)
        return view('seller.products.edit', compact('product', 'categories'));
    }

    // Update existing product
    public function update(Request $request, $id)
    {
        $product = Product::where('seller_id', Auth::guard('seller')->id())->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $product->name = $request->name;
        $product->slug = Str::slug($request->name) . '-' . uniqid();
        $product->category_id = $request->category_id;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->description = $request->description;

        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store('products', 'public');
        }

        //  If product was approved before, mark as reapproval_pending
        if ($product->status === 'approved' || $product->status === 'reapproved') {
            $product->status = 'reapproval_pending';
        }

        $product->save();

        return redirect()->route('seller.products.index')
            ->with('success', '✅ Product updated successfully! Awaiting admin re-approval.');
    }
}
