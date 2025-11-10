<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        return view('admin.categories.create');
    }

    // Store new category
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'status' => 'required|boolean',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
        }

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image' => $imagePath,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category added successfully!');
    }

    // Show edit form
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    // Update existing category
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'status' => 'required|boolean',
        ]);

        $category = Category::findOrFail($id);

        if ($request->hasFile('image')) {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $imagePath = $request->file('image')->store('categories', 'public');
        } else {
            $imagePath = $category->image;
        }

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image' => $imagePath,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully!');
    }

    // Delete a category
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully!');
    }
}
