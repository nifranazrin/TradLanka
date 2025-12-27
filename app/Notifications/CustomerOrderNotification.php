<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerOrderNotification extends Notification
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     * Passes the order model so we can access tracking number and price.
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
     * This data will be stored in the 'data' JSON column of your table.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Successfully purchased! Your order has been placed.',
            'tracking_no' => $this->order->tracking_no,
            'total_amount' => 'Rs. ' . number_format($this->order->total_price, 2),
            'order_id' => $this->order->id,
            'type' => 'order_success'
        ];
    }
}