<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class StaffManagementController extends Controller
{
    /**
     * Display a listing of approved staff.
     */
    public function index()
    {
        // Get the current logged-in admin ID
        $adminId = Auth::id(); 

        // Get everyone except the currently logged-in admin to prevent self-lockout
        $staff = Staff::where('id', '!=', $adminId)
                      ->orderBy('id', 'desc')
                      ->get();
                      
        return view('admin.staff.index', compact('staff'));
    }

    /**
     * Toggle staff status between active and inactive.
     */
    public function toggleStatus($id)
    {
        // Find the staff member or fail with a 404
        $staff = Staff::findOrFail($id);
        
        // Logic to switch status
        $staff->status = ($staff->status === 'active') ? 'inactive' : 'active';
        $staff->save();

        $message = ($staff->status === 'active') ? '✅ Staff account activated.' : '🚫 Staff account inactivated.';
        
        return redirect()->back()->with('success', $message);
    }
}