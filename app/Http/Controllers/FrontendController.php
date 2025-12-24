<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ContactMessage;
use App\Models\Banner;
use App\Models\Order;
use App\Models\Staff;
use App\Notifications\SellerDashboardNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FrontendController extends Controller
{
    /**
     * 1. Home Page
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
            ->take(10)
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
            ->take(10)
            ->get();

        if ($bestSellers->isEmpty()) {
            $bestSellers = Product::where('is_active', 1)
                ->whereIn('status', ['approved', 'reapproved'])
                ->inRandomOrder()
                ->take(10)
                ->get();
        }

        $banner = Banner::where('section_name', 'home_festive_offer')->first();

        return view('frontend.home', compact(
            'categories',
            'products',
            'banner',
            'bestSellers'
        ));
    }

   /**
     * 2. Category Page
     */
    protected $exchangeRate = 0.0032;

    public function productsByCategory(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->where('status', 1)->firstOrFail();
        $currency = session('currency', 'LKR');

        // --- 1. DEFINE SIDEBAR VARIABLES FIRST (Fixes the Undefined Variable Error) ---
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

        // --- 2. QUERY MAIN PRODUCTS ---
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

        // --- 3. APPLY CONVERSION TO MAIN LIST AND SIDEBAR ---
        if ($currency === 'USD') {
            // Convert main products
            $products->getCollection()->transform(function ($product) {
                $product->price = $product->price * $this->exchangeRate;
                return $product;
            });

            // Convert sidebar products (if they exist)
            if ($sidebarType === 'product') {
                $sidebarItems->transform(function ($product) {
                    $product->price = $product->price * $this->exchangeRate;
                    return $product;
                });
            }
        }

        return view('frontend.category.show', compact(
            'category', 
            'products', 
            'sidebarItems', 
            'sidebarType', 
            'sidebarTitle'
        ));
    }

    /**
     * 3. Search Page
     */
    public function searchPage(Request $request)
    {
        $query = $request->input('query');
        $currency = session('currency', 'LKR');

        $products = Product::whereIn('status', ['approved', 'reapproved'])
            ->where('is_active', 1)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->paginate(12);

        // --- APPLY CONVERSION ---
        if ($currency === 'USD') {
            $products->getCollection()->transform(function ($product) {
                $product->price = $product->price * $this->exchangeRate;
                return $product;
            });
        }

        return view('frontend.pages.search', compact('products', 'query'));
    }

    /**
     * 4. About Page
     */
    public function about()
    {
        return view('frontend.about');
    }

    /**
     * 5. Contact Page
     */
    public function contact()
    {
        return view('frontend.contact');
    }

    /**
     * 6. Submit Contact Form (FIXED & SAFE)
     */
    public function submitContact(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'message'    => 'required|string',
            'seller_id'  => 'nullable|integer|exists:staff,id',
        ]);

        // ✅ Correct: allow NULL for global inquiry
        $inquiry = ContactMessage::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'message'    => $request->message,
            'seller_id'  => $request->seller_id ?? null,
            'status'     => 'pending',
        ]);

        // 🔔 Notify sellers
        try {
            if (is_null($inquiry->seller_id)) {
                // Notify ALL sellers for global inquiry
                Staff::where('role', 'seller')->get()->each(function ($seller) use ($request, $inquiry) {
                    $seller->notify(new SellerDashboardNotification(
                        'inquiry',
                        "New Inquiry from {$request->first_name}",
                        $inquiry->id
                    ));
                });
            } else {
                // Notify specific seller
                $seller = Staff::find($inquiry->seller_id);
                if ($seller) {
                    $seller->notify(new SellerDashboardNotification(
                        'inquiry',
                        "New Inquiry from {$request->first_name}",
                        $inquiry->id
                    ));
                }
            }
        } catch (\Exception $e) {
            Log::error('Contact Notification Error: ' . $e->getMessage());
        }

        return back()->with(
            'success',
            'Message sent successfully! Our sellers will contact you soon.'
        );
    }

    /**
     * 7. Track Order
     */
    public function trackOrder(Request $request)
    {
        if (!$request->filled('tracking_no')) {
            return view('frontend.orders.track');
        }

        $request->validate([
            'tracking_no' => 'required|string',
        ]);

        $order = Order::with(['items.product'])
            ->where('tracking_no', $request->tracking_no)
            ->first();

        if (!$order) {
            return back()
                ->withInput()
                ->with('status', 'No order found with this tracking number.');
        }

        return view('frontend.orders.track', compact('order'));
    }
}
