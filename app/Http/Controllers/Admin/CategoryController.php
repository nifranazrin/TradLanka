<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    // Show all categories
    public function index()
    {
        // We need all categories to populate the "Parent Category" dropdown in the modal
        $categories = Category::orderBy('id', 'desc')->get();
        // Also handy to have just main categories separated if needed, but your view uses $categories
        return view('admin.categories.index', compact('categories'));
    }

    // Show create form
    public function create()
    {
        // Retrieve parents if you use a separate create page
        $categories = Category::whereNull('parent_id')->get();
        return view('admin.categories.create', compact('categories'));
    }

    // Store new category
    public function store(Request $request)
    {
        // Validate inputs
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'banner_image' => 'nullable|image|max:5120', // <--- ADDED: Banner Validation (Max 5MB)
            'parent_id' => 'nullable|exists:categories,id',
            // status is checked below via request
        ]);

        $name = trim($request->name);

        // Case-insensitive duplicate check
        $exists = Category::whereRaw('LOWER(name) = ?', [Str::lower($name)])->exists();
        if ($exists) {
            return back()
                ->withInput()
                ->with('error', "Category '{$name}' already exists.");
        }

        // Status Handling
        $status = $request->status; // Expecting 1 or 0 from the form

        // 1. Handle Main Image
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        // 2. Handle Banner Image (NEW)
        $bannerPath = null;
        if ($request->hasFile('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('category_banners', 'public');
        }

        try {
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $request->description,
                'image' => $imagePath,
                'banner_image' => $bannerPath, // <--- ADDED: Saving Banner
                'status' => $status,
                'parent_id' => $request->parent_id,
            ]);
        } catch (QueryException $e) {
            Log::error('Category store error: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', "Category '{$name}' could not be saved.");
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category added successfully!');
    }

    // Show edit form
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        // Get all categories except the current one (cannot be its own parent)
        $parentCategories = Category::where('id', '!=', $id)->get(); 
        
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    // Update existing category
    public function update(Request $request, $id)
    {
        // Validate inputs
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'banner_image' => 'nullable|image|max:5120', // <--- ADDED: Banner Validation
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $name = trim($request->name);

        // Case-insensitive duplicate check excluding current id
        $exists = Category::whereRaw('LOWER(name) = ?', [Str::lower($name)])
                    ->where('id', '!=', $id)
                    ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', "Category '{$name}' already exists.");
        }

        $status = $request->status;

        $category = Category::findOrFail($id);

        // Check if user tried to set the category as its own parent (loop prevention)
        if ($request->parent_id == $id) {
             return back()->withInput()->with('error', "A category cannot be its own parent.");
        }

        // 1. Handle Main Image Update
        if ($request->hasFile('image')) {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $imagePath = $request->file('image')->store('categories', 'public');
        } else {
            $imagePath = $category->image;
        }

        // 2. Handle Banner Image Update (NEW)
        if ($request->hasFile('banner_image')) {
            // Delete old banner if exists
            if ($category->banner_image && Storage::disk('public')->exists($category->banner_image)) {
                Storage::disk('public')->delete($category->banner_image);
            }
            // Store new banner
            $bannerPath = $request->file('banner_image')->store('category_banners', 'public');
        } else {
            // Keep existing banner
            $bannerPath = $category->banner_image;
        }

        try {
            $category->update([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $request->description,
                'image' => $imagePath,
                'banner_image' => $bannerPath, // <--- ADDED: Updating Banner
                'status' => $status,
                'parent_id' => $request->parent_id,
            ]);
        } catch (QueryException $e) {
            Log::error('Category update error: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', "Category '{$name}' could not be updated.");
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully!');
    }

    // Delete a category
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Delete Main Image
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        // Delete Banner Image (NEW)
        if ($category->banner_image && Storage::disk('public')->exists($category->banner_image)) {
            Storage::disk('public')->delete($category->banner_image);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully!');
    }
}