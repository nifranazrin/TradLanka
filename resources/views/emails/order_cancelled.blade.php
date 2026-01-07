<div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
    
    {{-- Pretty Brand Header --}}
    <table cellpadding="0" cellspacing="0" style="width: 100%; background-color: #5b2c2c; color: #ffffff;">
        <tr>
            <td style="padding: 20px 0 20px 40px; width: 70px;">
                <img src="{{ $message->embed(public_path('logo/tradlanka-logo.jpg')) }}" 
                     style="width: 60px; height: 60px; border-radius: 50%; border: 2px solid #ffffff; object-fit: cover;">
            </td>
            <td style="padding: 20px 0; vertical-align: middle;">
                <h1 style="margin:0; font-size: 24px; text-transform: uppercase; color: #ffffff;">TradLanka</h1>
                <div style="font-size: 11px; opacity: 0.9; color: #ffffff;">Authentic Sri Lankan Products</div>
            </td>
        </tr>
    </table>

    {{-- Body Message --}}
    <div style="padding: 30px; line-height: 1.6; color: #333;">
        <p style="font-size: 18px; color: #dc3545; font-weight: bold;">Order Cancelled</p>
        <p>Hi <strong>{{ $order->fname }} {{ $order->lname }}</strong>,</p>
        
        <p>This email is to confirm that your order <strong>#{{ $order->tracking_no }}</strong> has been officially cancelled and the process is finalized.</p>

        <div style="background-color: #fff5f5; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; padding-bottom: 5px;"><strong>Cancellation Finalized:</strong> {{ now()->format('d M Y') }}</p>
            <p style="margin: 0; padding-bottom: 5px;"><strong>Amount:</strong> 
                {{ $order->currency == 'USD' ? '$' : 'Rs.' }} {{ number_format($order->total_price, 2) }}
            </p>
            <p style="margin: 0;"><strong>Refund Status:</strong> 
                {{-- Robust check for COD vs Online payment --}}
                @if(str_contains(strtolower($order->payment_mode), 'cod'))
                    No Refund Needed (Cash on Delivery)
                @else
                    Refund Processed (Will reflect in 5-10 business days)
                @endif
            </p>
        </div>

        <p>We apologize for any inconvenience this may have caused. If you have any questions, feel free to reach out to us.</p>

        <p style="font-size: 13px; color: #666; text-align: center; margin-top: 30px;">
            Support: <a href="mailto:infotradlanka@gmail.com" style="color: #5b2c2c;">infotradlanka@gmail.com</a>
        </p>
    </div>

    {{-- Footer --}}
    <div style="background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #777;">
        <p style="margin: 0;">We hope to serve you again soon. TradLanka.</p>
    </div>
</div>