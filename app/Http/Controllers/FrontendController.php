<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ContactMessage;
use App\Models\Banner;
use App\Models\Order;
use App\Models\Staff;
use App\Models\ProductView;
use App\Notifications\SellerDashboardNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class FrontendController extends Controller
{
    /**
     * 1. Home Page - Logic corrected to show single rows (5 items)
     */
    public function home()
    {
        $categories = Category::where('status', 1)
            ->whereNull('parent_id')
            ->orderBy('sort_order', 'asc')
            ->get();

        $products = Product::whereIn('status', ['approved', 'reapproved'])
            ->where('is_active', 1)
            ->latest()
            ->take(5)
            ->get();

        $bestSellers = Product::select('products.*', DB::raw('SUM(order_items.qty) as total_sales'))
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 4) 
            ->where('products.is_active', 1)
            ->groupBy(
                'products.id', 'products.name', 'products.slug', 'products.price',
                'products.image', 'products.status', 'products.is_active',
                'products.category_id', 'products.seller_id', 'products.description',
                'products.stock', 'products.unit_type', 'products.approved_at',
                'products.created_at', 'products.updated_at'
            )
            ->orderByDesc('total_sales')
            ->take(5)
            ->get();

        if ($bestSellers->isEmpty()) {
            $bestSellers = Product::where('is_active', 1)
                ->whereIn('status', ['approved', 'reapproved'])
                ->inRandomOrder()
                ->take(5) 
                ->get();
        }

        $banner = Banner::where('section_name', 'home_festive_offer')->first();

        $recommendedProducts = collect(); // Default empty collection

        try {
            // 1. Get current identity (Supports both logged-in users and guests)
            $userId = Auth::guard('customer')->id() ?? Auth::id(); 
            $sessionId = Session::getId();

            // 2. Fetch History - Checks BOTH user_id and session_id to find matches 
            // like those seen in your database (e.g., user_id 7)
            $recentViews = ProductView::with('product')
                ->where(function($q) use ($userId, $sessionId) {
                    if ($userId) {
                        $q->where('user_id', $userId);
                    }
                    // Always include current session history as well
                    $q->orWhere('session_id', $sessionId);
                })
                ->latest()
                ->take(10) 
                ->get();

            // 3. Extract unique image filenames from the history
            $viewedFilenames = $recentViews->map(function ($view) {
                return $view->product->image ?? null;
            })->filter()->unique()->values()->toArray();

            // 4. Debugging: This allows you to verify history is found in storage/logs/laravel.log
            Log::info('AI Recommend - Filenames sent to Python:', ['files' => $viewedFilenames]);

            if (!empty($viewedFilenames)) {
                // 5. Call Python API with a timeout to prevent site hang if Flask is down
                $response = Http::timeout(3)->post('http://127.0.0.1:5000/recommend', [
                    'filenames' => $viewedFilenames
                ]);

                if ($response->successful()) {
                    // Extract the filenames recommended by the AI
                    $paths = collect($response->json())->pluck('filename');
                    
                    if ($paths->isNotEmpty()) {
                        // 6. Fetch actual Product models from your database
                        // Only include items that are approved and active
                        $recommendedProducts = Product::whereIn('image', $paths)
                            ->whereIn('status', ['approved', 'reapproved'])
                            ->where('is_active', 1)
                            ->take(10)
                            ->get();
                    }
                } else {
                    Log::error("AI Server responded with error: " . $response->status());
                }
            }
        } catch (\Exception $e) {
            // Silently fail if AI is down so the homepage still loads for users
            Log::error("AI Recommendation Error: " . $e->getMessage());
        }

        // Return the view with all necessary data
        return view('frontend.home', compact(
            'categories',
            'products',
            'banner',
            'bestSellers',
            'recommendedProducts'
        ));
    }

    protected $exchangeRate = 0.0032;

    public function productsByCategory(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->where('status', 1)->firstOrFail();
        $currency = session('currency', 'LKR');

        if ($category->subcategories->count() > 0) {
            $sidebarItems = $category->subcategories;
            $sidebarType  = 'category';
            $sidebarTitle = $category->name . ' Categories';
        } else {
            $sidebarItems = Product::where('category_id', $category->id)
                ->whereIn('status', ['approved', 'reapproved'])
                ->where('is_active', 1)
                ->orderBy('name', 'asc')
                ->get();
            $sidebarType  = 'product';
            $sidebarTitle = $category->name . ' Items';
        }

        $query = Product::where('category_id', $category->id)
            ->whereIn('status', ['approved', 'reapproved'])
            ->where('is_active', 1);

        if ($request->sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($request->sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12);

        if ($currency === 'USD') {
            $products->getCollection()->transform(function ($product) {
                $product->price = $product->price * $this->exchangeRate;
                return $product;
            });

            if ($sidebarType === 'product') {
                $sidebarItems->transform(function ($product) {
                    $product->price = $product->price * $this->exchangeRate;
                    return $product;
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
            $productQuery = Product::select('products.*', DB::raw('SUM(order_items.qty) as total_sales'))
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', 5)
                ->where('products.is_active', 1)
                ->groupBy(
                    'products.id', 'products.name', 'products.slug', 'products.price',
                    'products.image', 'products.status', 'products.is_active',
                    'products.category_id', 'products.seller_id', 'products.description',
                    'products.stock', 'products.unit_type', 'products.approved_at',
                    'products.created_at', 'products.updated_at'
                )
                ->orderByDesc('total_sales');
        } elseif ($query === 'new arrivals') {
            $productQuery = Product::where('is_active', 1)->latest();
        } else {
            $productQuery = Product::where('is_active', 1)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                });
        }

        $products = $productQuery->whereIn('products.status', ['approved', 'reapproved'])
            ->where('products.is_active', 1) 
            ->paginate(12);

        if ($currency === 'USD') {
            $products->getCollection()->transform(function ($product) {
                $product->price = $product->price * $this->exchangeRate;
                return $product;
            });
        }

        return view('frontend.pages.search', compact('products', 'query'));
    }

    public function about() { return view('frontend.about'); }
    public function contact() { return view('frontend.contact'); }

    public function submitContact(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'message'    => 'required|string',
            'seller_id'  => 'nullable|integer|exists:staff,id',
        ]);

        $inquiry = ContactMessage::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'message'    => $request->message,
            'seller_id'  => $request->seller_id ?? null,
            'status'     => 'pending',
        ]);

        try {
            if (is_null($inquiry->seller_id)) {
                Staff::where('role', 'seller')->get()->each(function ($seller) use ($request, $inquiry) {
                    $seller->notify(new SellerDashboardNotification('inquiry', "New Inquiry from {$request->first_name}", $inquiry->id));
                });
            } else {
                $seller = Staff::find($inquiry->seller_id);
                if ($seller) {
                    $seller->notify(new SellerDashboardNotification('inquiry', "New Inquiry from {$request->first_name}", $inquiry->id));
                }
            }
        } catch (\Exception $e) {
            Log::error('Contact Notification Error: ' . $e->getMessage());
        }

        return back()->with('success', 'Message sent successfully!');
    }

    public function trackOrder(Request $request)
    {
        if (!$request->filled('tracking_no')) { return view('frontend.orders.track'); }
        $request->validate(['tracking_no' => 'required|string']);
        $order = Order::with(['items.product'])->where('tracking_no', $request->tracking_no)->first();
        if (!$order) { return back()->withInput()->with('status', 'No order found.'); }
        return view('frontend.orders.track', compact('order'));
    }
}