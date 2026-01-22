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
        
        return redirect()->route('home'); 
    }

    public function search(Request $request)
{
    $request->validate([
        'search_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5048',
    ]);

    try {
        // Send image to Flask API
        $response = Http::attach(
            'image', file_get_contents($request->file('search_image')), 
            $request->file('search_image')->getClientOriginalName()
        )->post('http://127.0.0.1:5000/search');

        if ($response->failed()) return back()->with('error', 'AI Connection Failed');

        // Process results with Distance Threshold
        $aiResults = $response->json();
        $filteredPaths = [];

         // In ImageSearchController.php
            foreach ($aiResults as $result) {
                
                if ($result['distance'] <= 1.05) {
                    $filteredPaths[] = $result['filename'];
                }

        }

        if (empty($filteredPaths)) {
            return view('search.results', ['products' => collect()]);
        }

        //  Find Product IDs based on filtered paths
        $mainIds = Product::whereIn('image', $filteredPaths)->pluck('id');
        $galleryIds = DB::table('product_images')->whereIn('path', $filteredPaths)->pluck('product_id');
        $allIds = $mainIds->merge($galleryIds)->unique();

        //  Load only approved and active products
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