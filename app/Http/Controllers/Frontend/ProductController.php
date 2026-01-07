<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\ProductView; // Required for Recommendation System

class ProductController extends Controller
{
    /**
     * Show product detail (customer-facing).
     */
    public function show(Product $product, Request $request): View
    {
        $currency = session('currency', 'LKR');
        $exchangeRate = 0.0032; 

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

        // 2. RECOMMENDATION SYSTEM TRACKING
        // We track the view in the database so the AI knows what the user likes.
        $userId = Auth::guard('web')->id(); 
        $sessionId = Session::getId();

        ProductView::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'product_id' => $product->id,
        ]);

        // 3. APPLY CURRENCY CONVERSION
      
            if ($currency === 'USD') {
                // Convert main product price using explicit float casting
                $product->price = (float)$product->price * $exchangeRate;

                // Convert all variant prices
                foreach ($product->variants as $variant) {
                    $variant->price = (float)$variant->price * $exchangeRate;
                }
            }

        // 4. Breadcrumb category (optional)
        $breadcrumbCategory = null;
        if ($request->has('from_category')) {
            $breadcrumbCategory = Category::where(
                'slug',
                $request->query('from_category')
            )->first();
        }

        // 5. Check if User can Review
        $canReview = false;
        if (Auth::guard('web')->check()) {
            $canReview = Order::where('user_id', Auth::guard('web')->id())
                ->where('orders.status', 5) // Assuming 5 = Delivered
                ->whereHas('items', function ($query) use ($product) {
                    $query->where('product_id', $product->id);
                })
                ->exists();
        }

        // 6. Product visibility rules
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
                'currency'
            ));
        }

        abort(404);
    }
}