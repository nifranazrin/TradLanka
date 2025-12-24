<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Message;
use App\Models\Staff;
use App\Models\Order;
use App\Notifications\SellerDashboardNotification;

class ChatController extends Controller
{
    /**
     * 1. Show Chat Dashboard
     */
    public function index()
    {
        // Fetches admin and riders for the sidebar
        $admin  = Staff::where('role', 'admin')->first();
        $riders = Staff::where('role', 'delivery')->get();

        return view('seller.chat.index', compact('admin', 'riders'));
    }

    /**
     * 2. Fetch Messages (AJAX)
     */
    public function fetchMessages($receiverId, $type)
    {
        $sellerId = Auth::guard('seller')->id();

        // Retrieves messages excluding those deleted by the seller
        $messages = Message::where(function ($q) use ($sellerId, $receiverId, $type) {
                $q->where('sender_id', $sellerId)
                  ->where('sender_type', 'seller')
                  ->where('receiver_id', $receiverId)
                  ->where('receiver_type', $type)
                  ->where('deleted_by_sender', false);
            })
            ->orWhere(function ($q) use ($sellerId, $receiverId, $type) {
                $q->where('sender_id', $receiverId)
                  ->where('sender_type', $type)
                  ->where('receiver_id', $sellerId)
                  ->where('receiver_type', 'seller')
                  ->where('deleted_by_receiver', false);
            })
            ->with('replyTo')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * 3. Send Message (Handles Text, Files, and Order Cards)
     */
  public function sendMessage(Request $request)
{
    $request->validate([
        'receiver_id'   => 'required',
        'receiver_type' => 'required|in:admin,delivery',
        'message'       => 'nullable|string',
        'attachments.*' => 'nullable|image|max:2048', // Matches JS key
    ]);

    $msg = new Message();
    $msg->sender_id     = Auth::guard('seller')->id();
    $msg->sender_type   = 'seller';
    $msg->receiver_id   = $request->receiver_id;
    $msg->receiver_type = $request->receiver_type;
    $msg->message       = $request->message;

    // Handles multi-file attachments
    if ($request->hasFile('attachments')) {
        $paths = [];
        foreach ($request->file('attachments') as $file) {
            $paths[] = $file->store('chats', 'public');
        }
        // Store as JSON if multiple, or just the first string
        $msg->attachment = count($paths) === 1 ? $paths[0] : json_encode($paths);
    }

    $msg->save();
    return response()->json(['status' => 'success', 'message' => $msg]);
}

    /**
     * 4. Delete Message (Supports "For Me" and "Everyone")
     */
 public function deleteMessage(Request $request)
{
    $request->validate([
        'id'   => 'required|integer',
        'type' => 'required|in:me,everyone'
    ]);

    $msg = Message::findOrFail($request->id);
    $sellerId = Auth::guard('seller')->id();

    // Identify if the current seller is the sender or receiver of this specific message
    $isSender   = ($msg->sender_id == $sellerId && $msg->sender_type == 'seller');
    $isReceiver = ($msg->receiver_id == $sellerId && $msg->receiver_type == 'seller');

    if ($request->type === 'everyone') {
        // Delete for everyone: Only the original sender can trigger this
        if ($isSender) {
            $msg->is_deleted_everyone = true;
            $msg->message = "This message was deleted";
            $msg->attachment = null;
            $msg->save();
        }
    } else {
        // Delete for me: Hide the message ONLY for the person who clicked delete
        if ($isSender) {
            $msg->deleted_by_sender = true;
        } elseif ($isReceiver) {
            $msg->deleted_by_receiver = true;
        }
        $msg->save();
    }

    return response()->json(['status' => 'success']);
}
    /**
     * 5. Fetch Recent Orders (Fixed for fname, lname, address1)
     */
    public function getRecentOrders()
    {
        $sellerId = Auth::guard('seller')->id();

        // Fetches last 10 orders where seller has products
        $orders = Order::whereHas('items.product', fn ($q) =>
                $q->where('seller_id', $sellerId)
            )
            ->select('id', 'tracking_no', 'fname', 'lname', 'address1', 'phone') // Use exact DB columns
            ->latest()
            ->take(10)
            ->get();

        return response()->json($orders);
    }

    /**
     * 6. Get Profile Info
     */
    public function getStaffProfile($type, $id)
    {
        $staff = Staff::where('id', $id)
                      ->where('role', $type)
                      ->select('id','name','email','phone','image','role')
                      ->firstOrFail();

        return response()->json($staff);
    }
}