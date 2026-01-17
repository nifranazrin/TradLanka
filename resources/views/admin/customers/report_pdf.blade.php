<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; margin: 0; padding: 0; }
        
        /* Brand Header Styling */
        .brand-header { 
            width: 100%; 
            background-color: #3b0b0b; 
            color: white; 
            border-bottom: 3px solid #facc15; 
        }
        .brand-logo-img { 
            width: 60px; 
            height: 60px; 
            border-radius: 50%; 
            border: 2px solid white;
        }
        
        /* Table Styling */
        .report-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .report-table th { 
            background-color: #3b0b0b; 
            color: white; 
            padding: 12px 10px; 
            font-size: 12px; 
            text-transform: uppercase;
        }
        .report-table td { 
            border-bottom: 1px solid #eee; 
            padding: 10px; 
            font-size: 11px; 
            vertical-align: middle;
        }
        
        /* Badge and Currency Styling */
        .gold-tag { 
            color: #ffae00; 
            font-weight: bold; 
            font-size: 9px; 
            margin-right: 5px;
        }
        .total-amount { font-weight: bold; color: #3b0b0b; }
    </style>
</head>
<body>
    <table cellpadding="0" cellspacing="0" class="brand-header">
        <tr>
            <td style="padding: 20px 0 20px 40px; width: 70px;">
                {{-- Base64 encoding is the safest way to render images in dompdf --}}
                @php
                    $path = public_path('logo/tradlanka-logo.jpg');
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                @endphp
                <img src="{{ $base64 }}" class="brand-logo-img">
            </td>
            <td style="padding: 20px 0; vertical-align: middle;">
                <h1 style="margin:0; font-size: 22px; letter-spacing: 1px;">TRAD<span style="color:#facc15;">LANKA</span></h1>
                <div style="font-size: 10px; opacity: 0.9;">Authentic Sri Lankan Products</div>
            </td>
            <td style="text-align: right; padding-right: 40px; vertical-align: middle;">
                <h2 style="margin:0; font-size: 16px;">{{ $stats['report_title'] ?? 'CUSTOMER ANALYSIS' }}</h2>
                <div style="font-size: 9px;">Generated: {{ now()->format('d M, Y h:i A') }}</div>
            </td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th align="left" style="padding-left: 40px;">Customer Details</th>
                <th align="left">Country</th>
                <th align="center">Orders</th>
                <th align="right" style="padding-right: 40px;">Total Spend</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $user)
                @php $isSL = (strtolower($user->country ?? '') == 'sri lanka'); @endphp
                <tr>
                    <td style="padding-left: 40px;">
                        @if($user->orders_count >= 10) 
                            <span class="gold-tag">★ GOLD</span> 
                        @endif
                        <span style="font-size: 12px; font-weight: bold;">{{ $user->name ?? ($user->fname . ' ' . $user->lname) }}</span><br>
                        <span style="color: #666;">ID: #{{ $user->id }} | {{ $user->email }}</span>
                    </td>
                    <td>
                        <span style="text-transform: capitalize;">{{ $user->country ?? 'N/A' }}</span>
                    </td>
                    <td align="center">
                        <span style="background: #f8f9fa; padding: 2px 8px; border-radius: 10px;">{{ $user->orders_count }}</span>
                    </td>
                    <td align="right" class="total-amount" style="padding-right: 40px;">
                        @if($isSL)
                            Rs. {{ number_format($user->total_spent ?? 0, 2) }}
                        @else
                            $ {{ number_format($user->total_spent ?? 0, 2) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer Summary --}}
    <div style="margin-top: 30px; padding: 0 40px; text-align: right; font-size: 10px; color: #999;">
        <p>This is a computer-generated report for TradLanka Admin Management.</p>
    </div>
</body>
</html>