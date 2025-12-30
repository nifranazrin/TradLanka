<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserRequest; 
use App\Models\Staff; 
use App\Notifications\NewSellerNotification; 
use Illuminate\Support\Facades\Log;

class StaffRegistrationController extends Controller
{
    /**
     * Display the registration form.
     */
    public function showForm()
    {
        return view('seller.register');
    }

    /**
     * Handle the registration submission.
     */
    public function submitForm(Request $request)
{
    // 1. Prepare Phone Number (+94 Prefix Logic)
    $rawPhone = ltrim($request->phone, '0'); 
    $fullPhone = '+94' . $rawPhone;
    $request->merge(['phone' => $fullPhone]);

    // 2. Comprehensive Validation
    $request->validate([
        'name'           => 'required|string|max:255',
        'email'          => 'required|email|unique:user_requests,email|unique:staff,email',
        'phone'          => 'required|string|size:12|unique:user_requests,phone|unique:staff,phone',
        'nic_number'     => 'required|string|max:20|unique:user_requests,nic_number',
        'preferred_name' => 'required|string|max:255',
        'role'           => 'required|in:seller,delivery', 
        'address'        => 'nullable|string|max:500',
        'nic_image'      => 'required|image|mimes:jpg,jpeg,png|max:2048',
    ], [
        'email.unique'      => 'This email is already registered or pending approval.',
        'phone.unique'      => 'This phone number is already registered or assigned to a staff member.',
        'phone.size'        => 'Please enter exactly 9 digits after the +94 prefix.',
        'nic_number.unique' => 'This NIC number has already been used in a pending application.',
    ]);

    // 3. File Upload for NIC images
    $path = $request->file('nic_image')->store('seller_nics', 'public');

    // 4. Create the Request
    $newRequest = UserRequest::create([
        'name'           => $request->name,
        'email'          => $request->email,
        'phone'          => $fullPhone,
        'nic_number'     => $request->nic_number,
        'preferred_name' => $request->preferred_name,
        'role'           => $request->role, 
        'address'        => $request->address,
        'nic_image'      => $path,
        'status'         => 'pending',
    ]);

    // 5. Notification Logic
    try {
        $admins = Staff::where('role', 'admin')->get(); 
        foreach ($admins as $admin) {
            $admin->notify(new NewSellerNotification($newRequest));
        }
    } catch (\Throwable $e) {
        Log::error('Registration Notification Error: ' . $e->getMessage());
    }

    return redirect()->back()->with('success', 'Your application has been submitted successfully! Our admin team will review it and contact you via email.');
}
}