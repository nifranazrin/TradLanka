<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    // 1. Home Page (Landing Page)
    public function home()
    {
        // Fetch only active MAIN categories (where parent_id is NULL)
        $categories = Category::where('status', 1)
            ->whereNull('parent_id')
            ->orderBy('sort_order', 'asc')
            ->get();

        // Fetch latest approved & active products for "New Arrivals"
        $products = Product::whereIn('status', ['approved', 'reapproved'])
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('frontend.home', compact('categories', 'products'));
    }

    // 2. Category Page
    public function productsByCategory(Request $request, $slug)
    {
        // 1. Find the Category
        $category = Category::where('slug', $slug)
            ->where('status', 1)
            ->firstOrFail();

        // 2. SIDEBAR LOGIC
        // --------------------------------------------------------

        // CASE A: Does it have Sub-Categories? (e.g. Beauty -> Skin Care, Hair Care)
        if ($category->subcategories->count() > 0) {
            $sidebarItems = $category->subcategories;
            $sidebarType = 'category'; // Links will go to Category Pages
            $sidebarTitle = $category->name . ' Categories';
        }
        // CASE B: No Sub-Categories? Show Products
        else {
            $sidebarItems = Product::where('category_id', $category->id)
                ->whereIn('status', ['approved', 'reapproved'])
                ->where('is_active', 1)
                ->orderBy('name', 'asc')
                ->get();

            $sidebarType = 'product'; // Links will go to Product Detail Pages
            $sidebarTitle = $category->name . ' Items';
        }
        // --------------------------------------------------------

        // 3. Main Product Grid Query
        $query = Product::where('category_id', $category->id)
            ->whereIn('status', ['approved', 'reapproved'])
            ->where('is_active', 1);

        // Price Filter Sorting
        if ($request->sort == 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($request->sort == 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12);

        // Pass all variables to the view
        return view('frontend.category.show', compact('category', 'products', 'sidebarItems', 'sidebarType', 'sidebarTitle'));
    }

    // 3. Search Results Page (ADDED THIS FUNCTION)
    public function searchPage(Request $request)
    {
        $query = $request->input('query');

        // Search logic: Find products where Name OR Description contains the query
        $products = Product::whereIn('status', ['approved', 'reapproved']) // Only approved items
            ->where('is_active', 1) // Only active items
            ->where(function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->paginate(12); // Pagination (12 items per page)

        // Return the search view
        return view('frontend.pages.search', compact('products', 'query'));
    }
}