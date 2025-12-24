@extends('layouts.delivery')

@section('content')
<style>
    .order-details-card { background: #ffffff; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); border: none; }
    .item-row { border-bottom: 1px solid #eee; padding: 15px 0; }
    .item-row:last-child { border-bottom: none; }
    .product-img { width: 70px; height: 70px; object-fit: cover; border-radius: 10px; background-color: #f8f9fa; }
    
    /* Highlight for International/USD rows */
    .payment-alert { border-radius: 10px; padding: 15px; font-weight: 700; text-align: center; font-size: 1.1rem; }
    .international-border { border: 3px solid #0d6efd !important; }
</style>

@php 
    /** * ✅ ULTIMATE CURRENCY DETECTION
     * We check the DB currency column, the payment mode string, AND the value.
     */
    $dbCurrency = strtoupper(trim($order->currency));
    $payMode = strtoupper($order->payment_mode);
    $total = $order->total_price;

    // Safety Override: Force USD if value is small (< 500) and online payment, or labeled USD
    // This fixes the DB rows where 2.62 is labeled as LKR
    if ($dbCurrency === 'USD' || str_contains($payMode, '(USD)') || ($total < 500 && !str_contains($payMode, 'COD'))) {
        $symbol = '$ ';
        $isActuallyUSD = true;
    } else {
        $symbol = 'Rs. ';
        $isActuallyUSD = false;
    }

    $isCOD = str_contains(strtolower($order->payment_mode), 'cod');
@endphp

<div class="container-fluid px-3 py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('delivery.my-deliveries') }}" class="btn btn-light rounded-circle me-3 shadow-sm">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="h4 fw-bold mb-0">Order #{{ $order->tracking_no }}</h2>
        </div>
        <a href="tel:{{ $order->phone }}" class="btn btn-primary rounded-pill px-3 shadow-sm">
            <i class="bi bi-telephone-fill me-1 text-white"></i> Call Customer
        </a>
    </div>

    {{-- ✅ RIDER PAYMENT INSTRUCTIONS --}}
    @if($isCOD)
        <div class="payment-alert bg-warning text-dark mb-4 border border-warning shadow-sm">
            <i class="bi bi-cash-stack me-2"></i> CASH ON DELIVERY: COLLECT {{ $symbol }} {{ number_format($total, 2) }}
        </div>
    @else
        <div class="payment-alert bg-success text-white mb-4 shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> ONLINE PAID: DO NOT COLLECT CASH
        </div>
    @endif

    <div class="card order-details-card p-4 mb-4 {{ $isActuallyUSD ? 'international-border' : '' }}">
        <h6 class="fw-bold text-muted text-uppercase small mb-3">Items Verification</h6>
        
        @foreach($order->items as $item)
        <div class="item-row d-flex align-items-center">
            <img src="{{ $item->product->image ? \Illuminate\Support\Facades\Storage::url(str_replace('public/', '', $item->product->image)) : asset('images/placeholder.png') }}" 
                 onerror="this.src='{{ asset('images/placeholder.png') }}'"
                 class="product-img me-3"
                 alt="{{ $item->product->name }}">
            
            <div class="flex-grow-1">
                <div class="fw-bold text-dark">{{ $item->product->name }}</div>
                <div class="text-muted small">
                    Qty: <span class="text-primary fw-bold">{{ $item->qty }}</span> 
                    @if($item->variant) | <span class="badge bg-light text-dark">{{ $item->variant->unit_label }}</span> @endif
                </div>
            </div>
            <div class="fw-bold text-dark text-end">
                {{ $symbol }}{{ number_format($item->price, 2) }}
            </div>
        </div>
        @endforeach

        <hr class="my-4">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted">Payment Mode:</span>
            <span class="badge {{ $isCOD ? 'bg-warning text-dark' : 'bg-primary text-white' }} px-3 py-2">
                {{ strtoupper($order->payment_mode) }}
            </span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span class="h5 fw-bold mb-0">Total Value:</span>
            <span class="h4 fw-bold {{ $isCOD ? 'text-danger' : 'text-success' }} mb-0">
                {{ $symbol }}{{ number_format($total, 2) }}
            </span>
        </div>
    </div>

    @if($order->status == 4)
        <form action="{{ route('delivery.mark-delivered', $order->id) }}" method="POST" onsubmit="return confirm('Confirm that you have delivered the package?')">
            @csrf
            @method('PUT')
            <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-3 shadow">
                <i class="bi bi-check-circle me-2"></i> COMPLETE DELIVERY
            </button>
        </form>
    @else
        <div class="alert alert-secondary text-center fw-bold border-0 shadow-sm py-3">
            <i class="bi bi-info-circle-fill me-2"></i> 
            @if($order->status == 5)
                DELIVERED ON {{ $order->updated_at->format('d M, Y') }}
            @elseif($order->status == 6)
                MARKED AS FAILED
            @else
                ORDER PROCESSED
            @endif
        </div>
    @endif
</div>
@endsection