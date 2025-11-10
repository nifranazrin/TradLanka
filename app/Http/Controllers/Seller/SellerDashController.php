<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;

class SellerDashController extends Controller
{
    public function dashboard()
    {
        return view('seller.dashboard');
    }
}
