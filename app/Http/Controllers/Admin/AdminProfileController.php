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
    protected function getAdminId() {
        return Auth::guard('admin')->id() ?? session('staff_id');
    }

    public function index() {
        $admin = Staff::find($this->getAdminId());
        if (!$admin) return redirect()->route('staff.login');
        return view('admin.profile.index', compact('admin'));
    }

    public function update(Request $request) {
        $admin = Staff::find($this->getAdminId());

        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:staff,email,' . $admin->id,
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $admin->password)) {
                return response()->json(['status' => 'error', 'message' => 'Current password incorrect.']);
            }
            $admin->password = Hash::make($request->new_password);
        }

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->phone = $request->phone;
        $admin->address = $request->address;

        if ($request->hasFile('image')) {
            if ($admin->image) Storage::disk('public')->delete($admin->image);
            $admin->image = $request->file('image')->store('admin_profiles', 'public');
        }

        $admin->save();
        return response()->json(['status' => 'success', 'message' => 'Profile updated successfully!']);
    }

    public function checkPassword(Request $request) {
        $admin = Staff::find($this->getAdminId());
        return response()->json(['valid' => Hash::check($request->password, $admin->password)]);
    }
}