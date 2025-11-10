<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function home()
    {
        //  Fetch only active categories (Shop by Category section)
        $categories = Category::where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->get();

        //  Fetch latest approved & active products for "New Arrivals"
        $products = Product::whereIn('status', ['approved', 'reapproved'])
            ->where('is_active', 1)             // make sure only visible ones show
            ->orderBy('approved_at', 'desc')    // newest approvals first
            ->take(10)
            ->get();

        //  Pass both variables to the Blade view
        return view('frontend.home', compact('categories', 'products'));
    }
}
