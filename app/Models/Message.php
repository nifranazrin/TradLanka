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
        'reply_to_id', 
        'is_deleted_everyone', 
        'deleted_by_sender', 
        'deleted_by_receiver'
    ];

    /**
     * Relationship: The user who sent the message.
     * This fixes the crash in AppServiceProvider.
     */
    /**
 * Relationship: Get the actual sender (Staff or User)
 * This fixes the "Wrong Identity" issue by looking at sender_type.
 */
     public function sender()
    {
        return $this->belongsTo(Staff::class, 'sender_id');
    }

    /**
     * Relationship: Get the message this message is replying to.
     */
    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /**
     * Helper to get image/attachment URL.
     */
    public function getAttachmentUrlAttribute()
    {
        if ($this->attachment) {
            return Storage::url($this->attachment);
        }
        return null;
    }
}