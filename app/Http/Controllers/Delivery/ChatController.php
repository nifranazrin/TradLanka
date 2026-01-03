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
    public function index()
    {
        $riderId = Auth::guard('delivery')->id();

        // 1. Admin Support
        $admin = Staff::where('role', 'admin')->first();
        $excludeIds = [$riderId];
        if ($admin) {
            $excludeIds[] = $admin->id;
            $admin->unread_count = Message::where('sender_id', $admin->id)
                ->where('receiver_id', $riderId)->where('is_read', false)->count();
        }

        // 2. Active Chats
        $activeSellers = Staff::whereIn('role', ['seller', 'delivery'])
            ->whereNotIn('id', $excludeIds)
            ->where(function($q) use ($riderId) {
                $q->whereHas('messagesSent', function($sq) use ($riderId) { $sq->where('receiver_id', $riderId); })
                  ->orWhereHas('messagesReceived', function($sq) use ($riderId) { $sq->where('sender_id', $riderId); });
            })
            ->addSelect(['last_interaction' => Message::select('created_at')
                ->where(function($q) use ($riderId) {
                    $q->whereColumn('sender_id', 'staff.id')->where('receiver_id', $riderId);
                })->orWhere(function($q) use ($riderId) {
                    $q->whereColumn('receiver_id', 'staff.id')->where('sender_id', $riderId);
                })->latest()->take(1)
            ])
            ->orderByDesc('last_interaction')
            ->get();

        $activeIds = $activeSellers->pluck('id')->toArray();
        $allOtherStaff = Staff::whereNotIn('id', array_merge($excludeIds, $activeIds))->get();

        foreach($activeSellers as $contact) {
            $contact->unread_count = Message::where('sender_id', $contact->id)
                ->where('receiver_id', $riderId)->where('is_read', false)->count();
        }

        return view('delivery.chat.index', compact('activeSellers', 'allOtherStaff', 'admin'));
    }

    // This fixes the "Select Contact" header by returning user info
    public function getProfile($type, $id)
    {
        $user = Staff::findOrFail($id);
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'image' => $user->image,
            'email' => $user->email,
            'phone' => $user->phone
        ]);
    }

    public function fetchMessages($receiverId, $type)
    {
        $riderId = Auth::guard('delivery')->id();
        $messages = Message::where(function($q) use ($riderId, $receiverId) {
            $q->where('sender_id', $riderId)->where('receiver_id', $receiverId);
        })->orWhere(function($q) use ($riderId, $receiverId) {
            $q->where('sender_id', $receiverId)->where('receiver_id', $riderId);
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $riderId = Auth::guard('delivery')->id();
        
        $message = new Message();
        $message->sender_id = $riderId;
        $message->sender_type = 'delivery';
        $message->receiver_id = $request->receiver_id;
        $message->receiver_type = $request->receiver_type;
        $message->message = $request->message;

        if ($request->hasFile('attachments')) {
            $paths = [];
            foreach ($request->file('attachments') as $file) {
                $paths[] = $file->store('chat_attachments', 'public');
            }
            $message->attachment = json_encode($paths);
        }

        $message->save();
        return response()->json(['status' => 'success']);
    }

    // Maps to your 'chat.orders' route
    public function getRecentOrders()
    {
        return response()->json(Order::latest()->take(10)->get());
    }

    public function markAsRead($id, $type)
    {
        Message::where('sender_id', $id)
            ->where('receiver_id', Auth::guard('delivery')->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return response()->json(['status' => 'success']);
    }

    public function deleteMessage(Request $request)
    {
        $msg = Message::findOrFail($request->id);
        if($request->type == 'everyone') {
            $msg->delete();
        } else {
            // Logic for "Delete for me" (e.g., update a hidden_for_rider column)
            $msg->delete(); 
        }
        return response()->json(['status' => 'success']);
    }
}