<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Show product detail (customer-facing).
     *
     * Uses route-model binding: route should be defined as:
     * Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product.show');
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\View\View
     */
    public function show(Product $product): View
    {
        // Eager load relations and order gallery images if you use sort_order
        $product->load([
            'images' => function ($q) {
                $q->orderBy('sort_order')->orderBy('id');
            },
            'category',
            'seller'
        ]);

        // Acceptable public statuses (case-insensitive)
        $publicStatuses = ['approved', 'active', 'reapproved'];
        $status = strtolower((string) ($product->status ?? ''));

        // If product is public, show normally
        if (in_array($status, $publicStatuses, true)) {
            return view('frontend.product.show', compact('product'));
        }

        // Allow preview for admins
        if (Auth::guard('admin')->check()) {
            return view('frontend.product.show', compact('product'));
        }

        // Allow preview for the owning seller
        if (Auth::guard('seller')->check() && Auth::guard('seller')->id() === $product->seller_id) {
            return view('frontend.product.show', compact('product'));
        }

        // Otherwise hide the product
        abort(404);
    }
}
