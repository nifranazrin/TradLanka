<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ImageSearchController extends Controller
{
    public function index()
    {
        // Redirects to home or wherever you want, since the modal is on the header
        return redirect()->route('home'); 
    }

    public function search(Request $request)
{
    $request->validate([
        'search_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5048',
    ]);

    try {
        // 1. Send image to Flask API
        $response = Http::attach(
            'image', file_get_contents($request->file('search_image')), 
            $request->file('search_image')->getClientOriginalName()
        )->post('http://127.0.0.1:5000/search');

        if ($response->failed()) return back()->with('error', 'AI Connection Failed');

        // 2. Process results with Distance Threshold
        $aiResults = $response->json();
        $filteredPaths = [];

         // In ImageSearchController.php
            foreach ($aiResults as $result) {
                // Threshold of 1.05 ensures high-quality matches
                if ($result['distance'] <= 1.05) {
                    $filteredPaths[] = $result['filename'];
                }

        }

        if (empty($filteredPaths)) {
            return view('search.results', ['products' => collect()]);
        }

        // 3. Find Product IDs based on filtered paths
        $mainIds = Product::whereIn('image', $filteredPaths)->pluck('id');
        $galleryIds = DB::table('product_images')->whereIn('path', $filteredPaths)->pluck('product_id');
        $allIds = $mainIds->merge($galleryIds)->unique();

        // 4. Load only approved and active products
        $products = Product::whereIn('id', $allIds)
            ->whereIn('status', ['approved', 'reapproved'])
            ->where('is_active', 1)
            ->get();

        return view('search.results', ['products' => $products]);

    } catch (\Exception $e) {
        return back()->with('error', 'Search Error: ' . $e->getMessage());
    }
}
}