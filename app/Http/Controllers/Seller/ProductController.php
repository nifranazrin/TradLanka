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

// --- CHANGED: Use Staff instead of User ---
use App\Notifications\AdminProductNotification;
use App\Models\Staff; // <--- CHANGED THIS FROM 'User' TO 'Staff'
// ------------------------------------------

class ProductController extends Controller
{
    // Show seller's products with SMART SORTING
    public function index()
    {
        $sellerId = Auth::guard('seller')->id();

        $products = Product::where('seller_id', $sellerId)
            ->with(['category', 'images'])
            // SORTING LOGIC:
            // 1. Pending and Re-Approval items jump to the TOP (Priority 1)
            // 2. Approved/Rejected items go below (Priority 0)
            // 3. Within groups, sort by the most recently updated date
            ->orderByRaw("CASE 
                WHEN status IN ('pending', 'reapproval_pending') THEN 1 
                ELSE 0 
            END DESC")
            ->orderBy('updated_at', 'desc')
            ->get();

        // --- compute a presentational label for the status ---
        $products->each(function ($product) {
            if (isset($product->status) && $product->status === 'reapproval_pending') {
                $product->status_label = 'pending to re approve';
            } elseif (isset($product->status) && $product->status === 'pending') {
                $product->status_label = 'pending';
            } else {
                $product->status_label = $product->status;
            }
        });

        $categories = Category::where('status', 1)->get();

        return view('seller.products.index', compact('products', 'categories'));
    }

    // Store a new product
    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'images' => 'nullable|array|max:12',
            'images.*' => 'nullable|image|max:2048',
        ]);

        // Logging for debugging
        try {
            $allFilesKeys = array_keys($request->allFiles());
        } catch (\Throwable $e) {
            $allFilesKeys = null;
        }
        Log::info('[Seller\ProductController@store] Incoming files', [
            'has_front_image' => $request->hasFile('image'),
            'has_images_key' => $request->hasFile('images'),
            'allFiles_keys' => $allFilesKeys,
        ]);

        $sellerId = Auth::guard('seller')->id() ?? session('staff_id');
        if (!$sellerId) {
            Log::error('[Seller\ProductController@store] Seller ID missing.');
            return back()->withInput()->with('error', 'Authentication error: seller not found. Please login as seller and try again.');
        }

        $name = trim($request->name);

        // CASE-INSENSITIVE DUPLICATE CHECK
        $existing = Product::whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();
        if ($existing) {
            $existing->loadMissing('seller');
            $otherSeller = $existing->seller->name ?? 'another seller';

            if ($existing->seller_id == $sellerId) {
                return back()->withInput()->with('error', "You already added product '{$name}'.");
            } else {
                return back()->withInput()->with('error', "Product '{$name}' already exists (added by {$otherSeller}).");
            }
        }

        DB::beginTransaction();
        try {
            $product = new Product();
            $product->name = $name;
            $product->slug = Str::slug($name) . '-' . uniqid();
            $product->category_id = $request->category_id;
            $product->price = $request->price;
            $product->stock = $request->stock;
            $product->description = $request->description;
            $product->seller_id = $sellerId;
            
            // NEW PRODUCTS always start as 'pending'
            $product->status = 'pending';

            // MAIN IMAGE -> store in products/front
            if ($request->hasFile('image')) {
                $product->image = $request->file('image')->store('products/front', 'public');
            }

            $saved = $product->save();
            if (!$saved) {
                throw new \RuntimeException("Product->save() returned false");
            }
            Log::info('[Seller\ProductController@store] Product saved', ['product_id' => $product->id]);

            // Handle Gallery Images
            $files = $request->file('images', []);
            if (empty($files) && $request->allFiles()) {
                 foreach ($request->allFiles() as $key => $val) {
                    if (strpos($key, 'images') !== false) {
                        $files = is_array($val) ? $val : [$val];
                    }
                 }
            }
            
            // Normalize to array
            if ($files === null) {
                $files = [];
            } elseif (!is_array($files)) {
                $files = [$files];
            }

            // Persist gallery images
            $lastOrder = $product->images()->max('sort_order') ?? 0;
            foreach ($files as $i => $file) {
                if (! $file) continue;
                if (!($file instanceof \Illuminate\Http\UploadedFile)) continue;
                if (! $file->isValid()) continue;

                try {
                    $path = $file->store('products/gallery', 'public');
                    $product->images()->create([
                        'path' => $path,
                        'sort_order' => $lastOrder + $i + 1,
                    ]);
                } catch (\Throwable $e) {
                    Log::error("[Seller\ProductController@store] Failed to save gallery image: " . $e->getMessage());
                }
            }

            DB::commit();

            // --- NOTIFICATION START (New Product) ---
            try {
                // FIXED: Now searching in 'Staff' table instead of 'User'
                $admins = Staff::where('role', 'admin')->get(); 
                foreach ($admins as $admin) {
                    $admin->notify(new AdminProductNotification($product, 'new'));
                }
            } catch (\Throwable $e) {
                // Log the error but don't break the product creation
                Log::error('Notification failed: ' . $e->getMessage());
            }
            // --- NOTIFICATION END ---

            return redirect()->route('seller.products.index')->with('success', '✅ Product added successfully! Wait for admin approval.');

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('[Seller\ProductController@store] QueryException: ' . $e->getMessage(), [
                'product_name' => $name,
            ]);
            return back()->withInput()->with('error', "Unable to add product '{$name}'. DB error occurred.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Seller\ProductController@store] Error: ' . $e->getMessage(), [
                'product_name' => $name,
            ]);
            return back()->withInput()->with('error', "Unable to add product '{$name}'. Unexpected error occurred.");
        }
    }

    public function show($id)
    {
        $product = Product::with('images')
            ->where('seller_id', Auth::guard('seller')->id())
            ->where('id', $id)
            ->firstOrFail();
        return view('seller.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::with('images')
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

        $name = $request->input('name', $product->name ?? 'unknown');

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $product->name = trim($request->name);
            $product->category_id = $request->category_id;
            $product->price = $request->price;
            $product->stock = $request->stock;
            $product->description = $request->description;

            // MAIN IMAGE UPDATE
            if ($request->hasFile('image')) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $product->image = $request->file('image')->store('products/front', 'public');
            }

            // --- STATUS UPDATE LOGIC ---
            $currentStatus = strtolower($product->status);

            if (in_array($currentStatus, ['approved', 'reapproved', 'active'])) {
                $product->status = 'reapproval_pending';
            } elseif ($currentStatus === 'rejected') {
                $product->status = 'pending';
            }

            $saved = $product->save();
            if (!$saved) {
                throw new \RuntimeException("Product->save() returned false on update");
            }

            // HANDLE NEW GALLERY IMAGES
            $files = $request->file('images', []);
            if (empty($files) && $request->allFiles()) {
                 foreach ($request->allFiles() as $key => $val) {
                    if (strpos($key, 'images') !== false) {
                        $files = is_array($val) ? $val : [$val];
                    }
                 }
            }
            
            // Extra logic for nested file arrays
            if (empty($files) && $request->allFiles()) {
                $collected = [];
                foreach ($request->allFiles() as $key => $value) {
                    if (strpos($key, 'images') !== false) {
                        if (is_array($value)) {
                            foreach ($value as $f) {
                                $collected[] = $f;
                            }
                        } else {
                            $collected[] = $value;
                        }
                    }
                }
                if (!empty($collected)) {
                    $files = $collected;
                }
            }

            if ($files === null) {
                $files = [];
            } elseif (!is_array($files)) {
                $files = [$files];
            }

            $lastOrder = $product->images()->max('sort_order') ?? 0;
            foreach ($files as $i => $file) {
                if (!$file) continue;
                if (!($file instanceof \Illuminate\Http\UploadedFile)) continue;
                if (!$file->isValid()) continue;

                try {
                    $path = $file->store('products/gallery', 'public');
                    $product->images()->create([
                        'path' => $path,
                        'sort_order' => $lastOrder + $i + 1,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('[Seller\ProductController@update] Failed saving gallery image: ' . $e->getMessage());
                }
            }

            DB::commit();

            // --- NOTIFICATION START (Edited Product) ---
            try {
                // Only notify if status changed to re-approval
                if ($product->status == 'reapproval_pending') {
                    // FIXED: Now searching in 'Staff' table instead of 'User'
                    $admins = Staff::where('role', 'admin')->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new AdminProductNotification($product, 'update'));
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Notification failed: ' . $e->getMessage());
            }
            // --- NOTIFICATION END ---

            return redirect()->route('seller.products.index')
                ->with('success', '✅ Product updated! Status is now: ' . ucfirst(str_replace('_', ' ', $product->status)));

        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('[Seller\ProductController@update] QueryException: ' . $e->getMessage(), [
                'product_name' => $name,
            ]);
            return back()->withInput()->with('error', "Unable to update product '{$name}'. DB error occurred.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Seller\ProductController@update] Error: ' . $e->getMessage(), [
                'product_name' => $name,
            ]);
            return back()->withInput()->with('error', "Unable to update product '{$name}'. Unexpected error occurred.");
        }
    }

    public function deleteImage($imageId)
    {
        $img = ProductImage::findOrFail($imageId);
        // Security check
        if ($img->product->seller_id !== Auth::guard('seller')->id()) {
            abort(403);
        }
        if ($img->path) Storage::disk('public')->delete($img->path);
        $img->delete();
        return back()->with('success', 'Image removed.');
    }
}