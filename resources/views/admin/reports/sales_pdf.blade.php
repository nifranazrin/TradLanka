<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        /* Professional Maroon Header */
        .brand-header { width: 100%; background-color: #490808; color: #fff; border-collapse: collapse; }
        .brand-logo-img { width: 50px; height: 50px; border-radius: 5px; }
        
        /* Filter Scope Styling */
        .filter-info { background: #fdf6e3; padding: 10px; margin-top: 10px; border: 1px solid #eee; border-radius: 5px; }
        .filter-tag { display: inline-block; background: #490808; color: white; padding: 2px 8px; border-radius: 3px; margin-right: 5px; font-size: 9px; }

        /* Summary Boxes Layout */
        .summary-table { width: 100%; margin-top: 20px; border-collapse: collapse; table-layout: fixed; }
        .summary-box { padding: 12px; border: 1px solid #eee; text-align: center; vertical-align: top; }
        
        /* Data Table Styling */
        .data-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .data-table th { background: #490808; color: white; padding: 8px; text-align: left; text-transform: uppercase; font-size: 10px; }
        .data-table td { padding: 8px; border-bottom: 1px solid #eee; }
        
        /* Status Colors */
        .text-success { color: #28a745; font-weight: bold; }
        .text-danger { color: #dc3545; font-weight: bold; }
        .text-pending { color: #d97706; font-weight: bold; }
        
        /* Footer Styling */
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>

    {{-- Maroon Header --}}
    <table cellpadding="0" cellspacing="0" class="brand-header">
        <tr>
            <td style="padding: 20px 0 20px 40px; width: 60px;">
                <img src="{{ public_path('logo/tradlanka-logo.jpg') }}" class="brand-logo-img">
            </td>
            <td style="padding: 20px 0; vertical-align: middle;">
                <h1 style="margin:0; font-size: 20px; text-transform: uppercase;">TradLanka</h1>
                <div style="font-size: 10px; opacity: 0.9;">Authentic Sri Lankan Products</div>
            </td>
            <td style="text-align: right; padding-right: 40px;">
                <h2 style="margin:0; font-size: 18px;">SALES REPORT</h2>
                <div style="font-size: 10px;">Generated: {{ now()->format('d M, Y H:i A') }}</div>
            </td>
        </tr>
    </table>

    {{-- Active Filters Section --}}
    <div class="filter-info">
        <strong>Report Scope:</strong>
        {{-- Safely check for filter existence to avoid "Undefined Variable" errors --}}
        @if(isset($filterDate) && $filterDate) <span class="filter-tag">Date: {{ $filterDate }}</span> @endif
        @if(isset($filterMonth) && $filterMonth) <span class="filter-tag">Month: {{ date('F', mktime(0, 0, 0, $filterMonth, 1)) }}</span> @endif
        @if(isset($filterCurrency) && $filterCurrency) <span class="filter-tag">Currency: {{ $filterCurrency }}</span> @endif
        @if(isset($filterStatus) && $filterStatus) 
            <span class="filter-tag">Status: {{ $filterStatus == 'pending' ? 'Pending' : ($filterStatus == '5' ? 'Success' : 'Failed') }}</span> 
        @endif
        @if(!isset($filterDate) && !isset($filterMonth) && !isset($filterCurrency) && !isset($filterStatus))
            <span class="filter-tag">All Time / Global</span>
        @endif
    </div>

    {{-- Financial Summary (Dynamic based on filters) --}}
    {{-- sales_pdf.blade.php - Final Optimized Summary Logic --}}
<table class="summary-table">
    <tr>
        {{-- 1. SUCCESS LKR: Hide if user is specifically looking at Failed or Pending --}}
        @if((!isset($filterStatus) || $filterStatus == '5' || $filterStatus == '') && 
            (!isset($filterCurrency) || $filterCurrency == 'LKR' || $filterCurrency == ''))
            <td class="summary-box">
                <div style="font-size: 9px; color: #666;">SUCCESS LKR</div>
                <div style="font-size: 14px; font-weight: bold;">Rs. {{ number_format($successLKR, 2) }}</div>
                <small style="color: #28a745;">{{ $successLKRCount }} Orders</small>
            </td>
        @endif

        {{-- 2. SUCCESS USD: Hide if status is Failed or Pending --}}
        @if((!isset($filterStatus) || $filterStatus == '5' || $filterStatus == '') && 
            (!isset($filterCurrency) || $filterCurrency == 'USD' || $filterCurrency == ''))
            <td class="summary-box">
                <div style="font-size: 9px; color: #666;">SUCCESS USD</div>
                <div style="font-size: 14px; font-weight: bold;">$ {{ number_format($successUSD, 2) }}</div>
                <small style="color: #28a745;">{{ $successUSDCount }} Orders</small>
            </td>
        @endif

        {{-- 3. TOTAL FAILED: Only show if Status is Failed or All --}}
        @if(!isset($filterStatus) || $filterStatus == '6' || $filterStatus == '')
            <td class="summary-box">
                <div style="font-size: 9px; color: #666;">TOTAL FAILED</div>
                <div style="font-size: 14px; font-weight: bold; color: #dc3545;">{{ $failedCount }} Orders</div>
                <div style="font-size: 8px; margin-top: 5px;">
                    COD (LKR): <strong>{{ $failedCODLKR }}</strong> | STRIPE (LKR): <strong>{{ $failedStripeLKR }}</strong>
                </div>
                <div style="font-size: 8px;">TOTAL USD: <strong>{{ $failedUSD }}</strong></div>
            </td>
        @endif

        {{-- 4. PENDING VALUE: Only show if Status is Pending or All --}}
        @if(!isset($filterStatus) || $filterStatus == 'pending' || $filterStatus == '')
            <td class="summary-box">
                <div style="font-size: 9px; color: #666;">PENDING VALUE</div>
                <div style="font-size: 11px;">Rs. {{ number_format($pendingLKR, 2) }}</div>
                <div style="font-size: 11px;">$ {{ number_format($pendingUSD, 2) }}</div>
                <small style="color: #d97706;">Awaiting Settlement</small>
            </td>
        @endif
    </tr>
</table>

    {{-- Transaction List --}}
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Currency</th>
                <th>Status</th>
                <th style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tableOrders as $order)
            <tr>
                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                <td><strong>#{{ $order->tracking_no }}</strong></td>
                <td>{{ $order->fname }} {{ $order->lname }}</td>
                <td>{{ $order->currency }}</td>
                <td>
                    {{-- Status mapping logic for Pending (warning), Delivered (success), and Cancelled (danger) --}}
                    @if($order->status == 5)
                        <span class="text-success">Delivered</span>
                    @elseif($order->status == 6)
                        <span class="text-danger">Cancelled</span>
                    @else
                        <span class="text-pending">Pending</span>
                    @endif
                </td>
                <td style="text-align: right; font-weight: bold;">
                    {{ $order->currency == 'USD' ? '$' : 'Rs.' }} {{ number_format($order->total_price, 2) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding: 20px; color: #999;">No transactions found for the selected filters.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        TradLanka Administrative Report - Confidential - Page 1
    </div>

</body>
</html>