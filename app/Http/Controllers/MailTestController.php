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
        // 1. Find the latest order to use as a template
        // Corrected line for MailTestController.php
$order = Order::with(['items.product', 'items.variant'])->latest()->first();

        if (!$order) {
            return "No order found in the database. Please place a test order first.";
        }

        try {
            // 2. Send the email to your personal/support address
            Mail::to('infotradlanka@gmail.com')->send(new OrderConfirmation($order));
            
            return "Test email sent successfully to infotradlanka@gmail.com!";
        } catch (\Exception $e) {
            // Catching the error here tells you if your SMTP settings in .env are wrong
            return "Mail Error: " . $e->getMessage();
        }
    }
}