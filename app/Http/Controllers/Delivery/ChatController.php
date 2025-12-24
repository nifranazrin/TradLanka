<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\Staff; 
use App\Models\Order;

class ChatController extends Controller
{
    /**
     * 1. Display the chat dashboard for the delivery person.
     */
    public function index()
    {
        // Use 'delivery' guard to match your StaffLoginController
        $riderId = Auth::guard('delivery')->id();

        // Get Sellers linked to this rider's assigned orders
        $sellers = Staff::where('role', 'seller')
            ->whereHas('products.orderItems.order', function($q) use ($riderId) {
                // Uses the 'delivery_boy_id' column from your migration
                $q->where('orders.delivery_boy_id', $riderId);
            })->distinct()->get();

        // Get Admin Support
        $admin = Staff::where('role', 'admin')->first();

        return view('delivery.chat.index', compact('sellers', 'admin'));
    }

    /**
     * 2. Fetch message history (AJAX)
     */
    public function fetchMessages($receiverId, $type)
    {
        // Match the session guard
        $riderId = Auth::guard('delivery')->id();

        $messages = Message::where(function($q) use ($riderId, $receiverId, $type) {
                // Case A: Messages sent by Rider to Seller/Admin
                $q->where('sender_id', $riderId)
                  ->where('sender_type', 'delivery') 
                  ->where('receiver_id', $receiverId)
                  ->where('receiver_type', $type)
                  ->where('deleted_by_sender', false);
            })
            ->orWhere(function($q) use ($riderId, $receiverId, $type) {
                // Case B: Messages received by Rider from Seller/Admin
                $q->where('sender_id', $receiverId)
                  ->where('sender_type', $type)
                  ->where('receiver_id', $riderId)
                  ->where('receiver_type', 'delivery')
                  ->where('deleted_by_receiver', false);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * 3. Store and send a new message
     */
    public function sendMessage(Request $request)
    {
        $riderId = Auth::guard('delivery')->id();

        // Validation to ensure data integrity
        $request->validate([
            'receiver_id' => 'required',
            'receiver_type' => 'required|in:admin,seller',
            'message' => 'nullable|string',
            'attachments.*' => 'nullable|image|max:2048'
        ]);

        $message = new Message();
        $message->sender_id = $riderId;
        $message->sender_type = 'delivery'; // Must match Seller's 'receiver_type'
        $message->receiver_id = $request->receiver_id;
        $message->receiver_type = $request->receiver_type;
        $message->message = $request->message;

        // Handle attachments if your delivery chat supports them
        if ($request->hasFile('attachments')) {
            $paths = [];
            foreach ($request->file('attachments') as $file) {
                $paths[] = $file->store('chats', 'public');
            }
            $message->attachment = json_encode($paths);
        }

        $message->save();

        return response()->json(['status' => 'Message Sent!', 'message' => $message]);
    }
}