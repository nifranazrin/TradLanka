<div class="container" style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
    
    {{-- Header exactly like your working Confirmation Mail --}}
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

    <div style="padding: 30px; line-height: 1.6; color: #333;">
        <p style="font-size: 18px; color: #5b2c2c; font-weight: bold;">Delivery Completed!</p>
        <p style="font-size: 16px;">Hi <strong>{{ $order->fname }} {{ $order->lname }}</strong>,</p>
        
        <p>Great news! Your order <strong>#{{ $order->tracking_no }}</strong> has been successfully delivered and completed.</p>

        <div style="background-color: #f9f9f9; border-left: 4px solid #5b2c2c; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; padding-bottom: 5px;"><strong>Order Reference:</strong> {{ $order->tracking_no }}</p>
            <p style="margin: 0;"><strong>Payment Status:</strong> 
                @if(str_contains(strtoupper($order->payment_mode ?? ''), 'COD'))
                    Cash on Delivery (Paid)
                @else
                    Paid Online
                @endif
            </p>
        </div>

        {{-- Simple Review Link --}}
        <div style="background-color: #fdf6e3; padding: 20px; border-radius: 8px; text-align: center; border: 1px dashed #5b2c2c; margin: 25px 0;">
            <p style="font-weight: bold; color: #5b2c2c; margin-top: 0;">Give Us a Comment & Rate!</p>
            <p style="font-size: 14px; color: #666;">Please share your experience on our website to support our sellers.</p>
            
            <a href="{{ url('/user/reviews') }}" 
               style="background-color: #5b2c2c; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; margin-top: 10px;">
                Rate & Comment Here
            </a>
        </div>

        <p style="font-size: 13px; color: #666; text-align: center; margin-top: 30px;">
            Questions? Contact us at <a href="mailto:infotradlanka@gmail.com" style="color: #5b2c2c;">infotradlanka@gmail.com</a>
        </p>
    </div>

    <div style="background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #777;">
        <p style="margin: 0;">Thank you for shopping with TradLanka!</p>
    </div>
</div>