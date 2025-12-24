<!DOCTYPE html>
<html>
<body>
    <h3>Hello {{ $customerName }},</h3>
    <p>Thank you for contacting TradLanka.</p>
    
    <p><strong>Our Reply:</strong></p>
    <div style="background: #f3f3f3; padding: 15px; border-left: 4px solid #5b2c2c;">
        {!! nl2br(e($replyContent)) !!}
    </div>

    <p>Best Regards,<br>TradLanka Team</p>
</body>
</html>