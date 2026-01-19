<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to TradLanka</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4;">
    <div class="container" style="font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background-color: #ffffff;">
        
        {{-- Maroon Brand Header --}}
        <table cellpadding="0" cellspacing="0" style="width: 100%; background-color: #5b2c2c; color: #ffffff;">
            <tr>
                <td style="padding: 20px 0 20px 40px; width: 70px;">
                    {{-- Logo embedding works through the WelcomeUserMail class --}}
                    <img src="{{ $message->embed(public_path('logo/tradlanka-logo.jpg')) }}" 
                         style="width: 60px; height: 60px; border-radius: 50%; border: 2px solid #ffffff; object-fit: cover;"
                         alt="TradLanka Logo">
                </td>
                <td style="padding: 20px 0; vertical-align: middle;">
                    <h1 style="margin:0; font-size: 24px; text-transform: uppercase; color: #ffffff;">TradLanka</h1>
                    <div style="font-size: 11px; opacity: 0.9; color: #ffffff;">Authentic Sri Lankan Products</div>
                </td>
            </tr>
        </table>

        {{-- Body Message --}}
        <div style="padding: 30px; line-height: 1.6; color: #333;">
            <p style="font-size: 18px; color: #5b2c2c; font-weight: bold;">Welcome to the Family!</p>
            
            {{-- Personalization using the $user variable from your Mailable --}}
            <p style="font-size: 16px;">Hi <strong>{{ $user->name }}</strong>,</p>
            
            <p>Your account has been successfully created. We are thrilled to have you with us! You can now enjoy a faster checkout experience, track your orders easily, and enjoy purchasing your favorite authentic Sri Lankan products at any time.</p>

            {{-- Butter CTA Box --}}
            <div style="background-color: #fdf6e3; padding: 20px; border-radius: 8px; text-align: center; border: 1px dashed #5b2c2c; margin: 25px 0;">
                <p style="font-weight: bold; color: #5b2c2c; margin-top: 0;">Ready to explore?</p>
                <p style="font-size: 14px; color: #666;">Start browsing our exclusive collections today.</p>
                
                <a href="{{ url('/') }}" 
                   style="background-color: #5b2c2c; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; margin-top: 10px;">
                    Start Shopping
                </a>
            </div>

            <p>If you have any questions or need assistance, our team is always here to help.</p>

            <p style="font-size: 13px; color: #666; text-align: center; margin-top: 30px;">
                Questions? Contact us at <a href="mailto:infotradlanka@gmail.com" style="color: #5b2c2c; text-decoration: none;">infotradlanka@gmail.com</a>
            </p>
        </div>

        {{-- Footer --}}
        <div style="background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #777;">
            <p style="margin: 0;">Thank you for choosing TradLanka!</p>
            <p style="margin: 5px 0 0 0;">© {{ date('Y') }} TradLanka. All rights reserved.</p>
        </div>
    </div>
</body>
</html>