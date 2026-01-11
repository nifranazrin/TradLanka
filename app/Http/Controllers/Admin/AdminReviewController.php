<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        // ✅ ADD THIS LINE: This changes 'New' (1) to 'Seen' (2) 
        // This makes the red 16 in your sidebar disappear.
        Review::where('status', 1)->update(['status' => 2]);

        $query = Review::with(['product', 'user']);

        // Filter by rating if provided in the URL
        if ($request->has('rating') && $request->rating >= 1 && $request->rating <= 5) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->latest()->get();

        return view('admin.reviews.index', compact('reviews'));
    }
}