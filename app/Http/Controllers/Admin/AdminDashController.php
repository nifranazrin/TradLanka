<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\categories;
use App\Models\Category;
use App\Models\Product;
use App\Models\SellerRequest;
use App\Models\Staff;

class AdminDashController extends Controller
{
    public function dashboard()
    {
        // Dashboard statistics
        $totalCategories = Category::count();
        $totalProducts = Product::count();
        $totalSellers = Staff::where('role', 'seller')->count();
        $pendingRequests = SellerRequest::where('status', 'pending')->count();

        return view('admin.dashboard', compact(
            'totalCategories',
            'totalProducts',
            'totalSellers',
            'pendingRequests'
        ));
    }
}
