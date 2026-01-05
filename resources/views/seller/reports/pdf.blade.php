<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>TradLanka Inventory Report</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Branded Header */
        .brand-header {
            background: #5b2c2c;
            color: #ffffff;
            padding: 20px;
            width: 100%;
        }
        .brand-logo img {
            height: 50px;
            width: auto;
        }
        .brand-title {
            font-size: 26px;
            font-weight: bold;
            margin: 0;
            color: #ffffff;
        }
        .brand-subtitle {
            font-size: 10px;
            opacity: 0.9;
            margin-top: 2px;
        }

        /* Report Info */
        .report-details {
            padding: 15px 20px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Data Table */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .content-table th {
            background-color: #f3f4f6;
            color: #5b2c2c;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            padding: 10px;
            border-bottom: 2px solid #5b2c2c;
            text-align: left;
        }
        .content-table td {
            padding: 10px;
            border-bottom: 1px solid #eeeeee;
            vertical-align: middle;
        }

        /* Status Colors */
        .text-danger { color: #dc3545; font-weight: bold; }
        .text-success { color: #198754; font-weight: bold; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #777;
            padding-bottom: 20px;
        }
    </style>
</head>
<body>

    {{-- Branded Header --}}
    <table class="brand-header" cellpadding="0" cellspacing="0">
        <tr>
            <td class="brand-logo" width="70">
                {{-- Ensure your logo path is correct in public/logo/tradlanka-logo.jpg --}}
                <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('logo/tradlanka-logo.jpg'))) }}">
            </td>
            <td>
                <h1 class="brand-title">TradLanka</h1>
                <div class="brand-subtitle">Authentic Sri Lankan Products</div>
            </td>
            <td align="right" style="padding-right: 10px;">
                <div style="font-size: 12px; font-weight: bold;">{{ $stats['report_title'] }}</div>
                <div style="font-size: 9px; margin-top: 4px;">Generated: {{ $stats['date'] }}</div>
            </td>
        </tr>
    </table>

    {{-- Seller Info --}}
    <div class="report-details">
        <strong>Seller Name:</strong> {{ $stats['seller_name'] }}
    </div>

    {{-- Main Inventory Table --}}
    <table class="content-table">
        <thead>
            <tr>
                <th width="15%">ID</th>
                <th width="45%">Product Name</th>
                <th width="10%" style="text-align: center;">Stock</th>
                <th width="15%" style="text-align: center;">Sales</th>
                <th width="15%" style="text-align: right;">Price</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>PRD-{{ str_pad($product->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td align="center">
                        <span class="{{ $product->stock < 5 ? 'text-danger' : '' }}">
                            {{ $product->stock }}
                        </span>
                    </td>
                    <td align="center">
                        @if($reportType == 'top_selling')
                            <span class="text-success">{{ $product->total_sold ?? 0 }} Units</span>
                        @else
                            -
                        @endif
                    </td>
                    <td align="right">Rs. {{ number_format($product->price, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" align="center" style="padding: 30px;">No records found for this criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        TradLanka Marketplace System Generated Report &copy; {{ date('Y') }}
    </div>

</body>
</html>