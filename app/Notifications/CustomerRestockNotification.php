<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerRestockNotification extends Notification
{
    use Queueable;

    public $product;

    /**
     * Create a new notification instance.
     * We pass the Product model here to access its name and slug.
     */
    public function __construct($product)
    {
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     * We use 'database' to trigger the bell icon in your header.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     * This data is saved in the 'data' column of your 'notifications' table.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'restock',
            'product_id' => $this->product->id,
            'message'    => 'Good News! ' . $this->product->name . ' is back in stock.',
            'url'        => url('product/' . $this->product->slug), // Link to the product page
        ];
    }
}