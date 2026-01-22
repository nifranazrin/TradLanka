<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>TradLanka Restock Alert</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f1ee; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; margin-top: 20px; margin-bottom: 20px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        
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

        {{-- Main Body --}}
        <tr>
            <td style="padding: 40px 30px; text-align: center;">
                <h2 style="color: #5b2c2c; margin-top: 0;">Good News! It's Back.</h2>
                <p style="color: #4a4a4a; font-size: 16px; line-height: 1.6;">
                    You recently showed interest in one of our products. We wanted to let you know that it is now back in stock and ready for you to enjoy!
                </p>

                {{-- Product Card --}}
                {{-- Simplified Product Card without Price --}}
                <div style="margin: 30px 0; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px; background-color: #fafafa;">
                    <h3 style="margin: 0; color: #333333; font-size: 18px;">{{ $product->name }}</h3>
                    <p style="color: #5b2c2c; font-size: 16px; font-weight: bold; margin: 10px 0;">
                        <i class="fas fa-check-circle"></i> NOW AVAILABLE
                    </p>
                    <p style="color: #666666; font-size: 14px;">Hurry! Stock is limited.</p>
                </div>

                {{-- Action Button --}}
                <a href="{{ url('/product/' . $product->slug) }}" 
                   style="display: inline-block; background-color: #5b2c2c; color: #ffffff; padding: 15px 35px; text-decoration: none; font-weight: bold; border-radius: 5px; text-transform: uppercase; font-size: 14px;">
                    Buy It Now
                </a>
            </td>
        </tr>

        {{-- Footer --}}
        <tr>
            <td style="padding: 20px 30px; background-color: #f9f9f9; text-align: center; font-size: 12px; color: #999999;">
                <p style="margin: 0;">You received this because you added this item to your cart at TradLanka.</p>
                <p style="margin: 5px 0;">&copy; {{ date('Y') }} TradLanka. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>