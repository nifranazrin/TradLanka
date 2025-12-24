<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariant; 

// --- Notifications ---
use App\Notifications\AdminProductNotification;
use App\Models\Staff;

class ProductController extends Controller
{
    public function index()
{
    $seller = Auth::guard('seller')->user();

    // Clear product notifications when visiting the page
    if ($seller) {
        $seller->unreadNotifications
            ->where('data.type', 'product')
            ->markAsRead();
    }

    $products = Product::where('seller_id', $seller->id)
        ->with(['category', 'images'])
        ->orderBy('updated_at', 'desc')
        ->get();

    $categories = Category::where('status', 1)->get();

    return view('seller.products.index', compact('products', 'categories'));
}
    // Store a new product
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'unit_type' => 'required|in:weight,liquid,default',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'images' => 'nullable|array|max:12',
            'images.*' => 'nullable|image|max:2048',
            
            // ✅ New Validation for Variants
            // If the user adds variants, these fields become required
            'variations' => 'nullable|array',
            'variations.*.unit_label' => 'required_with:variations|string',
            'variations.*.price' => 'required_with:variations|numeric',
            'variations.*.stock' => 'required_with:variations|integer',
        ]);

        $sellerId = Auth::guard('seller')->id() ?? session('staff_id');
        if (!$sellerId) {
            return back()->withInput()->with('error', 'Authentication error: seller not found.');
        }

        $name = trim($request->name);

        // Check for duplicates
        $existing = Product::whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();
        if ($existing) {
            return back()->withInput()->with('error', "Product '{$name}' already exists.");
        }

        DB::beginTransaction();
        try {
            // 2. Prepare Main Product Data
            // If variants exist, we will overwrite price/stock later with calculated values
            $mainPrice = $request->price ?? 0; 
            $mainStock = $request->stock ?? 0;

            // If variations are provided, calculate total stock and min price
            if ($request->has('variations') && count($request->variations) > 0) {
                $mainStock = 0;
                $prices = [];
                foreach ($request->variations as $v) {
                    $mainStock += $v['stock'];
                    $prices[] = $v['price'];
                }
                // Set main price to the lowest variant price (e.g. "Starts at 500")
                $mainPrice = min($prices);
            }

            $product = new Product();
            $product->name = $name;
            $product->slug = Str::slug($name) . '-' . uniqid();
            $product->category_id = $request->category_id;
            $product->unit_type = $request->unit_type;
            $product->price = $mainPrice; 
            $product->stock = $mainStock;
            $product->description = $request->description;
            $product->seller_id = $sellerId;
            $product->status = 'pending';

            if ($request->hasFile('image')) {
                $product->image = $request->file('image')->store('products/front', 'public');
            }

            $product->save(); // Save first to get ID

            // 3. Save Variants
            if ($request->has('variations')) {
                foreach ($request->variations as $variant) {
                    if(!empty($variant['unit_label']) && !empty($variant['price'])) {
                        ProductVariant::create([
                            'product_id' => $product->id,
                            'unit_label' => $variant['unit_label'],
                            'price'      => $variant['price'],
                            'stock'      => $variant['stock']
                        ]);
                    }
                }
            }

            // 4. Handle Gallery Images
            $this->handleGalleryImages($request, $product);

            DB::commit();

            // Notify Admin
            try {
                $admins = Staff::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new AdminProductNotification($product, 'new'));
                }
            } catch (\Throwable $e) {
                Log::error('Notification failed: ' . $e->getMessage());
            }

            return redirect()->route('seller.products.index')->with('success', '✅ Product added successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Seller\ProductController@store] Error: ' . $e->getMessage());
            return back()->withInput()->with('error', "Error adding product: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        $product = Product::with(['images', 'variants']) // ✅ Load variants
            ->where('seller_id', Auth::guard('seller')->id())
            ->where('id', $id)
            ->firstOrFail();
        return view('seller.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::with(['images', 'variants']) // ✅ Load variants for edit form
            ->where('seller_id', Auth::guard('seller')->id())
            ->where('id', $id)
            ->firstOrFail();
        $categories = Category::where('status', 1)->get();
        return view('seller.products.edit', compact('product', 'categories'));
    }

    // Update existing product
    public function update(Request $request, $id)
    {
        $product = Product::with('images')
            ->where('seller_id', Auth::guard('seller')->id())
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'unit_type' => 'required|in:weight,liquid,default',
            // Variants validation
            'variations' => 'nullable|array',
            'variations.*.unit_label' => 'required_with:variations|string',
            'variations.*.price' => 'required_with:variations|numeric',
            'variations.*.stock' => 'required_with:variations|integer',
        ]);

        DB::beginTransaction();
        try {
            // Calculate new totals if variants exist
            $mainPrice = $request->price;
            $mainStock = $request->stock;

            if ($request->has('variations') && count($request->variations) > 0) {
                $mainStock = 0;
                $prices = [];
                foreach ($request->variations as $v) {
                    $mainStock += $v['stock'];
                    $prices[] = $v['price'];
                }
                $mainPrice = min($prices);
            }

            $product->name = trim($request->name);
            $product->category_id = $request->category_id;
            $product->unit_type = $request->unit_type;
            $product->price = $mainPrice;
            $product->stock = $mainStock;
            $product->description = $request->description;

            if ($request->hasFile('image')) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $product->image = $request->file('image')->store('products/front', 'public');
            }

            // Status Logic
            $currentStatus = strtolower($product->status);
            if (in_array($currentStatus, ['approved', 'reapproved', 'active'])) {
                $product->status = 'reapproval_pending';
            } elseif ($currentStatus === 'rejected') {
                $product->status = 'pending';
            }

            $product->save();

            // ✅ UPDATE VARIANTS Logic
            // 1. Delete old variants (easiest way to handle updates/removals)
            ProductVariant::where('product_id', $product->id)->delete();

            // 2. Create new ones from the form
            if ($request->has('variations')) {
                foreach ($request->variations as $variant) {
                    if(!empty($variant['unit_label'])) {
                        ProductVariant::create([
                            'product_id' => $product->id,
                            'unit_label' => $variant['unit_label'],
                            'price'      => $variant['price'],
                            'stock'      => $variant['stock']
                        ]);
                    }
                }
            }

            // Handle Gallery Images
            $this->handleGalleryImages($request, $product);

            DB::commit();

            // Notify Admin
            try {
                if ($product->status == 'reapproval_pending') {
                    $admins = Staff::where('role', 'admin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new AdminProductNotification($product, 'update'));
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Notification failed: ' . $e->getMessage());
            }

            return redirect()->route('seller.products.index')
                ->with('success', '✅ Product updated! Status is now: ' . ucfirst(str_replace('_', ' ', $product->status)));

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Seller\ProductController@update] Error: ' . $e->getMessage());
            return back()->withInput()->with('error', "Update failed: " . $e->getMessage());
        }
    }

    // Helper to handle gallery upload to keep code clean
    private function handleGalleryImages($request, $product)
    {
        $files = $request->file('images', []);
        
        // Logic to extract files if nested (handling array quirks)
        if (empty($files) && $request->allFiles()) {
             foreach ($request->allFiles() as $key => $val) {
                if (strpos($key, 'images') !== false) {
                    $files = is_array($val) ? $val : [$val];
                }
             }
        }
        
        if ($files === null) $files = [];
        if (!is_array($files)) $files = [$files];

        $lastOrder = $product->images()->max('sort_order') ?? 0;
        
        foreach ($files as $i => $file) {
            if (!$file || !($file instanceof \Illuminate\Http\UploadedFile) || !$file->isValid()) continue;
            try {
                $path = $file->store('products/gallery', 'public');
                $product->images()->create([
                    'path' => $path,
                    'sort_order' => $lastOrder + $i + 1,
                ]);
            } catch (\Throwable $e) {
                Log::error('Gallery save error: ' . $e->getMessage());
            }
        }
    }

    public function deleteImage($imageId)
    {
        $img = ProductImage::findOrFail($imageId);
        if ($img->product->seller_id !== Auth::guard('seller')->id()) {
            abort(403);
        }
        if ($img->path) Storage::disk('public')->delete($img->path);
        $img->delete();
        return back()->with('success', 'Image removed.');
    }
}