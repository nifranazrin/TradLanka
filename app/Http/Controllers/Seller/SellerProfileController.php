<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Staff;

class SellerProfileController extends Controller
{
    
    protected function getSellerId()
    {
        $id = Auth::guard('seller')->id();
        if (!$id && session()->has('staff_id')) {
            $id = session('staff_id');
        }
        return $id;
    }

    //  Show profile
    public function index()
    {
        $sellerId = $this->getSellerId();
        $seller = $sellerId ? Staff::find($sellerId) : null;

        if (!$seller) {
            return redirect()->route('staff.login')->with('error', 'Please log in first.');
        }

        return view('seller.profile.index', compact('seller'));
    }

    //  Update profile info (and optionally password)
    public function update(Request $request)
    {
        $sellerId = $this->getSellerId();
        $seller = $sellerId ? Staff::find($sellerId) : null;

        if (!$seller) {
            return redirect()->route('staff.login')->with('error', 'Please login first.');
        }

        //  Validation rules
        $rules = [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:staff,email,' . $seller->id,
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        // Password fields check
        if ($request->filled('new_password')) {
            $rules['current_password'] = 'required|string';
            $rules['new_password'] = 'required|min:6|confirmed';
        }

        $request->validate($rules);

        //  Update basic info
        $seller->name = $request->name;
        $seller->email = $request->email;
        $seller->phone = $request->phone;
        $seller->address = $request->address;

        //  Update password correctly
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $seller->password)) {
                return back()->with('error', 'Current password is incorrect.');
            }

            //  Always hash before saving
            $seller->password = Hash::make($request->new_password);
        }

        //  Handle image upload
        if ($request->hasFile('image')) {
            if ($seller->profile_photo) {
                Storage::disk('public')->delete($seller->profile_photo);
            }
            $path = $request->file('image')->store('seller_profiles', 'public');
            $seller->image = $path;  
        }



        $seller->save();

        //  Clear old session and force re-login
        Auth::guard('seller')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('staff.login')
            ->with('success', 'Profile updated successfully! Please log in again using your new password.');
    }
}
