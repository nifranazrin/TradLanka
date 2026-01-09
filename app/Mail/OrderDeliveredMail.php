<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order; 

    public function __construct($order)
    {
        // Keep it simple to match your working confirmation mail
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Delivered: #' . $this->order->tracking_no,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_delivered',
            with: ['order' => $this->order],
        );
    }
}