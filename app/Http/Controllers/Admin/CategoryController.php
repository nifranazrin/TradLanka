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
        $categories = Category::orderBy('id', 'desc')->get();
        return view('admin.categories.index', compact('categories'));
    }

    // Show create form
    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();
        return view('admin.categories.create', compact('categories'));
    }



    // Store new category
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Z]/'], 
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',       // Thumbnail
            'banner_image' => 'nullable|image|max:5120', // Banner (5MB)
            'parent_id' => 'nullable|exists:categories,id',
          ], [
            // Custom error message for the capital letter requirement
            'name.regex' => 'The category name must start with a capital letter.'
        ]);

        $name = trim($request->name);

        // 2. Custom Duplicate Check (Case-Insensitive)
        $exists = Category::whereRaw('LOWER(name) = ?', [Str::lower($name)])->exists();
        if ($exists) {
            return back()
                ->withInput()
                ->with('error', "Category '{$name}' already exists.");
        }

        // 3. Status Handling (Ensure it saves as '1' or '0')
        $status = $request->status == '1' ? '1' : '0';

        // 4. Handle Main Image Upload (Thumbnail)
        $imagePath = null;
        if ($request->hasFile('image')) {
            // Using store() automatically generates a unique hash ID for the filename
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        // 5. Handle Banner Image Upload
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
                'banner_image' => $bannerPath,
                'status' => $status,
                'parent_id' => $request->parent_id,
            ]);
        } catch (QueryException $e) {
            Log::error('Category store error: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', "Category '{$name}' could not be saved due to a database error.");
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category added successfully!');
    }

    // Show edit form
public function edit($id)
{
    $category = Category::findOrFail($id);
    
    // Change $parentCategories to $mainCategories to match your Blade file
    $mainCategories = Category::where('id', '!=', $id)->get(); 
    
    return view('admin.categories.edit', compact('category', 'mainCategories'));
}

    // Update existing category
    public function update(Request $request, $id)
    {
        // 1. Validation
        $request->validate([
            'name' => 'required|string|max:255', // Removed unique check here as you do it manually below
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'banner_image' => 'nullable|image|max:5120',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $name = trim($request->name);

        // 2. Custom Duplicate Check (Excluding current ID)
        $exists = Category::whereRaw('LOWER(name) = ?', [Str::lower($name)])
                    ->where('id', '!=', $id)
                    ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', "Category '{$name}' already exists.");
        }

        // 3. Prevent Category from being its own Parent
        if ($request->parent_id == $id) {
             return back()->withInput()->with('error', "A category cannot be its own parent.");
        }

        $category = Category::findOrFail($id);
        $status = $request->status == '1' ? '1' : '0';

        // 4. Handle Main Image Update
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            // Store new image
            $imagePath = $request->file('image')->store('categories', 'public');
        } else {
            // Keep old image
            $imagePath = $category->image;
        }

        // 5. Handle Banner Image Update
        if ($request->hasFile('banner_image')) {
            // Delete old banner if it exists
            if ($category->banner_image && Storage::disk('public')->exists($category->banner_image)) {
                Storage::disk('public')->delete($category->banner_image);
            }
            // Store new banner
            $bannerPath = $request->file('banner_image')->store('category_banners', 'public');
        } else {
            // Keep old banner
            $bannerPath = $category->banner_image;
        }

        try {
            $category->update([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $request->description,
                'image' => $imagePath,
                'banner_image' => $bannerPath,
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

        // 1. Delete Main Image
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        // 2. Delete Banner Image
        if ($category->banner_image && Storage::disk('public')->exists($category->banner_image)) {
            Storage::disk('public')->delete($category->banner_image);
        }

        // 3. Delete Record
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully!');
    }
}