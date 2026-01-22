<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Models\Staff;
use App\Models\Order;
use App\Notifications\SellerDashboardNotification;

class ChatController extends Controller
{
    
    public function index()
    {
        $adminId = Auth::guard('admin')->id();

        // Mark incoming messages for admin as read globally on index load
        Message::where('receiver_id', $adminId)
            ->where('receiver_type', 'admin')
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        // Get Active Chats (Staff who have message history with Admin)
        $activeContacts = Staff::whereIn('role', ['seller', 'delivery'])
            ->where(function($q) use ($adminId) {
                $q->whereHas('messagesSent', function($sq) use ($adminId) { 
                    $sq->where('receiver_id', $adminId)->where('receiver_type', 'admin'); 
                })
                ->orWhereHas('messagesReceived', function($sq) use ($adminId) { 
                    $sq->where('sender_id', $adminId)->where('sender_type', 'admin'); 
                });
            })
            ->addSelect(['last_interaction' => Message::select('created_at')
                ->where(function($q) use ($adminId) {
                    $q->whereColumn('sender_id', 'staff.id')->where('receiver_id', $adminId);
                })->orWhere(function($q) use ($adminId) {
                    $q->whereColumn('receiver_id', 'staff.id')->where('sender_id', $adminId);
                })->latest()->take(1)
            ])
            ->orderByDesc('last_interaction')
            ->get();

        $activeIds = $activeContacts->pluck('id')->toArray();
        $allOtherStaff = Staff::where('id', '!=', $adminId)
            ->whereIn('role', ['seller', 'delivery'])
            ->whereNotIn('id', $activeIds)
            ->get();

        foreach($activeContacts as $contact) {
            $contact->unread_count = Message::where('sender_id', $contact->id)
                ->where('receiver_id', $adminId)
                ->where('is_read', false)
                ->count();
        }

        return view('admin.chat.index', compact('activeContacts', 'allOtherStaff'));
    }

    
    public function fetchMessages($receiverId, $type)
{
    $adminId = Auth::guard('admin')->id(); //
    
    return Message::where('deleted_by_admin', false) 
        ->where(function($q) use ($adminId, $receiverId, $type) {
            $q->where(function($sq) use ($adminId, $receiverId, $type) {
                $sq->where('sender_id', $adminId)->where('sender_type', 'admin')
                   ->where('receiver_id', $receiverId)->where('receiver_type', $type);
            })->orWhere(function($sq) use ($adminId, $receiverId, $type) {
                $sq->where('sender_id', $receiverId)->where('sender_type', $type)
                   ->where('receiver_id', $adminId)->where('receiver_type', 'admin');
            });
        })
        ->orderBy('created_at', 'asc')
        ->get(); //
}

    
    public function sendMessage(Request $request)
    {
        $adminId = Auth::guard('admin')->id();
        $message = new Message();
        $message->sender_id = $adminId;
        $message->sender_type = 'admin';
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

   
   public function clearChat($receiverId, $type)
{
    $adminId = Auth::guard('admin')->id();

    Message::where(function($q) use ($adminId, $receiverId, $type) {
        $q->where('sender_id', $adminId)->where('sender_type', 'admin')
          ->where('receiver_id', $receiverId)->where('receiver_type', $type);
    })->orWhere(function($q) use ($adminId, $receiverId, $type) {
        $q->where('sender_id', $receiverId)->where('sender_type', $type)
          ->where('receiver_id', $adminId)->where('receiver_type', 'admin');
    })->update(['deleted_by_admin' => true]); // Mark as hidden for Admin

    return response()->json(['status' => 'success']);
}

    /**
     * ✅ SINGLE DELETE LOGIC
     */
    public function deleteMessage(Request $request)
    {
        $msg = Message::findOrFail($request->id);
        $msg->delete(); 
        
        return response()->json(['status' => 'success']);
    }

    /**
     * Helpers (Profile, Orders, Read Status)
     */
    public function getProfile($type, $id) {
        $user = Staff::findOrFail($id);
        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'role'  => $user->role,
            'image' => $user->image,
            'email' => $user->email,
            'phone' => $user->phone
        ]);
    }

    public function getRecentOrders() { 
        return response()->json(Order::latest()->take(10)->get()); 
    }

    public function markAsRead($id, $type) {
        Message::where('sender_id', $id)
            ->where('receiver_id', Auth::guard('admin')->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        return response()->json(['status' => 'success']);
    }
}