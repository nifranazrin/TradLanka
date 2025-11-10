<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellerRequest;

class SellerRegistrationController extends Controller
{
    public function showForm()
    {
        return view('seller.register');
    }

    public function submitForm(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:seller_requests,email',
            'phone'          => 'required|string|max:15',
            'nic_number'     => 'required|string|max:20|unique:seller_requests,nic_number',
            'preferred_name' => 'required|string|max:255',
            'address'        => 'nullable|string|max:500',
            'nic_image'      => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $path = $request->file('nic_image')->store('seller_nics', 'public');

        SellerRequest::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'nic_number'     => $request->nic_number,
            'preferred_name' => $request->preferred_name,
            'address'        => $request->address,
            'nic_image'      => $path,
            'status'         => 'pending',
        ]);

        return redirect()->back()->with('success', '✅ Your seller registration has been submitted for admin approval.');
    }
}
