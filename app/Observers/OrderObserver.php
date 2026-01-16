<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     * Fires immediately after a new order is saved to the database.
     */
    public function created(Order $order): void
    {
        $this->geocodeOrder($order);
    }

    /**
     * Handle the Order "updated" event.
     * Fires whenever an existing order is edited.
     */
    public function updated(Order $order): void
    {
        // ✅ WATCH COUNTRY: Now re-geocodes if the new country field changes
        if ($order->wasChanged(['city', 'state', 'zipcode', 'country'])) {
            $this->geocodeOrder($order);
        }
    }

    /**
     * Private helper to call OpenCage and save coordinates.
     */
    private function geocodeOrder(Order $order): void
    {
        // Only proceed if we have a city to search for
        if ($order->city) {
            try {
                // ✅ DYNAMIC COUNTRY LOGIC
                // 1. Check dedicated 'country' column first.
                // 2. Fallback to 'address2' if 'country' is empty.
                // 3. Finally, default to 'Sri Lanka' if nothing else is found.
                $country = $order->country ?? ($order->address2 ?? 'Sri Lanka');

                // Build the search query
                $query = "{$order->city}, {$order->state}, {$order->zipcode}, {$country}";

                // Use the registered config key
                $apiKey = config('services.opencage.key');

                $response = Http::get("https://api.opencagedata.com/geocode/v1/json", [
                    'q' => $query,
                    'key' => $apiKey,
                    'limit' => 1, 
                    'no_annotations' => 1 
                ]);

                if ($response->successful() && !empty($response->json()['results'])) {
                    $coords = $response->json()['results'][0]['geometry'];

                    // Save values without re-triggering the observer
                    $order->latitude = $coords['lat'];
                    $order->longitude = $coords['lng'];
                    $order->saveQuietly();
                    
                    Log::info("Geocoding success for Order #{$order->id}: {$query}");
                } else {
                    Log::warning("Geocoding returned no results for Order #{$order->id}: {$query}");
                }
            } catch (\Exception $e) {
                // Prevents the application from crashing if the API is down
                Log::error("Geocoding failed for Order #{$order->id}: " . $e->getMessage());
            }
        }
    }
}