<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Message;
use App\Models\Staff;
use App\Notifications\SellerDashboardNotification; // ✅ REQUIRED

class ChatController extends Controller
{
    /**
     * 1. Show Admin Chat Dashboard
     */
    public function index()
    {
        // Get all sellers
        $sellers = Staff::where('role', 'seller')->get();

        return view('admin.chat.index', compact('sellers'));
    }

    /**
     * 2. Fetch Messages (AJAX)
     */
    public function fetchMessages($sellerId)
    {
        $adminId = Auth::guard('admin')->id();

        $messages = Message::where(function ($q) use ($adminId, $sellerId) {
                $q->where('sender_id', $adminId)
                  ->where('sender_type', 'admin')
                  ->where('receiver_id', $sellerId)
                  ->where('receiver_type', 'seller');
            })
            ->orWhere(function ($q) use ($adminId, $sellerId) {
                $q->where('sender_id', $sellerId)
                  ->where('sender_type', 'seller')
                  ->where('receiver_id', $adminId)
                  ->where('receiver_type', 'admin');
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * 3. Send Message (ADMIN → SELLER)
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|integer',
            'message'     => 'nullable|string',
            'attachment'  => 'nullable|image|max:2048'
        ]);

        $msg = new Message();
        $msg->sender_id     = Auth::guard('admin')->id();
        $msg->sender_type   = 'admin';
        $msg->receiver_id   = $request->receiver_id;
        $msg->receiver_type = 'seller';
        $msg->message       = $request->message;

        if ($request->hasFile('attachment')) {
            $msg->attachment = $request->file('attachment')
                ->store('chats', 'public');
        }

        $msg->save();

        // ✅ CREATE NOTIFICATION FOR SELLER
        try {
            $seller = Staff::find($request->receiver_id);

            if ($seller) {
                $adminName = Auth::guard('admin')->user()->name ?? 'Admin';

                $seller->notify(
                    new SellerDashboardNotification(
                        'chat', // ✅ IMPORTANT (must match sidebar logic)
                        "New message from Admin ({$adminName})",
                        $msg->id,
                        'seller.chat.index'
                    )
                );
            }
        } catch (\Exception $e) {
            Log::error('Admin Chat Notification Failed: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success']);
    }
}
