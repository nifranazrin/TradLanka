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
            // 1. Send to Python
            $response = Http::attach(
                'image', file_get_contents($request->file('search_image')), 
                $request->file('search_image')->getClientOriginalName()
            )->post('http://127.0.0.1:5000/search');

            if ($response->failed()) return back()->with('error', 'AI Connection Failed');

            // 2. Get Paths
            $paths = collect($response->json())->pluck('filename')->toArray();

            if (empty($paths)) return view('search.results', ['products' => collect()]);

            // 3. Find IDs (Main Images + Gallery Images)
            $mainIds = Product::whereIn('image', $paths)->pluck('id');
            $galleryIds = DB::table('product_images')->whereIn('path', $paths)->pluck('product_id');
            $allIds = $mainIds->merge($galleryIds)->unique();

            // 4. Load Products (With correct status check)
            $products = Product::whereIn('id', $allIds)
                ->whereIn('status', ['approved', 'reapproved'])
                ->where('is_active', 1)
                ->get();

            return view('search.results', ['products' => $products]);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}