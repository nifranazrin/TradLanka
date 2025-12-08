<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SellerRequest;

// --- NEW IMPORTS ---
use App\Models\Staff; // To find the admins
use App\Notifications\NewSellerNotification; // To send the alert
// -------------------

class SellerRegistrationController extends Controller
{
    public function showForm()
    {
        return view('seller.register');
    }

    public function submitForm(Request $request)
    {
        // 1. Validation (Kept exactly the same)
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:seller_requests,email',
            'phone'          => 'required|string|max:15',
            'nic_number'     => 'required|string|max:20|unique:seller_requests,nic_number',
            'preferred_name' => 'required|string|max:255',
            'address'        => 'nullable|string|max:500',
            'nic_image'      => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // 2. File Upload
        $path = $request->file('nic_image')->store('seller_nics', 'public');

        // 3. Create the Request (Assigned to $newSeller variable so we can use it in notification)
        $newSeller = SellerRequest::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'nic_number'     => $request->nic_number,
            'preferred_name' => $request->preferred_name,
            'address'        => $request->address,
            'nic_image'      => $path,
            'status'         => 'pending',
        ]);

        // --- 4. SEND NOTIFICATION TO ADMINS ---
        try {
            // Find all Staff members who are Admins
            $admins = Staff::where('role', 'admin')->get(); 
            
            foreach ($admins as $admin) {
                // Send the notification passing the new seller data
                $admin->notify(new NewSellerNotification($newSeller));
            }
        } catch (\Throwable $e) {
            // Log error if needed, but don't stop the registration process
            // \Log::error('Seller Notification Error: ' . $e->getMessage());
        }
        // --------------------------------------

        return redirect()->back()->with('success', '✅ Your seller registration has been submitted for admin approval.');
    }
}