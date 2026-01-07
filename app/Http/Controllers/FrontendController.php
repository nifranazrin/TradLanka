<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ContactMessage;
use App\Models\Banner;
use App\Models\Order;
use App\Models\ProductView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class FrontendController extends Controller
{
    protected $exchangeRate = 0.0032;

    public function home()
    {
        // 1. Categories
        $categories = Category::where('status', 1)->whereNull('parent_id')->orderBy('sort_order', 'asc')->get();

        // 2. Popular Categories
        $popularCategories = Category::where('categories.status', 1) 
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->select('categories.id', 'categories.name', 'categories.slug', 'categories.image', DB::raw('SUM(order_items.qty) as total_sold'))
            ->groupBy('categories.id', 'categories.name', 'categories.slug', 'categories.image')
            ->orderByDesc('total_sold')->take(3)->get();

        if ($popularCategories->isEmpty()) {
            $popularCategories = Category::where('status', 1)->take(3)->get();
        }

        // 3. New Arrivals
        $products = Product::whereIn('status', ['approved', 'reapproved'])->where('is_active', 1)->latest()->take(5)->get();

        
        // 4. Best Sellers (Lively Logic)
    $bestSellers = Product::select('products.*', DB::raw('SUM(order_items.qty) as total_sales'))
        ->join('order_items', 'products.id', '=', 'order_items.product_id')
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->whereIn('orders.status', [4, 5, 6]) // Status 4, 5, 6 for processed/delivered
        ->where('products.is_active', 1)
        ->whereIn('products.status', ['approved', 'reapproved'])
        ->groupBy(
            'products.id', 'products.name', 'products.slug', 'products.price', 
            'products.image', 'products.status', 'products.is_active', 
            'products.category_id', 'products.seller_id', 'products.description', 
            'products.stock', 'products.unit_type', 'products.approved_at', 
            'products.created_at', 'products.updated_at'
        )
        ->orderByDesc('total_sales')->take(5)->get();

    //Banner
    $banner = Banner::where('section_name', 'home_festive_offer')->first();

        // 5. AI Recommendations (Text-Based)
        $recommendedProducts = $this->getAIRecommendations();

        // 6. Currency Conversion
       if (session('currency') === 'USD') {
            $products->each(fn($p) => $p->price = (float)$p->price * $this->exchangeRate);
            $bestSellers->each(fn($p) => $p->price = (float)$p->price * $this->exchangeRate);
            
            if ($recommendedProducts) {
                $recommendedProducts->each(fn($p) => $p->price = (float)$p->price * $this->exchangeRate);
            }
        }

        return view('frontend.home', compact('categories', 'popularCategories', 'products', 'banner', 'bestSellers', 'recommendedProducts'));
    }

   public function productsByCategory(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->where('status', 1)->firstOrFail();
        $currency = session('currency', 'LKR');
        $categoryIds = Category::where('parent_id', $category->id)->pluck('id')->push($category->id)->all();

        if ($category->subcategories->count() > 0) {
            $sidebarItems = $category->subcategories;
            $sidebarType  = 'category';
            $sidebarTitle = $category->name . ' Categories';
        } else {
            $sidebarItems = Product::whereIn('category_id', $categoryIds)->where('is_active', 1)->orderBy('name', 'asc')->get();
            $sidebarType  = 'product';
            $sidebarTitle = $category->name . ' Items';
        }

        $query = Product::whereIn('category_id', $categoryIds)->whereIn('status', ['approved', 'reapproved'])->where('is_active', 1);

        if ($request->sort === 'price_asc') { $query->orderBy('price', 'asc'); } 
        elseif ($request->sort === 'price_desc') { $query->orderBy('price', 'desc'); } 
        else { $query->orderBy('created_at', 'desc'); }

        $products = $query->paginate(12);

        // ✅ Apply Correct Currency Conversion
        if ($currency === 'USD') {
            $products->getCollection()->transform(function ($product) {
                // Use explicit float casting to match home() logic
                $product->price = (float)$product->price * $this->exchangeRate;
                return $product;
            });

            if ($sidebarType === 'product') {
                $sidebarItems->each(function($p) {
                    $p->price = (float)$p->price * $this->exchangeRate;
                });
            }
        }

        return view('frontend.category.show', compact('category', 'products', 'sidebarItems', 'sidebarType', 'sidebarTitle'));
    }

    public function searchPage(Request $request)
    {
        $query = $request->input('query');
        $currency = session('currency', 'LKR');
        
        if ($query === 'best sellers') {
            $products = Product::where('products.is_active', 1)
                ->whereIn('products.status', ['approved', 'reapproved'])
                ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                ->select('products.*', DB::raw('SUM(order_items.qty) as total_sales'))
                ->groupBy(
                    'products.id', 'products.name', 'products.slug', 'products.price', 
                    'products.image', 'products.status', 'products.is_active', 
                    'products.category_id', 'products.seller_id', 'products.description', 
                    'products.stock', 'products.unit_type', 'products.approved_at', 
                    'products.created_at', 'products.updated_at'
                )
                ->orderByDesc('total_sales')
                ->paginate(12);

        } elseif ($query === 'new arrivals') {
            $products = Product::where('is_active', 1)
                ->whereIn('status', ['approved', 'reapproved'])
                ->latest()
                ->take(12)
                ->get(); 
        } else {
            $products = Product::where('is_active', 1)
                ->whereIn('status', ['approved', 'reapproved'])
                ->where(fn($q) => $q->where('name', 'LIKE', "%{$query}%")
                                    ->orWhere('description', 'LIKE', "%{$query}%"))
                ->paginate(12);
        }

        // ✅ NEW: Add Currency Conversion to Search Results
        if ($currency === 'USD') {
            if ($products instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $products->getCollection()->transform(function ($p) {
                    $p->price = (float)$p->price * $this->exchangeRate;
                    return $p;
                });
            } else {
                // Standard collection for 'new arrivals' search
                $products->each(function($p) {
                    $p->price = (float)$p->price * $this->exchangeRate;
                });
            }
        }

        return view('frontend.pages.search', compact('products', 'query'));
    }

    /**
     * AI Recommendation Logic (Text-Based)
     */
    private function getAIRecommendations() {
        try {
            // Use 'web' guard to handle standard users correctly
            $userId = Auth::guard('web')->id(); 
            $sessionId = Session::getId();

            // 1. Get History IDs (Product IDs, not filenames)
            $historyIds = ProductView::where(function($q) use ($userId, $sessionId) {
                    if ($userId) $q->where('user_id', $userId);
                    $q->orWhere('session_id', $sessionId);
                })
                ->latest()
                ->limit(10) // Look at last 10 items viewed
                ->pluck('product_id')
                ->toArray();

            if (!empty($historyIds)) {
                // 2. Call Python Text API
                // Note: We use /recommend-text because we are sending IDs for TF-IDF matching
                $response = Http::timeout(2)->post('http://127.0.0.1:5000/recommend-text', [
                    'history_ids' => $historyIds
                ]);

                if ($response->successful()) {
                    $recIds = $response->json(); // Returns array of recommended IDs

                    if (!empty($recIds)) {
                        // 3. Fetch Products from DB
                        $products = Product::whereIn('id', $recIds)
                            ->where('is_active', 1)
                            ->whereIn('status', ['approved', 'reapproved'])
                            ->get();
                        
                        // 4. Sort results to match the AI's relevance order
                        return $products->sortBy(function($model) use ($recIds) {
                            return array_search($model->id, $recIds);
                        });
                    }
                }
            }
        } catch (\Exception $e) { 
            Log::error("AI Error: " . $e->getMessage()); 
        }
        return collect();
    }

    public function about() { return view('frontend.about'); }
    public function contact() { return view('frontend.contact'); }

    public function submitContact(Request $request) {
        $request->validate(['first_name' => 'required', 'email' => 'required|email', 'message' => 'required']);
        ContactMessage::create($request->all());
        return back()->with('success', 'Message sent successfully!');
    }

    public function trackOrder(Request $request) {
        if (!$request->filled('tracking_no')) return view('frontend.orders.track');
        $order = Order::with(['orderItems.product'])->where('tracking_no', $request->tracking_no)->first();
        if (!$order) return back()->withInput()->with('status', 'No order found.');
        return view('frontend.orders.track', compact('order'));
    }
}