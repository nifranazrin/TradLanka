<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;

class MailTestController extends Controller
{
    public function sendTestEmail()
    {
        
$order = Order::with(['items.product', 'items.variant'])->latest()->first();

        if (!$order) {
            return "No order found in the database. Please place a test order first.";
        }

        try {
           
            Mail::to('infotradlanka@gmail.com')->send(new OrderConfirmation($order));
            
            return "Test email sent successfully to infotradlanka@gmail.com!";
        } catch (\Exception $e) {
            
            return "Mail Error: " . $e->getMessage();
        }
    }
}