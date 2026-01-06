<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        /* Changed background-color to Maroon */
        .brand-header { width: 100%; background-color: #490808; color: #fff; border-collapse: collapse; }
        .brand-logo-img { width: 50px; height: 50px; border-radius: 5px; }
        .summary-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .summary-box { padding: 15px; border: 1px solid #eee; text-align: center; width: 25%; }
        .data-table { width: 100%; margin-top: 30px; border-collapse: collapse; }
        .data-table th { background: #f8f9fa; padding: 10px; text-align: left; border-bottom: 2px solid #ddd; }
        .data-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .text-success { color: #28a745; font-weight: bold; }
        .text-danger { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>

    {{-- Maroon Header Part --}}
    <table cellpadding="0" cellspacing="0" class="brand-header">
        <tr>
            <td style="padding: 20px 0 20px 40px; width: 60px;">
                <img src="{{ public_path('logo/tradlanka-logo.jpg') }}" class="brand-logo-img">
            </td>
            <td style="padding: 20px 0; vertical-align: middle;">
                <h1 style="margin:0; font-size: 22px; text-transform: uppercase;">TradLanka</h1>
                <div style="font-size: 10px; opacity: 0.9;">Authentic Sri Lankan Products</div>
            </td>
            <td style="text-align: right; padding-right: 40px;">
                <h2 style="margin:0;">SALES REPORT</h2>
                <div>Date: {{ $filterDate ?? 'All Time' }}</div>
            </td>
        </tr>
    </table>

    <table class="summary-table">
        <tr>
            {{-- LKR Box with Success Count --}}
            <td class="summary-box">
                <div style="font-size: 10px; color: #666;">LKR SUCCESS</div>
                <div style="font-size: 16px; font-weight: bold;">Rs. {{ number_format($successLKR, 2) }}</div>
                <small style="color: #28a745;">{{ $successLKRCount }} Successful Orders</small>
            </td>
            {{-- USD Box with Success Count --}}
            <td class="summary-box">
                <div style="font-size: 10px; color: #666;">USD SUCCESS</div>
                <div style="font-size: 16px; font-weight: bold;">$ {{ number_format($successUSD, 2) }}</div>
                <small style="color: #28a745;">{{ $successUSDCount }} Successful Orders</small>
            </td>
            {{-- Failed Orders Box --}}
            <td class="summary-box">
                <div style="font-size: 10px; color: #666;">FAILED / CANCELLED</div>
                <div style="font-size: 16px; font-weight: bold; color: #dc3545;">{{ $failedCount }} Orders</div>
                <small>Outcome Losses</small>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Status</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tableOrders as $order)
            <tr>
                <td>{{ $order->created_at->format('d M, Y') }}</td>
                <td>#{{ $order->tracking_no }}</td>
                <td>{{ $order->fname }}</td>
                <td class="{{ $order->status == 5 ? 'text-success' : 'text-danger' }}">
                    {{ $order->status == 5 ? 'Delivered' : 'Cancelled' }}
                </td>
                <td style="text-align: right;">
                    {{ $order->currency == 'USD' ? '$' : 'Rs.' }} {{ number_format($order->total_price, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>