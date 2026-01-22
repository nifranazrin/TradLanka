<?php

namespace App\Http\Controllers\Seller;

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
        $sellerId = Auth::guard('seller')->id();

        // Mark incoming messages as read for this seller immediately
        Message::where('receiver_id', $sellerId)
            ->where('receiver_type', 'seller')
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        // 1. Admin Support
        $admin = Staff::where('role', 'admin')->first();
        $excludeIds = [$sellerId];
        
        if ($admin) {
            $excludeIds[] = $admin->id;
            $admin->unread_count = Message::where('sender_id', $admin->id)
                ->where('receiver_id', $sellerId)
                ->where('is_read', false)
                ->count();
        }

        $activeSellers = Staff::whereIn('role', ['seller', 'delivery'])
            ->whereNotIn('id', $excludeIds)
            ->where(function($q) use ($sellerId) {
                $q->whereHas('messagesSent', function($sq) use ($sellerId) { 
                    $sq->where('receiver_id', $sellerId); 
                })
                ->orWhereHas('messagesReceived', function($sq) use ($sellerId) { 
                    $sq->where('sender_id', $sellerId); 
                });
            })
            ->addSelect(['last_interaction' => Message::select('created_at')
                ->where(function($q) use ($sellerId) {
                    $q->whereColumn('sender_id', 'staff.id')->where('receiver_id', $sellerId);
                })->orWhere(function($q) use ($sellerId) {
                    $q->whereColumn('receiver_id', 'staff.id')->where('sender_id', $sellerId);
                })->latest()->take(1)
            ])
            ->orderByDesc('last_interaction')
            ->get();

        $activeIds = $activeSellers->pluck('id')->toArray();
        
        // 3. All Other Staff (Start new chats via search)
        $allOtherStaff = Staff::whereNotIn('id', array_merge($excludeIds, $activeIds))
            ->whereIn('role', ['seller', 'delivery'])
            ->get();

        // 4. Calculate Unread Counts for Active Contacts
        foreach($activeSellers as $contact) {
            $contact->unread_count = Message::where('sender_id', $contact->id)
                ->where('receiver_id', $sellerId)
                ->where('is_read', false)
                ->count();
        }

        return view('seller.chat.index', compact('activeSellers', 'allOtherStaff', 'admin'));
    }

    // Identical to Delivery: Fixes the "Select Contact" header by returning user info
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
        $sellerId = Auth::guard('seller')->id();
        $messages = Message::where(function($q) use ($sellerId, $receiverId) {
            $q->where('sender_id', $sellerId)->where('receiver_id', $receiverId);
        })->orWhere(function($q) use ($sellerId, $receiverId) {
            $q->where('sender_id', $receiverId)->where('receiver_id', $sellerId);
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $sellerId = Auth::guard('seller')->id();
        
        $message = new Message();
        $message->sender_id = $sellerId;
        $message->sender_type = 'seller';
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

    // Maps to 'chat.orders' - Synchronized with Seller Privacy
    public function getRecentOrders()
    {
        $sellerId = Auth::guard('seller')->id();
        // Return only orders where this seller's products are present
        return response()->json(
            Order::whereHas('items.product', fn($q) => $q->where('seller_id', $sellerId))
                ->latest()
                ->take(10)
                ->get()
        );
    }

    public function markAsRead($id, $type)
    {
        Message::where('sender_id', $id)
            ->where('receiver_id', Auth::guard('seller')->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return response()->json(['status' => 'success']);
    }

    public function deleteMessage(Request $request)
    {
        $msg = Message::findOrFail($request->id);
        // Matching Delivery logic: Simple delete
        $msg->delete(); 
        return response()->json(['status' => 'success']);
    }

    // Added ClearChat to match your current Seller routes
    public function clearChat($receiverId, $type)
    {
        $sellerId = Auth::guard('seller')->id();
        Message::where(function($q) use ($sellerId, $receiverId) {
            $q->where('sender_id', $sellerId)->where('receiver_id', $receiverId);
        })->orWhere(function($q) use ($sellerId, $receiverId) {
            $q->where('sender_id', $receiverId)->where('receiver_id', $sellerId);
        })->delete(); 
        
        return response()->json(['status' => 'success']);
    }
}