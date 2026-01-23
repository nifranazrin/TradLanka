<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Staff; 
use App\Notifications\SellerDashboardNotification;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class AdminProductController extends Controller
{
    // Display all products with their sellers.
    public function index()
    {
       
        $products = Product::with(['seller', 'images'])
            ->orderByRaw("CASE 
                WHEN status IN ('pending', 'reapproval_pending') THEN 1 
                ELSE 0 
            END DESC")
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.products.index', compact('products'));
    }

    // Show single product details.
    public function show($id)
    {
        $product = Product::with(['seller', 'images'])->findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    // Approve or Re-Approve a product.
    public function approve($id)
    {
        $product = Product::findOrFail($id);

        if ($product->status === 'reapproval_pending') {
            $product->status = 'reapproved';
            $message = '✅ Product re-approved successfully!';
            $notifText = "Your product '{$product->name}' has been re-approved.";
        } else {
            $product->status = 'approved';
            $message = '✅ Product approved successfully!';
            $notifText = "Your product '{$product->name}' has been approved!";
        }

        $product->is_active = 1;
        $product->approved_at = now();
        $product->save();

        try {
            $seller = Staff::find($product->seller_id);
            if ($seller) {
               
                $seller->notify(new SellerDashboardNotification('product', $notifText, $product->id));
            }
        } catch (\Exception $e) {
           
            Log::error('Notification Error: ' . $e->getMessage());
        }

        try {
            $this->updateAISystem();
        } catch (\Exception $e) {
            Log::error("Admin AI Update Failed: " . $e->getMessage());
        }

        return back()->with('success', $message);
    }

    // Reject a product.
    public function reject($id)
    {
        $product = Product::findOrFail($id);
        $product->status = 'rejected';
        $product->is_active = 0;
        $product->save();

        try {
            $seller = Staff::find($product->seller_id);
            if ($seller) {
                $seller->notify(new SellerDashboardNotification('product', "Your product '{$product->name}' was rejected.", $product->id));
            }
        } catch (\Exception $e) {
            Log::error('Notification Error: ' . $e->getMessage());
        }

        return back()->with('error', '❌ Product rejected successfully.');
    }

  
    private function updateAISystem()
    {
        // 1. REGENERATE products.json
        $products = Product::whereIn('status', ['approved', 'reapproved'])
            ->where('is_active', 1)
            ->get()
            ->map(function($p) {
                $cat = $p->category->name ?? '';
                $boostedCategory = str_repeat($cat . " ", 10); 
                return [
                    'id' => $p->id,
                    'text' => $p->name . " " . $p->description . " " . $boostedCategory,
                ];
            });

        $jsonPath = base_path('ai_service/products.json');
        File::put($jsonPath, $products->toJson());
        Log::info("Admin AI: products.json regenerated.");

       try {
            
            $response = Http::timeout(2)->post('http://127.0.0.1:5000/retrain');
            
            if ($response->successful()) {
                Log::info("AI Retrain Signal Sent: " . $response->json()['message']);
            } else {
                Log::error("AI Retrain Signal Failed: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::warning("Could not reach AI Server. Is 'python api_server.py' running?");
        }

        // 2. RELOAD SERVER
        try {
            $response = Http::post('http://127.0.0.1:5000/reload');
            if ($response->successful()) {
                Log::info("AI Server Reloaded.");
            }
        } catch (\Exception $e) {
            Log::warning("Could not reload AI Server (Is it running?).");
        }
    }
}