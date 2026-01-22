<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    
    public function handle(Request $request)
    {
        $intent = $request->input('queryResult.intent.displayName');
        $params = $request->input('queryResult.parameters', []);

        
        Log::info('Dialogflow Params:', $params);

        switch ($intent) {

            case 'Track.Order':
                return $this->trackOrder($params);

            case 'Product.Details':
                return $this->productDetails($params);

            default:
                return response()->json([
                    "fulfillmentText" =>
                        "Sorry, I can help with order tracking, product details, or contacting support."
                ]);
        }
    }

   
    private function trackOrder(array $params)
    {
       
        $trackingNo =
            $params['tracking_no']
            ?? $params['trackingNumber']
            ?? $params['tracking-number']
            ?? null;

        if (!$trackingNo) {
            return response()->json([
                "fulfillmentText" =>
                    "Please enter a valid tracking number (Example: TRAD-XXXX)."
            ]);
        }

        $trackingNo = trim($trackingNo);

        $order = Order::where('tracking_no', $trackingNo)->first();

        if (!$order) {
            return response()->json([
                "fulfillmentText" =>
                    "❌ Order not found. Please check your tracking number."
            ]);
        }

        // Order status mapping
        $statusMap = [
            0 => 'Order Placed',
            1 => 'Confirmed',
            2 => 'Processing',
            3 => 'At Head Office',
            4 => 'With Rider',
            5 => 'Delivered',
            6 => 'Cancelled & Refunded',
            7 => 'Cancellation Requested (Seller)',
            8 => 'Seller Approved Refund',
            9 => 'Delivery Cancelled by Admin',
        ];

        $statusText = $statusMap[$order->status] ?? 'Unknown Status';

       
        $currencySymbol = match ($order->currency) {
            'USD' => '$',
            'LKR' => 'Rs.',
            default => $order->currency . ' ',
        };

        return response()->json([
            "fulfillmentText" =>
                "📦 Order: {$order->tracking_no}\n" .
                "🚚 Status: {$statusText}\n" .
                "📍 City: {$order->city}\n" .
                "💰 Amount: {$currencySymbol}" . number_format($order->total_price, 2)
        ]);
    }

  
    private function productDetails(array $params)
{
    $productName = $params['product_name'] ?? null;

    if (!$productName) {
        return response()->json([
            "fulfillmentText" => "Please enter the product name you want details about."
        ]);
    }

    $productName = trim($productName);

    $product = Product::with('category')
        ->where('name', 'LIKE', '%' . $productName . '%')
        ->first();

    if (!$product) {
        return response()->json([
            "fulfillmentText" => "❌ Product not found. Please try another product name."
        ]);
    }

    // Formatting prices
    $localPrice = number_format($product->price, 2);
    $usdPrice   = number_format($product->price / 312.50, 2);

    // Using Dialogflow Messenger Rich Content for a cleaner UI
    return response()->json([
        "fulfillmentMessages" => [
            [
                "payload" => [
                    "richContent" => [[
                        [
                            "type" => "info",
                            "title" => $product->name,
                            "subtitle" => "🇱🇰 Rs. {$localPrice} | 🌍 \${$usdPrice}\nStock: {$product->stock} | Category: {$product->category->name}",
                            "image" => [
                                "src" => [
                                    // Ensure your product model has an 'image' attribute
                                    "rawUri" => asset('storage/' . $product->image) 
                                ]
                            ],
                            "actionLink" => url('/product/' . $product->slug)
                        ],
                        [
                            "type" => "description",
                            "title" => "About this Product",
                            "text" => [
                                "📝 Description: {$product->description}",
                            ]
                        ],
                        [
                            "type" => "chips",
                            "options" => [
                                ["text" => "Track an Order"],
                                ["text" => "Search another product"],
                                ["text" => "Contact Support"]
                            ]
                        ]
                    ]]
                ]
            ]
        ]
    ]);
}
}
