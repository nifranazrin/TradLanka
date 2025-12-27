<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;

class ProductController extends Controller
{
    /**
     * Show product detail (customer-facing).
     */
     public function show(Product $product, Request $request): View
{
    $currency = session('currency', 'LKR');
    $exchangeRate = 0.0032; //
   
    // 1. Load relations
    $product->load([
        'images' => function ($q) {
            $q->orderBy('sort_order')->orderBy('id');
        },
        'category',
        'seller',
        'variants',
        'reviews' => function ($q) {
            $q->where('status', 1)
              ->with('user')
              ->latest();
        }
    ]);

    // 2. APPLY CURRENCY CONVERSION
    if ($currency === 'USD') {
        // Convert main product price
        $product->price = $product->price * $exchangeRate;

        // Convert all variant prices
        foreach ($product->variants as $variant) {
            $variant->price = $variant->price * $exchangeRate;
        }
    }

        // Breadcrumb category (optional)
        $breadcrumbCategory = null;
        if ($request->has('from_category')) {
            $breadcrumbCategory = Category::where(
                'slug',
                $request->query('from_category')
            )->first();
        }


                $canReview = false;
                if (Auth::check()) {
                    $canReview = Order::where('user_id', Auth::id())
                        
                        ->where('orders.status', 5) 
                        
                        ->whereHas('items', function ($query) use ($product) {
                            $query->where('product_id', $product->id);
                        })
                        ->exists();
                }

                // Product visibility rules
                $publicStatuses = ['approved', 'active', 'reapproved'];
                $status = strtolower((string) ($product->status ?? ''));
                $isPublic = in_array($status, $publicStatuses, true);

                $isAdmin = Auth::guard('admin')->check();
                $isOwner = Auth::guard('seller')->check() && Auth::guard('seller')->id() === $product->seller_id;

                if ($isPublic || $isAdmin || $isOwner) {
                    return view('frontend.product.show', compact(
                        'product',
                        'breadcrumbCategory',
                        'canReview',
                        'currency' // Pass currency to the view for the $ / Rs. symbol
                    ));
                }

    abort(404);
}
}
