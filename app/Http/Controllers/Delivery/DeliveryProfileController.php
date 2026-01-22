<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Staff;

class DeliveryProfileController extends Controller
{
   
    protected function getRiderId()
    {
        $id = Auth::guard('delivery')->id();
        if (!$id && session()->has('staff_id')) {
            $id = session('staff_id');
        }
        return $id;
    }

   
    public function index()
    {
        $riderId = $this->getRiderId();
        $rider = $riderId ? Staff::find($riderId) : null;

        if (!$rider) {
            return redirect()->route('staff.login')->with('error', 'Please log in first.');
        }

        return view('delivery.profile.index', compact('rider'));
    }

    
    public function update(Request $request)
    {
        $riderId = $this->getRiderId();
        $rider = $riderId ? Staff::find($riderId) : null;

        if (!$rider) {
            return redirect()->route('staff.login')->with('error', 'Please login first.');
        }

        
        $rules = [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:staff,email,' . $rider->id,
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        if ($request->filled('new_password')) {
            $rules['current_password'] = 'required|string';
            $rules['new_password'] = 'required|min:6|confirmed';
        }

        $request->validate($rules);

        //  Update basic info
        $rider->name = $request->name;
        $rider->email = $request->email;
        $rider->phone = $request->phone;
        $rider->address = $request->address;

        //  Update password correctly
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $rider->password)) {
                return back()->with('error', 'Current password is incorrect.');
            }
            $rider->password = Hash::make($request->new_password);
        }

        //  Handle image upload
        if ($request->hasFile('image')) {
            if ($rider->profile_photo) {
                Storage::disk('public')->delete($rider->profile_photo);
            }
            $path = $request->file('image')->store('delivery_profiles', 'public');
            $rider->image = $path;  
        }

        $rider->save();

        // Force re-login exactly like seller
        Auth::guard('delivery')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('staff.login')
            ->with('success', 'Profile updated successfully! Please log in again using your new password.');
    }

    //  AJAX Password Check (New function for the JS logic)
    public function checkPassword(Request $request)
    {
        $rider = Staff::find($this->getRiderId());
        $isValid = $rider && Hash::check($request->password, $rider->password);
        return response()->json(['valid' => $isValid]);
    }
}