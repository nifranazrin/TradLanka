<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderDeliveredNotification extends Notification
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     * Passes the order model to access tracking numbers and create the review link.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     * Set to 'database' to use your existing notifications table.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     * This data is stored in the 'data' JSON column.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Your product was successfully delivered! Order #{$this->order->tracking_no}",
            'tracking_no' => $this->order->tracking_no,
            'order_id' => $this->order->id,
            'type' => 'delivery_success',
            // Points to the My Reviews page shown in your screenshot
            'url' => route('user.reviews.index'), 
        ];
    }
}