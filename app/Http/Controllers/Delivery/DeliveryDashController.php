<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;

class DeliveryDashController extends Controller
{
    public function dashboard()
    {
        return view('delivery.dashboard');
    }
}
