<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        // ✅ FIX: Mark all '0' (Unread) as '1' (Read/Visible)
        // This makes the red count in your sidebar disappear instantly.
        \App\Models\Review::where('status', 0)->update(['status' => 1]);

        // Eager load product and user for the table
        $query = Review::with(['product', 'user']);

        // Filter by rating if requested
        if ($request->has('rating') && $request->rating >= 1 && $request->rating <= 5) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->latest()->get();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function markAsRead($id)
    {
        // This method is now optional since the index() clears them all,
        // but you can keep it to manually handle individual reviews.
        $review = Review::findOrFail($id);
        $review->update(['status' => 1]); 

        return back()->with('success', 'Review acknowledged!');
    }
}