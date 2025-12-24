<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    // Show the edit form
    public function edit()
    {
        // Fetch our specific banner by its unique name
        $banner = Banner::where('section_name', 'home_festive_offer')->firstOrFail();

        return view('admin.banner.edit', compact('banner'));
    }

    // Handle the update form submission
    public function update(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'button_text'  => 'required|string|max:50',
            'button_link'  => 'required|string|max:255',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        $banner = Banner::where('section_name', 'home_festive_offer')->firstOrFail();

        $data = $request->only([
            'title',
            'button_text',
            'button_link',
        ]);

        // Handle Image Upload
        if ($request->hasFile('image')) {

            // Delete old image if local
            if (
                $banner->image_path &&
                !str_starts_with($banner->image_path, 'http') &&
                Storage::disk('public')->exists($banner->image_path)
            ) {
                Storage::disk('public')->delete($banner->image_path);
            }

            // Store new image
            $imagePath = $request->file('image')->store('banners', 'public');
            $data['image_path'] = $imagePath;
        }

        $banner->update($data);

        return redirect()
            ->back()
            ->with('success', 'Banner updated successfully!');
    }
}
