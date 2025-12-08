<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\SellerRequest;
use App\Models\Product;
use App\Models\Staff;

class SellerApprovalController extends Controller
{
    // Show all sellers directly from seller_requests
    public function index()
    {
        $requests = SellerRequest::orderBy('id', 'desc')->get();
        return view('admin.seller_requests.index', compact('requests'));
    }

    // Approve seller: update seller_requests, create/update Staff, flash credentials
    public function approve($id)
    {
        $seller = SellerRequest::findOrFail($id);

        // email + password
        $cleanName = strtolower(preg_replace('/\s+/', '', $seller->preferred_name ?: $seller->name));
        if (empty($cleanName)) {
            $cleanName = 'seller' . $seller->id;
        }

        $companyEmail = "{$cleanName}.tradlanka@gmail.com";

        // last 4 digits of NIC 
        $nicDigits = substr((string) ($seller->nic_number ?? ''), -4);
        if ($nicDigits === '') {
            $nicDigits = substr(md5(uniqid((string)$seller->id, true)), 0, 4);
        }

        // plain password: sanitized name + last4 nic
        $plainPassword = $cleanName . $nicDigits;

        DB::beginTransaction();
        try {
            //  Mark the seller_request as approved
            $seller->status = 'approved';
            $seller->save();

            //  Create or update staff account (company email is used as staff->email)
            $staff = Staff::where('email', $companyEmail)->first();

           $staffData = [
              'name'     => $seller->name,
              'email'    => $companyEmail,
              'phone'    => $seller->phone,
               'address'  => $seller->address,   
               'role'     => 'seller',
              'password' => Hash::make($plainPassword),
               'status'   => 'active',
              ];

            if ($staff) {
                // update existing staff
                $staff->fill($staffData);
                $staff->save();
            } else {
                // create new staff
                $staff = Staff::create($staffData);
            }

            // 3) Optional: send email to original (best effort, don't abort on mail fail)
            try {
                Mail::raw(
                    "Your seller account has been approved.\n\nCompany Email: {$companyEmail}\nPassword: {$plainPassword}\n\nLogin at: " . route('staff.login'),
                    function ($m) use ($seller) {
                        $m->to($seller->email)->subject('Your Seller Account Approved');
                    }
                );
            } catch (\Exception $mailEx) {
                Log::warning('Seller approval email failed: ' . $mailEx->getMessage(), ['seller_id' => $seller->id]);
            }

            DB::commit();

            // Flash credentials for SweetAlert in blade (plain password shown only in popup)
            session()->flash('seller_approved_data', [
                'original_email' => $seller->email,
                'company_email'  => $companyEmail,
                'password'       => $plainPassword,
            ]);

            return redirect()->back()->with('success', '✅ Seller approved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Seller approval error: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->back()->with('error', 'Something went wrong while approving the seller.');
        }
    }

    // Reject seller
    public function reject($id)
    {
        $seller = SellerRequest::findOrFail($id);
        $seller->status = 'rejected';
        $seller->save();

        return redirect()->back()->with('error', '❌ Seller request rejected.');
    }

    // Toggle between Approved <-> Inactive (inside seller_requests) and handle product transfer by staff id
    public function toggleStatus($id)
    {
        $seller = SellerRequest::findOrFail($id);

        // Determine company email (same logic as approve) to find staff row
        $cleanName = strtolower(preg_replace('/\s+/', '', $seller->preferred_name ?: $seller->name));
        if (empty($cleanName)) {
            $cleanName = 'seller' . $seller->id;
        }
        $companyEmail = "{$cleanName}.tradlanka@gmail.com";

        // Find the staff account (may not exist if approve didn't create it)
        $staff = Staff::where('email', $companyEmail)->first();

        if ($seller->status === 'approved') {
            // Deactivate request
            $seller->status = 'inactive';
            $seller->save();

            // Transfer their products to Admin (if staff found)
            if ($staff) {
                Product::where('seller_id', $staff->id)->update(['seller_id' => 1]);
            } else {
                // If products were stored using seller_request id (unlikely), try that as fallback:
                Product::where('seller_id', $seller->id)->update(['seller_id' => 1]);
            }

            // Also mark the staff inactive if exists
            if ($staff) {
                $staff->status = 'inactive';
                $staff->save();
            }

            return redirect()->back()->with('success', '🚫 Seller deactivated successfully.');
        } elseif ($seller->status === 'inactive') {
            // Reactivate
            $seller->status = 'approved';
            $seller->save();

            // Reactivate staff if exists
            if ($staff) {
                $staff->status = 'active';
                $staff->save();
            }

            return redirect()->back()->with('success', '✅ Seller reactivated successfully.');
        } else {
            return redirect()->back()->with('error', 'This seller cannot be toggled.');
        }
    }

    // --- NEW METHOD: Restore Rejected Seller ---
    public function restore($id)
    {
        $seller = SellerRequest::findOrFail($id);

        if ($seller->status === 'rejected') {
            $seller->status = 'pending';
            $seller->save();
            return redirect()->back()->with('success', '🔄 Seller request restored to Pending. You can re-evaluate it now.');
        }

        return redirect()->back()->with('error', 'Only rejected requests can be restored.');
    }
}

