<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification
{
    use Queueable;
    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Stores in your notifications table
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Your order #{$this->order->tracking_no} has been cancelled and stock restored.",
            'tracking_no' => $this->order->tracking_no,
            'order_id' => $this->order->id,
            'type' => 'order_cancelled', // Match this in header.blade.php for red styling
            'url' => url('track-order?tracking_no=' . $this->order->tracking_no),
        ];
    }
}