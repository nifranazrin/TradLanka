@extends('layouts.admin')

@section('content')

{{-- CUSTOM STYLES --}}
<style>
    .text-maroon { color: #5b2c2c !important; }
    .bg-maroon { background-color: #5b2c2c !important; color: white !important; }
    
    .info-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        background: white;
        width: 100%;
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
        background-color: #e0f2fe;
        color: #075985;
        border: 1px solid #bae6fd;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 600;
        display: inline-block;
        margin-top: 4px;
    }

  
</style>

<div class="container-fluid px-4 py-5">
    @php
        /** * ULTIMATE CURRENCY LOGIC 
         * This handles the cases where the DB might have LKR labels for USD values.
         */
        $dbCurrency = strtoupper(trim($order->currency));
        $payMode = strtoupper($order->payment_mode);
        
        // Force USD if either the currency column says so or the payment mode contains (USD)
        $isActuallyUSD = ($dbCurrency === 'USD' || str_contains($payMode, '(USD)'));
        $symbol = $isActuallyUSD ? '$ ' : 'Rs. ';

        $statusMap = [
            3 => ['text' => 'At Head Office', 'class' => 'bg-info text-dark'],
            4 => ['text' => 'Assigned to Rider', 'class' => 'bg-primary'],
        ];
        $statusData = $statusMap[$order->status] ?? ['text' => 'Processing', 'class' => 'bg-secondary'];
        
    @endphp

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
             <h3 class="fw-bold text-maroon mb-0 d-inline-flex align-items-center">
                Reviewing Order #{{ $order->tracking_no }}
                @if($isActuallyUSD)
                    <span class="badge bg-primary ms-2 fs-6 shadow-sm animated fadeIn">
                        <i class="bi bi-globe me-1"></i>INTERNATIONAL SHIPMENT
                    </span>
                @endif
            </h3>
            <span class="text-muted small">Head Office Processing Phase</span>
        </div>
        
        <div>
            <span class="badge {{ $statusData['class'] }} fs-6 px-3 py-2">
                <i class="bi bi-building me-1"></i> {{ $statusData['text'] }}
            </span>
        </div>
    </div>

    <div class="row g-4">
        {{-- LEFT COLUMN --}}
        <div class="col-lg-8">
            {{-- 1. CUSTOMER & SHIPPING --}}
            <div class="card info-card mb-4">
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <h6 class="text-maroon fw-bold mb-3"><i class="bi bi-person-badge me-2"></i>Customer Information</h6>
                            <div class="mb-3">
                                <span class="info-label">Full Name</span>
                                <span class="info-value">{{ $order->fname }} {{ $order->lname }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="info-label">Contact Details</span>
                                <span class="info-value">{{ $order->phone }} <br> {{ $order->email }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 ps-md-4">
                            <h6 class="text-maroon fw-bold mb-3"><i class="bi bi-truck me-2"></i>Delivery Location</h6>
                            <div class="info-value lh-base">
                                {{ $order->address1 }} <br>
                                @if($order->address2) {{ $order->address2 }} <br> @endif
                                {{ $order->city }}, {{ $order->state }} <br>
                                <span class="text-muted">Postal Code: {{ $order->zipcode }}</span> <br>
                                <strong class="text-uppercase" style="font-size: 0.8rem;">{{ $order->country }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. ITEM VERIFICATION --}}
            <div class="card info-card">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="text-maroon fw-bold mb-0"><i class="bi bi-list-check me-2"></i>Packed Items Verification</h6>
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
                                                    <div class="fw-bold">{{ $item->product->name }}</div>
                                                    @if($item->variant)
                                                        <span class="variant-badge">{{ $item->variant->unit_label }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold fs-5">{{ $item->qty }}</td>
                                        <td class="text-end pe-4 fw-bold">
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
                
                {{-- QUICK ASSIGNMENT BOX --}}
                @if($order->status == 3)
                    <div class="card info-card shadow" style="border: 2px solid {{ $isActuallyUSD ? '#0d6efd' : '#5b2c2c' }};">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3 {{ $isActuallyUSD ? 'text-primary' : 'text-maroon' }}">
                                <i class="bi bi-truck-flatbed me-2"></i>Dispatch Assignment
                            </h6>
                            <form action="{{ route('admin.orders.assign', $order->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="info-label">Select Delivery Partner</label>
                                    <select name="rider_id" class="form-select" required>
                                        <option value="" selected disabled>Choose Personnel</option>
                                        @foreach($deliveryPartners as $rider)
                                            <option value="{{ $rider->id }}">{{ $rider->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn {{ $isActuallyUSD ? 'btn-primary' : 'btn-success' }} w-100 fw-bold py-2 shadow-sm">
                                    <i class="bi bi-send-check me-2"></i> Confirm & Dispatch
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- TOTALS SUMMARY --}}
                <div class="card info-card bg-light border shadow-sm">
                    <div class="card-body">
                        <h6 class="text-maroon fw-bold mb-3">Order Value</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Payment Mode:</span>
                            <span class="badge {{ str_contains(strtolower($order->payment_mode), 'stripe') ? 'bg-primary text-white' : 'bg-success' }}">
                                {{ strtoupper($order->payment_mode) }}
                            </span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-5 fw-bold">Total Payable</span>
                            <span class="fs-4 fw-bold text-maroon">
                                {{ $symbol }}{{ number_format($order->total_price, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- NAVIGATION --}}
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.orders.review') }}" class="btn btn-outline-secondary py-2 fw-bold">
                        <i class="bi bi-arrow-left me-2"></i> Back to Review List
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection