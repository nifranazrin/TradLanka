<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Staff;

class AdminProfileController extends Controller
{
     //Helper: Get currently logged-in admin ID
   
    protected function getAdminId()
    {
        // Try guard-based login
        $id = Auth::guard('admin')->id();

        // If no guard, fallback to session login (used in your setup)
        if (!$id && session()->has('staff_id')) {
            $id = session('staff_id');
        }

        return $id;
    }

     //Show the admin profile page
   
    public function index()
    {
        $adminId = $this->getAdminId();
        $admin = $adminId ? Staff::find($adminId) : null;

        if (!$admin) {
            return redirect()->route('staff.login')->with('error', 'Please log in first.');
        }

        return view('admin.profile.index', compact('admin'));
    }

   // Update admin profile
    
    public function update(Request $request)
    {
        $adminId = $this->getAdminId();
        $admin = $adminId ? Staff::find($adminId) : null;

        if (!$admin) {
            return redirect()->route('staff.login')->with('error', 'Please login first.');
        }

        //  Validation
        $rules = [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email,' . $admin->id, 
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        // If changing password, require confirmation and check old one
        if ($request->filled('new_password')) {
            $rules['current_password'] = 'required|string';
            $rules['new_password'] = 'required|min:6|confirmed';
        }

        $request->validate($rules);

        //  Update basic info
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->phone = $request->phone;

        //   Handle password update
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $admin->password)) {
                return back()->with('error', 'Current password is incorrect.');
            }

            $admin->password = Hash::make($request->new_password);
        }

        // Handle profile photo update
        if ($request->hasFile('image')) {
            if ($admin->image) {
                Storage::disk('public')->delete($admin->image);
            }

            $path = $request->file('image')->store('admin_profiles', 'public');
            $admin->image = $path;
        }

        //  Save changes
        $admin->save();

        // Update session name (for navbar)
        session(['staff_name' => $admin->name]);

        return back()->with('success', 'Profile updated successfully!');
    }
}
