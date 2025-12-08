<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewSellerNotification extends Notification
{
    use Queueable;

    public $seller;

    public function __construct($seller)
    {
        $this->seller = $seller;
    }

    public function via($notifiable)
    {
        return ['database']; // Store in DB
    }

    public function toArray($notifiable)
    {
        return [
            // 'new' gives it the Blue Plus icon in your dashboard
            'type' => 'new', 
            'message' => 'New Seller Registration: ' . $this->seller->name,
            // We use the seller's name here so the blade file doesn't crash
            'seller_name' => $this->seller->name, 
            // Link directly to the User Management page
            'link' => route('admin.seller.requests'), 
        ];
    }
}