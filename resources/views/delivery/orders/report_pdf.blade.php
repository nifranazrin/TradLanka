<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>TradLanka - Performance Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 11px; margin: 0; padding: 0; }
        .brand-header { width: 100%; background-color: #5b2c2c; color: #ffffff; }
        .brand-logo { padding: 15px 30px; width: 50px; }
        .brand-logo img { width: 50px; height: 50px; border-radius: 50%; border: 2px solid #ffffff; }
        .brand-title { font-size: 20px; text-transform: uppercase; margin: 0; letter-spacing: 1px; }
        
        .content { padding: 25px 35px; }
        .summary-table { width: 100%; margin-bottom: 25px; }
        .summary-box { background: #fdfaf9; border: 1px solid #eee; padding: 12px; border-radius: 8px; width: 48%; }
        .stat-val { font-size: 13px; font-weight: bold; color: #5b2c2c; margin-top: 5px; }
        
        table.data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #f8f8f8; color: #5b2c2c; padding: 10px; font-size: 9px; text-transform: uppercase; border-bottom: 2px solid #5b2c2c; text-align: left; }
        .data-table td { padding: 10px; border-bottom: 1px solid #f1f1f1; }
        
        .status-success { color: #198754; font-weight: bold; }
        .status-danger { color: #dc3545; font-weight: bold; }
        .badge-cod { background: #facc15; color: #333; padding: 2px 5px; border-radius: 3px; font-size: 8px; }
    </style>
</head>
<body>
    <table class="brand-header" width="100%">
        <tr>
            <td class="brand-logo"><img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('logo/tradlanka-logo.jpg'))) }}"></td>
            <td><h1 class="brand-title">TradLanka</h1><div style="font-size: 9px; opacity: 0.8;">Authentic Sri Lankan Products</div></td>
            <td align="right" style="padding-right: 30px; font-size: 9px;">Generated: {{ $stats['date'] }}</td>
        </tr>
    </table>

    <div class="content">
        <h2 style="text-align: center; color: #5b2c2c; text-transform: uppercase;">Performance Report</h2>

        <table class="summary-table">
            <tr>
                <td class="summary-box">
                    <div style="color: #777; font-size: 9px;">TASK PERFORMANCE</div>
                    <div class="stat-val">{{ $stats['delivered'] }} Success / {{ $stats['failed'] }} Failed</div>
                </td>
                <td width="4%"></td>
                <td class="summary-box">
                    {{-- ✅ UPDATED LABEL --}}
                    <div style="color: #777; font-size: 9px;">TOTAL CASH COLLECTED (COD)</div>
                    <div class="stat-val">
                        Rs. {{ number_format($stats['total_lkr'], 2) }}
                        @if($stats['total_usd'] > 0)
                            <span style="color: #666; font-size: 11px; margin-left: 8px;"> / $ {{ number_format($stats['total_usd'], 2) }}</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Order ID</th>
                    <th>Customer & Payment</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $task)
                @php
                    $payMode = strtoupper($task->payment_mode);
                    $isUSD = str_contains($payMode, 'USD');
                    $isCOD = str_contains($payMode, 'COD');
                    $symbol = $isUSD ? '$ ' : 'Rs. ';
                @endphp
                <tr>
                    <td>{{ $task->updated_at->format('d M Y') }}</td>
                    <td style="font-weight: bold;">#{{ $task->tracking_no }}</td>
                    <td>
                        <div style="font-weight: 500;">{{ $task->fname }} {{ $task->lname }}</div>
                        <div style="font-size: 8px; color: #666;">
                            {{ $task->city }} | 
                            @if($isCOD)
                                <span style="color: #b38600; font-weight: bold;">CASH ON DELIVERY</span>
                            @else
                                <span style="color: #198754;">PAID ONLINE</span>
                            @endif
                        </div>
                    </td>
                    <td style="font-weight: bold;">{{ $symbol }}{{ number_format($task->total_price, 2) }}</td>
                    <td class="{{ $task->status == 5 ? 'status-success' : 'status-danger' }}">
                        {{ $task->status == 5 ? 'DELIVERED' : 'FAILED' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>