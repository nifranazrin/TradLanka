<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\UserRequest; 
use App\Models\Product;
use App\Models\Staff;

class UserApprovalController extends Controller
{

    public function readNotification($id)
    {
        $admin = auth()->guard('admin')->user() ?? Staff::find(session('staff_id'));
        $notification = $admin->notifications->find($id);

        if ($notification) {
            $notification->markAsRead();
           
            return redirect($notification->data['link'] ?? route('admin.seller.requests'));
        }

        return back();
    }

    /**
     *  Mark all notifications as read
     */
    public function markAllRead()
    {
        $admin = auth()->guard('admin')->user() ?? Staff::find(session('staff_id'));
        
        if ($admin) {
            $admin->unreadNotifications->markAsRead();
            return back()->with('success', 'All notifications marked as read');
        }

        return back()->with('error', 'Could not find admin session.');
    }


    // Show all requests directly from user_requests table
    public function index()
{
    // Fetches both Sellers and Delivery person applications
    $requests = UserRequest::orderBy('id', 'desc')->get(); 
    
    return view('admin.user_requests.index', compact('requests')); 
}
    // Approve user: update user_requests, create/update Staff, generate custom credentials

     public function approve($id)
{
    $request = UserRequest::findOrFail($id);

    // Security check
    $existingStaff = Staff::where('phone', $request->phone)->first();
    if ($existingStaff) {
        return redirect()->back()->with('error', "❌ Error: Phone {$request->phone} is already registered.");
    }

    // NEW Logic for Credentials
    $prefix = ($request->role === 'delivery') ? 'd.' : 's.';
    
    // Use preferred name or standard name, remove spaces, make lowercase
    $cleanName = strtolower(preg_replace('/\s+/', '', $request->preferred_name ?: $request->name));
    
    // Get last 3 digits of NIC
    $nicDigits = substr((string) ($request->nic_number ?? ''), -3);
    
    
    $plainPassword = $cleanName . $nicDigits;
    
    $companyEmail = "{$prefix}{$cleanName}.tradlanka@gmail.com";

    $approvedData = [
        'original_email' => $request->email,
        'company_email'  => $companyEmail,
        'password'       => $plainPassword,
        'role'           => $request->role,
    ];

    DB::beginTransaction();
    try {
        Staff::create([
            'name'       => $request->name,
            'email'      => $companyEmail,
            'phone'      => $request->phone,
            'nic_number' => $request->nic_number,
            'address'    => $request->address,
            'role'       => $request->role ?? 'seller',
            'password'   => Hash::make($plainPassword), 
            'status'     => 'active',
            'id_image'   => $request->nic_image,
            'image'      => null,
        ]);

        session()->flash('seller_approved_data', $approvedData);
        $request->delete(); 
        DB::commit();

        return redirect()->route('admin.seller.requests')->with('success', '✅ Approved. Password is: ' . $plainPassword);
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}
    // Reject user request
    public function reject($id)
    {
        $request = UserRequest::findOrFail($id);
        $request->status = 'rejected';
        $request->save();

        return redirect()->back()->with('error', '❌ Request rejected.');
    }


public function sendCredentialsEmail(Request $request) 
{
    try {
        Mail::raw(
            "Welcome to TradLanka!\n\n" .
            "Your account has been created with the following credentials:\n" .
            "Role: " . strtoupper($request->role) . "\n" .
            "Company Email: " . $request->company_email . "\n" .
            "Password: " . $request->password . "\n\n" .
            "Login here: " . route('staff.login'),
            function ($m) use ($request) {
                $m->to($request->original_email)
                  ->subject('TradLanka - Your Official Account Credentials');
            }
        );
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
    // Restore rejected request to pending
    public function restore($id)
    {
        $request = UserRequest::findOrFail($id);
        if ($request->status === 'rejected') {
            $request->status = 'pending';
            $request->save();
            return redirect()->back()->with('success', '🔄 Request restored.');
        }
        return redirect()->back()->with('error', 'Only rejected requests can be restored.');
    }
}