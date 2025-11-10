<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SellerRequest;
use App\Models\Product;

class SellerApprovalController extends Controller
{
    //  Show all sellers directly from seller_requests
    public function index()
    {
        $requests = SellerRequest::orderBy('id', 'desc')->get();
        return view('admin.seller_requests.index', compact('requests'));
    }

    //  Approve seller + SweetAlert popup info
    public function approve($id)
    {
        $seller = SellerRequest::findOrFail($id);

        //  Update status to approved
        $seller->update(['status' => 'approved']);

        //  Generate company email (clean name)
        $cleanName = strtolower(preg_replace('/\s+/', '', $seller->name));
        $companyEmail = "{$cleanName}.tradlanka@gmail.com";

        //  Generate password (name + last 4 digits of NIC)
        $nicDigits = substr($seller->nic_number, -4);
        $plainPassword = strtolower($seller->name) . $nicDigits;

        //  Flash data for SweetAlert popup
        session()->flash('seller_approved_data', [
            'original_email' => $seller->email,
            'company_email' => $companyEmail,
            'password' => $plainPassword,
        ]);

        return redirect()->back()->with('success', '✅ Seller approved successfully!');
    }

    //  Reject seller
    public function reject($id)
    {
        $seller = SellerRequest::findOrFail($id);
        $seller->update(['status' => 'rejected']);

        return redirect()->back()->with('error', '❌ Seller request rejected.');
    }

    //  Toggle between Active / Inactive (inside seller_requests)
    public function toggleStatus($id)
    {
        $seller = SellerRequest::findOrFail($id);

        if ($seller->status === 'approved') {
            // Deactivate seller
            $seller->status = 'inactive';
            $seller->save();

            // Optional: Transfer their products to Admin (id = 1)
            Product::where('seller_id', $id)->update(['seller_id' => 1]);

            return redirect()->back()->with('success', '🚫 Seller deactivated successfully.');
        } elseif ($seller->status === 'inactive') {
            // Reactivate seller
            $seller->status = 'approved';
            $seller->save();

            return redirect()->back()->with('success', '✅ Seller reactivated successfully.');
        } else {
            return redirect()->back()->with('error', 'This seller cannot be toggled.');
        }
    }
}
