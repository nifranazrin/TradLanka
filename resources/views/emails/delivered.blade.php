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
        <p style="font-size: 18px; color: #5b2c2c; font-weight: bold;">Order Delivered!</p>
        <p>Hi <strong>{{ $order->fname }} {{ $order->lname }}</strong>,</p>
        
        <p>Great news! Your order <strong>#{{ $order->tracking_no }}</strong> has been successfully delivered. We hope you enjoy your authentic Sri Lankan products!</p>

        <div style="background-color: #fdf6e3; padding: 20px; border-radius: 8px; text-align: center; margin: 25px 0; border: 1px dashed #5b2c2c;">
            <p style="margin-top: 0; font-weight: bold; color: #5b2c2c;">Share your experience!</p>
            <p style="font-size: 14px; color: #666;">How did you like your items? Your feedback helps support our local sellers.</p>
            
            {{-- Dynamic link to review the first item in the order --}}
            @php $firstItem = $order->items->first(); @endphp
            @if($firstItem)
                <a href="{{ url('/user/reviews/create/'.$firstItem->product_id) }}" 
                   style="background-color: #5b2c2c; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; margin-top: 10px;">
                    Write a Review
                </a>
            @endif
        </div>

        <p style="font-size: 13px; color: #666; text-align: center; margin-top: 30px;">
            Need help? Contact us at <a href="mailto:infotradlanka@gmail.com" style="color: #5b2c2c;">infotradlanka@gmail.com</a>
        </p>
    </div>

    {{-- Footer --}}
    <div style="background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #777;">
        <p style="margin: 0;">Thank you for shopping with TradLanka!</p>
    </div>
</div>