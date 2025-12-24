<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->tracking_no }}</title>

    <style>
        /* General Reset & Typography */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            margin: 0; padding: 0;
        }

        /* Layout */
        .container { padding: 20px 40px; }

        /* Brand Header */
        .brand-header {
            width: 100%;
            background-color: #4d0c0c;
            color: #ffffff;
            
            border-bottom: 4px solid #5a0000;
            table-layout: fixed;
        }
        
        .brand-logo-img {
            width: 50px;
            height: 50px;
            border-radius: 25px; 
            border: 2px solid #ffffff; 
            object-fit: cover;
        }

        .content-wrapper {
        padding: 20px 40px;
        }
        
        .brand-text-container h1 { margin: 0; font-size: 22px; text-transform: uppercase; line-height: 1; }
        .brand-header .sub-text { font-size: 10px; opacity: 0.9; margin-top: 4px; }

        /* Invoice Meta */
        .invoice-meta { text-align: right; margin-bottom: 25px; margin-top: 20px; }
        .invoice-meta h2 { margin: 0; font-size: 18px; color: #800000; }
        .invoice-meta p { margin: 1px 0; font-size: 11px; color: #555; }

        /* Details Grid */
        .details-box { width: 100%; margin-bottom: 25px; }
        .details-col { width: 48%; display: inline-block; vertical-align: top; }
        .details-title {
            font-size: 11px; font-weight: bold; color: #800000;
            text-transform: uppercase; border-bottom: 1px solid #ccc;
            padding-bottom: 4px; margin-bottom: 6px;
        }

        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th {
            background-color: #f3f3f3; color: #333; font-weight: bold;
            text-align: left; padding: 8px; border-bottom: 2px solid #ccc;
            font-size: 10px; text-transform: uppercase;
        }
        table td {
            padding: 8px; border-bottom: 1px solid #eee; vertical-align: middle;
        }

        /* Product Image Styling */
        .product-img {
            width: 40px; height: 40px; object-fit: cover;
            border-radius: 4px; border: 1px solid #ddd;
        }

        /* Variant Tag */
        .variant-tag {
            display: inline-block; font-size: 9px; color: #555;
            margin-top: 2px; background-color: #eee;
            padding: 1px 5px; border-radius: 3px; border: 1px solid #ddd;
        }

        /* Totals */
        .totals-table { width: 45%; float: right; border-top: 2px solid #800000; }
        .totals-table td { padding: 5px 10px; text-align: right; border-bottom: none; }
        .totals-table .amount { font-weight: bold; color: #333; }
        .totals-table .grand-total { font-size: 14px; color: #800000; padding-top: 8px; border-top: 1px solid #ddd; }

        /* Footer */
        .footer {
            margin-top: 50px; padding-top: 15px; border-top: 1px solid #eee;
            text-align: center; font-size: 9px; color: #999; clear: both;
        }

        /* Helpers */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { font-weight: bold; padding: 2px 6px; border-radius: 3px; font-size: 9px; text-transform: uppercase; }
        .badge-cod { background: #fff8e1; color: #b7791f; border: 1px solid #fbd38d; }
        .badge-paid { background: #def7ec; color: #03543f; border: 1px solid #84e1bc; }
    </style>
</head>

<body>
    {{-- 1. FULL WIDTH HEADER (No margins) --}}
    <table cellpadding="0" cellspacing="0" class="brand-header">
        <tr>
            <td style="padding: 20px 0 20px 40px; width: 60px;">
                <img src="{{ public_path('logo/tradlanka-logo.jpg') }}" class="brand-logo-img">
            </td>
            <td style="padding: 20px 0; vertical-align: middle;">
                <h1 style="margin:0; font-size: 22px; text-transform: uppercase;">TradLanka</h1>
                <div style="font-size: 10px; opacity: 0.9;">Authentic Sri Lankan Products</div>
            </td>
        </tr>
    </table>

    {{-- 2. WRAP ALL CONTENT IN THE PADDED DIV --}}
    <div class="content-wrapper">
        
        {{-- INVOICE META --}}
        <div class="invoice-meta">
            <h2>INVOICE</h2>
            <p><strong>Ref #:</strong> {{ $order->tracking_no }}</p>
            <p><strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}</p>
            <p>
                <strong>Status:</strong>
                @if(strtolower($order->payment_mode) === 'cod')
                    <span class="badge badge-cod">Unpaid (COD)</span>
                @else
                    <span class="badge badge-paid">PAID</span>
                @endif
            </p>
        </div>

        {{-- CUSTOMER DETAILS --}}
        <div class="details-box">
            <div class="details-col">
                <div class="details-title">Billed To</div>
                <div>{{ $order->fname }} {{ $order->lname }}</div>
                <div>{{ $order->phone }}</div>
                <div>{{ $order->email }}</div>
            </div>
            <div class="details-col text-right">
                <div class="details-title">Shipped To</div>
                <div>{{ $order->address1 }}</div>
                @if($order->address2) <div>{{ $order->address2 }}</div> @endif
                <div>{{ $order->city }}, {{ $order->state }}</div>
                <div>{{ $order->zipcode }}</div>
            </div>
        </div>

        {{-- CUSTOMER NOTES --}}
        @if($order->message)
        <div style="margin-bottom: 25px; padding: 10px; background-color: #fffdf5; border: 1px solid #ffe082;">
            <div class="details-title">Customer Special Message</div>
            <div style="font-style: italic; color: #333;">"{{ $order->message }}"</div>
        </div>
        @endif

        {{-- ORDER ITEMS TABLE --}}
        @php
            $isUSD = str_contains($order->payment_mode, 'USD');
            $symbol = $isUSD ? '$' : 'Rs. ';
            $itemsSubtotal = 0;
        @endphp

        <table cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th width="15%">Image</th>
                    <th width="40%">Description</th>
                    <th width="10%" class="text-center">Qty</th>
                    <th width="15%" class="text-right">Unit Price</th>
                    <th width="20%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    @php
                        $rowSubtotal = $item->qty * $item->price;
                        $itemsSubtotal += $rowSubtotal;
                        $imagePath = public_path('images/placeholder.png');
                        if ($item->product->image) {
                            $cleanPath = preg_replace('/^public\//', '', $item->product->image);
                            $checkPath = storage_path('app/public/' . $cleanPath);
                            if (file_exists($checkPath)) { $imagePath = $checkPath; }
                            elseif (file_exists(public_path('storage/' . $cleanPath))) { $imagePath = public_path('storage/' . $cleanPath); }
                        }
                    @endphp
                    <tr>
                        <td><img src="{{ $imagePath }}" class="product-img"></td>
                        <td>
                            <strong>{{ $item->product->name }}</strong>
                            @if($item->variant)<br><span class="variant-tag">Size: {{ $item->variant->unit_label }}</span>@endif
                        </td>
                        <td class="text-center">{{ $item->qty }}</td>
                        <td class="text-right">{{ $symbol }}{{ number_format($item->price, 2) }}</td>
                        <td class="text-right">{{ $symbol }}{{ number_format($rowSubtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- TOTALS --}}
        @php $deliveryFee = max(0, $order->total_price - $itemsSubtotal); @endphp
        <table class="totals-table">
            <tr><td>Subtotal:</td><td class="amount">{{ $symbol }}{{ number_format($itemsSubtotal, 2) }}</td></tr>
            <tr><td>Delivery Fee:</td><td class="amount">{{ $symbol }}{{ number_format($deliveryFee, 2) }}</td></tr>
            <tr>
                <td class="grand-total"><strong>Grand Total:</strong></td>
                <td class="amount grand-total"><strong>{{ $symbol }}{{ number_format($order->total_price, 2) }}</strong></td>
            </tr>
        </table>

        {{-- FOOTER --}}
        <div class="footer">
            <p>Thank you for choosing TradLanka!</p>
            <p>For inquiries, contact support at <strong>support@tradlanka.com</strong></p>
        </div>
    </div> {{-- End content-wrapper --}}
</body>
</html>