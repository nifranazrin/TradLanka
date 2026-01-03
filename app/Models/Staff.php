<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable; 

class Staff extends Authenticatable
{
    use HasFactory, Notifiable; 

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',      
        'address', 
         'nic_number',   
        'role',
        'status',
        'image',  
    ];

    public function products()
    {
        return $this->hasMany(\App\Models\Product::class, 'seller_id');
    }

    /**
     * ✅ Relationship for messages RECEIVED by this staff member.
     * Essential for unread badge counts.
     */
    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * ✅ Relationship for messages SENT by this staff member.
     * Essential for sorting the sidebar by "latest interaction".
     */
    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
}