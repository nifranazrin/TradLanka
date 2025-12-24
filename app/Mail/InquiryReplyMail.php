<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InquiryReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $replyContent;
    public $customerName;

    public function __construct($replyContent, $customerName)
    {
        $this->replyContent = $replyContent;
        $this->customerName = $customerName;
    }

    public function build()
    {
        // This points to the view file 'emails.inquiry_reply'
        return $this->subject('Response to your Inquiry - TradLanka')
                    ->view('emails.inquiry_reply'); 
    }
}