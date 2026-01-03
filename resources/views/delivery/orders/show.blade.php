@extends('layouts.delivery')

@section('content')
<style>
    :root {
        --trad-maroon: #5b2c2c;
        --trad-gold: #d97706;
    }
    .main-wrapper { background-color: #f8f9fa; min-height: 100vh; padding-bottom: 50px; }
    
    /* Card Styling */
    .order-card { 
        background: #ffffff; 
        border-radius: 12px; 
        border: none; 
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05); 
        margin-bottom: 1.5rem;
    }

    /* Hero Section */
    .payment-hero {
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        border: 1px solid transparent;
    }
    .cod-alert { background: #fff9db; border-color: #ffe066; color: #856404; }
    .paid-alert { background: #ebfbee; border-color: #b2f2bb; color: #2b8a3e; }

    /* Product Styling */
    .product-img { 
        width: 60px; height: 60px; 
        object-fit: cover; 
        border-radius: 8px; 
        border: 1px solid #eee;
    }

    /* Button Styling - The Fix */
    .action-container {
        max-width: 400px; /* Limits the width so it's not too big */
        margin: 2rem auto;
    }
    .btn-complete { 
        background-color: #2b8a3e; 
        color: white;
        border: none; 
        padding: 14px 28px; 
        font-size: 1rem;
        font-weight: 700;
        border-radius: 10px;
        width: 100%;
        transition: all 0.2s ease;
        box-shadow: 0 4px 6px rgba(43, 138, 62, 0.2);
    }
    .btn-complete:hover { background-color: #237032; transform: translateY(-2px); }
</style>

@php 
    $dbCurrency = strtoupper(trim($order->currency));
    $payMode = strtoupper($order->payment_mode);
    $total = $order->total_price;

    if ($dbCurrency === 'USD' || str_contains($payMode, '(USD)') || ($total < 500 && !str_contains($payMode, 'COD'))) {
        $symbol = '$';
    } else {
        $symbol = 'Rs.';
    }
    $isCOD = str_contains(strtolower($order->payment_mode), 'cod');
@endphp

<div class="main-wrapper container-fluid px-4 py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('delivery.my-deliveries') }}" class="btn btn-outline-secondary btn-sm rounded-circle me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="h5 fw-bold mb-0">Order #{{ $order->tracking_no }}</h1>
            <small class="text-muted">Assigned to you</small>
        </div>
        <a href="tel:{{ $order->phone }}" class="btn btn-primary btn-sm ms-auto rounded-pill px-3">
            <i class="bi bi-telephone-fill me-1"></i> Call Customer
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="payment-hero shadow-sm {{ $isCOD ? 'cod-alert' : 'paid-alert' }} mb-4">
                @if($isCOD)
                    <div class="small fw-bold text-uppercase mb-1">Collect Cash on Delivery</div>
                    <div class="h2 fw-bold mb-0">{{ $symbol }} {{ number_format($total, 2) }}</div>
                @else
                    <div class="small fw-bold text-uppercase mb-1">Already Paid Online</div>
                    <div class="h2 fw-bold mb-0 text-success">{{ $symbol }} {{ number_format($total, 2) }}</div>
                    <span class="badge bg-success mt-2">Do Not Collect Cash</span>
                @endif
            </div>

            <div class="order-card p-4">
                <div class="row">
                    <div class="col-md-6 border-end">
                        <h6 class="text-muted small text-uppercase fw-bold mb-3">Customer Details</h6>
                        <p class="mb-1 fw-bold">{{ $order->fname }} {{ $order->lname }}</p>
                        <p class="text-muted small">{{ $order->phone }}</p>
                    </div>
                    <div class="col-md-6 ps-md-4">
                        <h6 class="text-muted small text-uppercase fw-bold mb-3">Delivery Address</h6>
                        <p class="small mb-0">
                            {{ $order->address1 }}<br>
                            @if($order->address2) {{ $order->address2 }}<br> @endif
                            <strong>{{ $order->city }}, {{ $order->state }}</strong>
                        </p>
                    </div>
                </div>
            </div>

            <div class="order-card p-4">
                <h6 class="text-muted small text-uppercase fw-bold mb-3">Items Verification ({{ $order->items->count() }})</h6>
                @foreach($order->items as $item)
                <div class="d-flex align-items-center py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <img src="{{ $item->product->image ? \Illuminate\Support\Facades\Storage::url(str_replace('public/', '', $item->product->image)) : asset('images/placeholder.png') }}" class="product-img me-3">
                    <div class="flex-grow-1">
                        <div class="fw-bold">{{ $item->product->name }}</div>
                        <div class="small text-muted">Qty: {{ $item->qty }} @if($item->variant) | {{ $item->variant->unit_label }} @endif</div>
                    </div>
                    <div class="fw-bold">{{ $symbol }}{{ number_format($item->price, 2) }}</div>
                </div>
                @endforeach
            </div>

            <div class="action-container">
                @if($order->status == 4)
                    <form action="{{ route('delivery.mark-delivered', $order->id) }}" method="POST" onsubmit="return confirm('Confirm Delivery?')">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="btn-complete shadow">
                            <i class="bi bi-check-circle-fill me-2"></i> COMPLETE DELIVERY
                        </button>
                    </form>
                @else
                    <div class="alert alert-secondary text-center rounded-3">
                        <i class="bi bi-info-circle me-2"></i> This order is already processed.
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection