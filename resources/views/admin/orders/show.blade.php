@extends('layouts.admin')

@section('content')

{{-- CUSTOM STYLES --}}
<style>
    /* Professional Maroon Theme */
    .text-maroon { color: #5b2c2c !important; }
    .bg-maroon { background-color: #5b2c2c !important; color: white !important; }
    
    .info-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); /* Shaped corner shadow */
        background: white;
        width: 100%;
    }
    
    .info-label { font-size: 0.75rem; text-transform: uppercase; color: #6c757d; font-weight: 700; margin-bottom: 4px; display: block; letter-spacing: 0.5px; }
    .info-value { font-size: 0.95rem; font-weight: 500; color: #212529; }

    /* Refined International Badge */
    .intl-badge-refined {
        font-size: 0.7rem !important;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 50px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        vertical-align: middle;
        box-shadow: 0 2px 4px rgba(13, 110, 253, 0.2);
    }

    .table-custom thead th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        border-bottom: 2px solid #e9ecef;
        padding: 15px;
    }
    
    .product-img-box {
        width: 50px; height: 50px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 2px;
        background: white;
    }
    .product-img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; }

    .variant-badge {
        font-size: 0.7rem;
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        padding: 2px 8px;
        border-radius: 6px;
        font-weight: 600;
        display: inline-block;
        margin-top: 2px;
    }

    /* Dispatch Assignment Box */
    .dispatch-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        transition: border-color 0.3s;
    }
    .intl-border { border-color: #0d6efd !important; }
    .maroon-border { border-color: #5b2c2c !important; }
</style>

<div class="container-fluid px-4 py-5">
    @php
        /** * ULTIMATE CURRENCY LOGIC */
        $dbCurrency = strtoupper(trim($order->currency));
        $payMode = strtoupper($order->payment_mode);
        
        $isActuallyUSD = ($dbCurrency === 'USD' || str_contains($payMode, '(USD)'));
        $symbol = $isActuallyUSD ? '$ ' : 'Rs. ';

       /** * EXPANDED STATUS MAPPING */
    $statusMap = [
        3 => ['text' => 'At Head Office', 'class' => 'bg-info text-dark', 'icon' => 'bi-building'],
        4 => ['text' => 'Assigned to Rider', 'class' => 'bg-primary', 'icon' => 'bi-bicycle'],
        5 => ['text' => 'Delivered', 'class' => 'bg-success', 'icon' => 'bi-check-all'],
        6 => ['text' => 'Order Closed', 'class' => 'bg-secondary', 'icon' => 'bi-archive'],
        8 => ['text' => 'Seller Approved Refund', 'class' => 'bg-warning text-dark', 'icon' => 'bi-exclamation-octagon'],
    ];
    $statusData = $statusMap[$order->status] ?? ['text' => 'Processing', 'class' => 'bg-secondary', 'icon' => 'bi-clock'];
@endphp

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
             <h3 class="fw-bold text-dark mb-1">
                Order #{{ $order->tracking_no }}
                @if($isActuallyUSD)
                    <span class="badge bg-primary intl-badge-refined ms-2">
                        <i class="bi bi-globe me-1"></i>International
                    </span>
                @endif
            </h3>
            <p class="text-muted small mb-0">Reviewing order details for dispatch and final processing</p>
        </div>
        
        {{-- HEADER STATUS BADGE --}}
<div class="text-end">
    <span class="badge {{ $statusData['class'] }} rounded-pill px-3 py-2 fw-bold shadow-sm">
        <i class="bi {{ $statusData['icon'] }} me-1"></i> {{ $statusData['text'] }}
    </span>
    {{-- ✅ Show Delivery Timestamp if Delivered --}}
    @if($order->status == 5 && $order->delivered_at)
        <div class="small text-muted mt-1 fw-bold">
            Delivered: {{ \Carbon\Carbon::parse($order->delivered_at)->format('d M, h:i A') }}
        </div>
    @endif
    </div>
    </div>

    <div class="row g-4">
        {{-- LEFT COLUMN --}}
        <div class="col-lg-8">
            {{-- 1. CUSTOMER & SHIPPING INFO --}}
            <div class="card info-card mb-4">
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <h6 class="text-maroon fw-bold mb-3"><i class="bi bi-person-badge me-2"></i>Customer Profile</h6>
                            <div class="mb-3">
                                <span class="info-label">Full Name</span>
                                <span class="info-value fw-bold">{{ $order->fname }} {{ $order->lname }}</span>
                            </div>
                            <div class="mb-0">
                                <span class="info-label">Contact Details</span>
                                <span class="info-value"><i class="bi bi-telephone text-muted me-1"></i> {{ $order->phone }}</span><br>
                                <span class="info-value"><i class="bi bi-envelope text-muted me-1"></i> {{ $order->email }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 ps-md-4">
                            <h6 class="text-maroon fw-bold mb-3"><i class="bi bi-geo-alt me-2"></i>Delivery Destination</h6>
                            <div class="info-value lh-base">
                                <span class="fw-bold d-block mb-1">{{ $order->address1 }}</span>
                                @if($order->address2) <span class="d-block">{{ $order->address2 }}</span> @endif
                                {{ $order->city }}, {{ $order->state }} <br>
                                <span class="text-muted small fw-bold">Postal: {{ $order->zipcode }}</span> <br>
                                <span class="badge bg-dark mt-2" style="font-size: 0.65rem; letter-spacing: 1px;">{{ strtoupper($order->country) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. PACKED ITEMS LIST --}}
            <div class="card info-card overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="text-maroon fw-bold mb-0"><i class="bi bi-box-seam me-2"></i>Packed Items Verification</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Product Details</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end pe-4">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="product-img-box me-3">
                                                    <img src="{{ $item->product->image ? \Illuminate\Support\Facades\Storage::url(str_replace('public/', '', $item->product->image)) : asset('images/placeholder.png') }}" class="product-img">
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $item->product->name }}</div>
                                                    @if($item->variant)
                                                        <span class="variant-badge">{{ $item->variant->unit_label }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold text-dark">{{ $item->qty }}</td>
                                        <td class="text-end pe-4 fw-bold text-dark">
                                            {{ $symbol }}{{ number_format($item->price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-4">
            <div class="d-flex flex-column gap-4">
                
                {{-- DISPATCH ASSIGNMENT BOX --}}
                @if($order->status == 3)
                    <div class="card info-card dispatch-card shadow-sm {{ $isActuallyUSD ? 'intl-border' : 'maroon-border' }}">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3 {{ $isActuallyUSD ? 'text-primary' : 'text-maroon' }}">
                                <i class="bi bi-truck-flatbed me-2"></i>Dispatch Assignment
                            </h6>
                            <form action="{{ route('admin.orders.assign', $order->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="info-label">Select Delivery Partner</label>
                                    <select name="rider_id" class="form-select shadow-none" required>
                                        <option value="" selected disabled>Choose Personnel</option>
                                        @foreach($deliveryPartners as $rider)
                                            <option value="{{ $rider->id }}">{{ $rider->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn {{ $isActuallyUSD ? 'btn-primary' : 'btn-success' }} w-100 fw-bold py-2 shadow-sm rounded-3">
                                    <i class="bi bi-send-check me-2"></i> Confirm & Dispatch
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- VALUE SUMMARY --}}
                <div class="card info-card bg-light border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="text-maroon fw-bold mb-3">Order Value Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small fw-bold">PAYMENT MODE</span>
                            <span class="badge {{ str_contains(strtolower($order->payment_mode), 'stripe') ? 'bg-primary text-white' : 'bg-success' }} py-1 px-2" style="font-size: 0.65rem;">
                                {{ strtoupper($order->payment_mode) }}
                            </span>
                        </div>
                        <hr class="my-2 opacity-10">
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="fw-bold text-muted small">TOTAL PAYABLE</span>
                            <span class="fs-4 fw-bold text-dark">
                                {{ $symbol }}{{ number_format($order->total_price, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- BACK BUTTON --}}
                <a href="{{ route('admin.orders.review') }}" class="btn btn-outline-dark py-2 fw-bold rounded-pill border-2">
                    <i class="bi bi-arrow-left me-2"></i> Back to Review List
                </a>

            </div>
        </div>
    </div>
</div>
@endsection