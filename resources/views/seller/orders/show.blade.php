@extends('layouts.seller')

@section('content')

{{-- CUSTOM STYLES --}}
<style>
    .text-maroon { color: #800000 !important; }
    .bg-maroon { background-color: #800000 !important; color: white !important; }
    
    .info-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        background: white;
    }
    
    .info-label { font-size: 0.85rem; text-transform: uppercase; color: #6c757d; font-weight: 600; margin-bottom: 4px; display: block; }
    .info-value { font-size: 1rem; font-weight: 500; color: #212529; }

    .table-custom th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        border-bottom: 2px solid #e9ecef;
    }
    
    .product-img-box {
        width: 60px; height: 60px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 2px;
        background: white;
    }
    .product-img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; }

    .variant-badge {
        font-size: 0.75rem;
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 600;
        display: inline-block;
        margin-top: 4px;
    }

            /* Add these to your <style> section so the status badges show colors */
        .badge-new { background: #fef3c7; color: #92400e; }
        .badge-received { background: #e0f2fe; color: #075985; }
        .badge-packed { background: #ede9fe; color: #5b21b6; }
        .badge-delivered { background: #dcfce7; color: #166534; }
</style>

<div class="container-fluid px-4 py-5">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-maroon mb-0">Order #{{ $order->tracking_no }}</h3>
            <span class="text-muted small">Placed on {{ $order->created_at->timezone('Asia/Colombo')->format('d M Y, h:i A') }}</span>
        </div>
        
        <div>
            @php
               $statusMap = [
                            0  => ['text' => 'New Order',                     'class' => 'badge-new'],
                            1  => ['text' => 'Received – Waiting to Pack',    'class' => 'badge-received'],
                            2  => ['text' => 'Packed',                        'class' => 'badge-packed'],
                            3  => ['text' => 'At Head Office',                'class' => 'badge-delivered'],
                            4  => ['text' => 'Handed to Delivery',            'class' => 'bg-info text-dark'],
                            5  => ['text' => 'Delivered',                     'class' => 'bg-success text-white'],
                            6  => ['text' => 'Order Cancelled',               'class' => 'bg-danger text-white'], 
                            7  => ['text' => '⚠️ Cancellation Requested',     'class' => 'bg-warning text-dark'],
                            8  => ['text' => '✅ Approved for Refund',        'class' => 'bg-success text-white'],
                            10 => ['text' => 'Arrived in Destination Country','class' => 'badge-packed'],
                        ];

                        $statusData = $statusMap[$order->status] ?? ['text' => 'Status: ' . $order->status, 'class' => 'bg-secondary'];
                // ✅ IMPROVED CURRENCY LOGIC: 
                // Check for USD in payment mode OR if country is not Sri Lanka
                $isUSD = str_contains(strtoupper($order->payment_mode), 'USD') || 
                         str_contains(strtolower($order->payment_mode), 'stripe') ||
                         ($order->country && $order->country !== 'Sri Lanka');
                
                $symbol = $isUSD ? '$ ' : 'Rs. ';
            @endphp
            <span class="badge {{ $statusData['class'] }} fs-6 px-3 py-2">
                {{ $statusData['text'] }}
            </span>
        </div>
    </div>

    <div class="row g-4">
        {{-- LEFT COLUMN --}}
        <div class="col-lg-8">
            
            {{-- 1. CUSTOMER & ADDRESS --}}
            <div class="card info-card mb-4">
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <h6 class="text-maroon fw-bold mb-3"><i class="bi bi-person-circle me-2"></i>Customer Details</h6>
                            <div class="mb-3">
                                <span class="info-label">Name</span>
                                <span class="info-value">{{ $order->fname }} {{ $order->lname }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="info-label">Contact Info</span>
                                <span class="info-value">
                                    <i class="bi bi-telephone me-1 text-muted"></i> {{ $order->phone }} <br>
                                    <i class="bi bi-envelope me-1 text-muted"></i> {{ $order->email }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 ps-md-4">
                            <h6 class="text-maroon fw-bold mb-3"><i class="bi bi-geo-alt-fill me-2"></i>Shipping Address</h6>
                            <div class="info-value lh-base">
                                {{ $order->address1 }} <br>
                                @if($order->address2) {{ $order->address2 }} <br> @endif
                                {{ $order->city }}, {{ $order->state }} - {{ $order->zipcode }} <br>
                                <strong class="text-uppercase" style="font-size: 0.8rem; color: #666;">{{ $order->country }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Customer's Message Card --}}
            @if($order->message)
            <div class="card info-card mb-4" style="border-left: 5px solid #800000; background-color: #fffdf5;">
                <div class="card-body p-4">
                    <h6 class="text-maroon fw-bold mb-3">
                        <i class="bi bi-chat-quote-fill me-2"></i>Customer's Special Note
                    </h6>
                    <div class="p-3 bg-white rounded border" style="font-size: 1rem; color: #333; line-height: 1.6; font-style: italic;">
                        "{{ $order->message }}"
                    </div>
                </div>
            </div>
            @endif

            {{-- 2. ORDER ITEMS --}}
            <div class="card info-card">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="text-maroon fw-bold mb-0"><i class="bi bi-box-seam me-2"></i>Order Items</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Product</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end pe-4">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $itemsSubtotal = 0; @endphp
                                @foreach($order->items as $item)
                                    @php 
                                        $rowTotal = $item->qty * $item->price; 
                                        $itemsSubtotal += $rowTotal;
                                    @endphp
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="product-img-box me-3 flex-shrink-0">
                                                    @php
                                                        $imgUrl = $item->product->image ? \Illuminate\Support\Facades\Storage::url(preg_replace('/^public\//', '', $item->product->image)) : asset('images/placeholder.png');
                                                    @endphp
                                                    <img src="{{ $imgUrl }}" class="product-img">
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $item->product->name }}</div>
                                                    @if($item->variant)
                                                        <span class="variant-badge">
                                                            <i class="bi bi-tag-fill me-1"></i>{{ $item->variant->unit_label }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center text-muted">{{ $symbol }}{{ number_format($item->price, 2) }}</td>
                                        <td class="text-center fw-bold">{{ $item->qty }}</td>
                                        <td class="text-end pe-4 fw-bold text-maroon">{{ $symbol }}{{ number_format($rowTotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 3. STATUS UPDATE ACTIONS --}}
            @if($order->status < 3)
            <div class="card info-card mt-4 bg-light border-0">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Update Order Status</h6>
                        <p class="mb-0 small text-muted">Move this order to the next stage.</p>
                    </div>

                    <form action="{{ route('seller.orders.update', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @if($order->status == 0)
                            <button name="status" value="1" class="btn btn-info text-white fw-bold px-4 shadow-sm">
                                <i class="bi bi-check-circle me-2"></i> Accept & Receive
                            </button>
                        @elseif($order->status == 1)
                            <button name="status" value="2" class="btn btn-primary fw-bold px-4 shadow-sm">
                                <i class="bi bi-box-seam me-2"></i> Mark as Packed
                            </button>
                        @elseif($order->status == 2)
                            <button name="status" value="3" class="btn btn-dark fw-bold px-4 shadow-sm">
                                <i class="bi bi-truck me-2"></i> Hand over to Head Office
                            </button>
                        @endif
                    </form>
                </div>
            </div>
            @endif

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-4">
            {{-- PAYMENT INFO --}}
            <div class="card info-card mb-4">
                <div class="card-body">
                    <h6 class="text-maroon fw-bold mb-3">Payment Info</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Method:</span>
                        <span class="fw-bold text-uppercase">{{ $order->payment_mode }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Status:</span>
                        @if(str_contains(strtolower($order->payment_mode), 'cod'))
                            <span class="badge bg-warning text-dark">Unpaid (COD)</span>
                        @else
                            <span class="badge bg-success">Paid</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- SUMMARY --}}
            @php
                // Correctly calculate delivery fee based on total paid minus items total
                $deliveryFee = $order->total_price - $itemsSubtotal;
                if($deliveryFee < 0.01) $deliveryFee = 0; 
            @endphp
            <div class="card info-card bg-light border shadow-sm">
                <div class="card-body">
                    <h6 class="text-maroon fw-bold mb-3">Order Summary</h6>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-bold">{{ $symbol }}{{ number_format($itemsSubtotal, 2) }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                        <span class="text-muted">Delivery Fee</span>
                        <span class="fw-bold">{{ $symbol }}{{ number_format($deliveryFee, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fs-5 fw-bold text-dark">Grand Total</span>
                        <span class="fs-4 fw-bold text-maroon">{{ $symbol }}{{ number_format($order->total_price, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- LINKS --}}
            <div class="d-grid gap-2 mt-4">
                <a href="{{ route('seller.orders.pdf', $order->id) }}" class="btn btn-success py-2 fw-bold shadow-sm">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Download Invoice
                </a>
                <a href="{{ route('seller.orders.index') }}" class="btn btn-outline-secondary py-2 fw-bold">
                    <i class="bi bi-arrow-left me-2"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

@endsection