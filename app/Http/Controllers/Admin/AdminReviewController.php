<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class AdminReviewController extends Controller
{
    /**
     * Display all reviews and mark unread ones as read
     */
    public function index(Request $request)
    {
        //  Mark all unread reviews as read so the badge clears
        Review::where('is_read', 0)->update(['is_read' => 1]);

        // Eager load product and user for the table
        $query = Review::with(['product', 'user']);

        // Filter by rating if requested
        if ($request->has('rating') && $request->rating >= 1 && $request->rating <= 5) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->latest()->get();

        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Manually mark a specific review as read (Status 1)
     */
    public function markAsRead($id)
    {
        $review = Review::findOrFail($id);
        
        // Update both status and is_read
        $review->update([
            'status'  => 1,
            'is_read' => 1
        ]); 

        return back()->with('success', 'Review acknowledged!');
    }
}