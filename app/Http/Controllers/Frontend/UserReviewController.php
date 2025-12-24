<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Added for safety
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;

class UserReviewController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 1. PRODUCTS TO REVIEW
        $toReview = Order::where('user_id', $userId)
            ->where('status', 5) 
            ->with('orderItems.product')
            ->get()
            ->flatMap(function ($order) {
                return $order->orderItems;
            })
            ->filter(function ($item) use ($userId) {
                return !Review::where('user_id', $userId)
                    ->where('product_id', $item->product_id)
                    ->exists();
            });

        $toReviewCount = $toReview->count();

        // 2. REVIEW HISTORY
        $history = Review::where('user_id', $userId)
            ->with('product')
            ->latest()
            ->get();

        return view('user.profile.reviews.index', compact('toReview', 'history', 'toReviewCount'));
    }

    public function create($product_id)
    {
        $product = Product::findOrFail($product_id);
        
        $hasPurchased = Order::where('user_id', Auth::id())
            ->where('status', 4)
            ->whereHas('orderItems', function($q) use ($product_id) {
                $q->where('product_id', $product_id);
            })->exists();

        if (!$hasPurchased) {
            return redirect()->route('user.reviews.index')->with('error', 'You can only review items delivered to you.');
        }

        return view('user.profile.reviews.create', compact('product'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'rating'       => 'required|integer|min:1|max:5',
            'comment'      => 'required|string|max:1000',
            'image'        => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'is_anonymous' => 'nullable',
        ]);

        $userId = Auth::id();

        // Security check
        $hasPurchased = Order::where('user_id', $userId)
            ->where('status', 4)
            ->whereHas('orderItems', function($q) use ($request) {
                $q->where('product_id', $request->product_id);
            })->exists();

        if (!$hasPurchased) {
            return back()->with('error', 'Security check failed. You must own this product to review it.');
        }

        // Duplicate check
        if (Review::where('user_id', $userId)->where('product_id', $request->product_id)->exists()) {
            return redirect()->route('user.reviews.index')->with('error', 'You have already reviewed this product.');
        }

        // Use a transaction to ensure database and file storage are in sync
        DB::beginTransaction();
        try {
            $review = new Review();
            $review->user_id = $userId;
            $review->product_id = $request->product_id;
            $review->rating = $request->rating;
            $review->comment = $request->comment;
            $review->is_anonymous = $request->has('is_anonymous') ? 1 : 0;
            $review->status = 1; 

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('reviews', 'public');
                $review->image = $path;
            }

            $review->save();
            DB::commit();

            return redirect()->route('user.reviews.index')->with('success', 'Your review has been posted successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }
}