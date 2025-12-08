<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminProductNotification extends Notification
{
    use Queueable;

    public $product;
    public $type; // 'new' or 'update'

    public function __construct($product, $type)
    {
        $this->product = $product;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['database']; // Store in the DB table
    }

    public function toArray($notifiable)
    {
        $message = '';
        if ($this->type == 'new') {
            $message = 'New Product Pending: ' . $this->product->name;
        } else {
            $message = 'Product Edited (Re-Check): ' . $this->product->name;
        }

        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'seller_name' => $this->product->seller->name ?? 'Unknown',
            'message' => $message,
            'link' => route('admin.products.show', $this->product->id), // Direct link to admin view
        ];
    }
}