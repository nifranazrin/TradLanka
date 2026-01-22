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
    public function index(Request $request) // Added Request $request here
    {
        $seller = Auth::guard('seller')->user();
        $search = $request->input('search'); // Capture the search term from the URL

        // Clear product notifications when visiting the page
        if ($seller) {
            $seller->unreadNotifications
                ->where('data.type', 'product')
                ->markAsRead();
        }

        // Modified Query to include Search
        $products = Product::where('seller_id', $seller->id)
            ->with(['category', 'images'])
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    // Search by product name or category name
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhereHas('category', function($catQuery) use ($search) {
                          $catQuery->where('name', 'LIKE', "%{$search}%");
                      });
                });
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        $categories = Category::where('status', 1)->get();

        return view('seller.products.index', compact('products', 'categories'));
    }
    // Store a new product
    public function store(Request $request)
{
    // 1. Validation: Make variations only required if NOT default
    $request->validate([
        'name' => 'required|string|max:255',
        'category_id' => 'required|integer|exists:categories,id',
        'unit_type' => 'required|in:weight,liquid,default',
        'description' => 'nullable|string',
        'image' => 'nullable|image|max:2048',
        'images' => 'nullable|array|max:12',
        'images.*' => 'nullable|image|max:2048',
        
        // Change: variations are only required if unit_type is NOT default
        'variations' => $request->unit_type === 'default' ? 'nullable|array' : 'required|array',
        'variations.*.unit_label' => 'required_if:unit_type,weight,liquid|string',
        'variations.*.price' => 'required|numeric',
        'variations.*.stock' => 'required|integer',
    ]);

    // ... (Your authentication and duplicate check logic) ...

    DB::beginTransaction();
    try {
        $product = new Product();
        $product->name = trim($request->name);
        $product->slug = Str::slug($request->name) . '-' . uniqid();
        $product->category_id = $request->category_id;
        $product->unit_type = $request->unit_type;
        $product->description = $request->description;
        $product->seller_id = Auth::guard('seller')->id();
        $product->status = 'pending';

        // 2. Logic for Default (No Unit)
        if ($request->unit_type === 'default') {
            // Take price and stock from the first variation row (set by JS)
            $firstVar = $request->variations[0] ?? ['price' => 0, 'stock' => 0];
            $product->price = $firstVar['price'];
            $product->stock = $firstVar['stock'];
        } else {
            // Existing logic for multiple variations
            $prices = collect($request->variations)->pluck('price');
            $product->price = $prices->min();
            $product->stock = collect($request->variations)->sum('stock');
        }

        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store('products/front', 'public');
        }

        $product->save();

        // 3. Save Variants
        if ($request->unit_type === 'default') {
            // Always create exactly one "Default" variant record for consistency
            ProductVariant::create([
                'product_id' => $product->id,
                'unit_label' => 'Default',
                'price'      => $request->variations[0]['price'] ?? 0,
                'stock'      => $request->variations[0]['stock'] ?? 0
            ]);
        } else {
            foreach ($request->variations as $variant) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'unit_label' => $variant['unit_label'],
                    'price'      => $variant['price'],
                    'stock'      => $variant['stock']
                ]);
            }
        }

        $this->handleGalleryImages($request, $product);
        DB::commit();

        // ... (Admin Notifications) ...

        return redirect()->route('seller.products.index')->with('success', '✅ Product added successfully!');
    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->withInput()->with('error', "Error: " . $e->getMessage());
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
        'variations' => 'nullable|array',
        'variations.*.unit_label' => 'required_with:variations|string',
        'variations.*.price' => 'required_with:variations|numeric',
        'variations.*.stock' => 'required_with:variations|integer',
    ]);

    DB::beginTransaction();
    try {
        // 1. Calculate new totals
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

        // 2. Capture the current stock from DB before updating the object
        $wasOutOfStock = ($product->stock == 0);

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

        // 3. Status Logic
        $currentStatus = strtolower($product->status);
        if (in_array($currentStatus, ['approved', 'reapproved', 'active'])) {
            $product->status = 'reapproval_pending';
        } elseif ($currentStatus === 'rejected') {
            $product->status = 'pending';
        }

        $product->save();

        // 4. TRIGGER: Send In-App Notifications if restocked
        if ($wasOutOfStock && $product->stock > 0) {
            $users = \App\Models\User::whereHas('carts', function($q) use ($product) {
                $q->where('product_id', $product->id);
            })->get();

            foreach ($users as $user) {
                $user->notify(new \App\Notifications\CustomerRestockNotification($product));
            }
        }

        // 5. UPDATE VARIANTS Logic
        ProductVariant::where('product_id', $product->id)->delete();
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

        $this->handleGalleryImages($request, $product);
        DB::commit();

        // 6. Notify Admin of the update
        try {
            if ($product->status == 'reapproval_pending') {
                $admins = Staff::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new AdminProductNotification($product, 'update'));
                }
            }
        } catch (\Throwable $e) {
            Log::error('Admin Notification failed: ' . $e->getMessage());
        }

        return redirect()->route('seller.products.index')
            ->with('success', '✅ Product updated! Status: ' . ucfirst(str_replace('_', ' ', $product->status)));

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