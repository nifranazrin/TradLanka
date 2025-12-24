<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id', 
        'sender_type',
        'receiver_id', 
        'receiver_type',
        'message', 
        'attachment', 
        'is_read',
        // ✅ NEW FIELDS FOR REPLY & DELETE
        'reply_to_id', 
        'is_deleted_everyone', 
        'deleted_by_sender', 
        'deleted_by_receiver'
    ];

    // ✅ Relationship: Get the message this message is replying to
    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    // Helper to get image URL
    public function getAttachmentUrlAttribute()
    {
        if ($this->attachment) {
            return Storage::url($this->attachment);
        }
        return null;
    }
}