@extends('layouts.admin')

@section('content')

{{-- CUSTOM STYLES --}}
<style>
    .order-review-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); 
        border: none;
    }

    .custom-table thead th {
        background-color: #5b2c2c;
        color: #ffffff;
        padding: 18px;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-assign {
        background-color: #198754;
        border: none;
        padding: 6px 15px;
        font-weight: 600;
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }

    .btn-view-details {
        border: 1.5px solid #6c757d;
        color: #495057;
        font-weight: 600;
        transition: all 0.2s;
    }

    .tracking-id {
        font-size: 1.1rem;
        color: #0d6efd;
    }

    .badge-assigned {
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .rider-select {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }
    /* Add this inside your existing <style> tag */
.international-highlight {
    background-color: #f0f7ff !important; 
    border-left: 5px solid #0d6efd !important;
}
</style>

<div class="container-fluid px-4 py-5">
    {{-- HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Review Orders & Delivery</h2>
            <p class="text-muted small">Orders handed over by Sellers. Track assignments and dispatch to Delivery Partners.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert" style="background-color: #dcfce7; color: #166534;">
            <i class="bi bi-check-circle-fill me-2"></i><strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- THE POPPING TABLE CARD --}}
    <div class="card order-review-card">
        <div class="table-responsive">
            <table class="table align-middle custom-table mb-0" style="min-width: 1000px;">
                <thead>
                    <tr>
                        <th class="ps-4">Order Details</th>
                        <th>Customer</th>
                        <th>Delivery Address</th>
                        <th class="text-center">Current Status</th>
                        <th class="text-center pe-4">Dispatch Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)

                    @php
        $dbCurrency = strtoupper(trim($order->currency));
        $payMode = strtoupper($order->payment_mode);
        
        // Logical check for highlight and symbol
        $isActuallyUSD = ($dbCurrency === 'USD' || str_contains($payMode, '(USD)'));
        $symbol = $isActuallyUSD ? '$ ' : 'Rs. ';
    @endphp
                       <tr class="border-bottom {{ $isActuallyUSD ? 'international-highlight' : '' }}">
                           

                            {{-- ORDER ID & PRICE --}}
<td class="ps-4 py-4">
    <div class="d-flex align-items-center mb-1">
        <span class="fw-bold tracking-id">{{ $order->tracking_no }}</span>
        
        {{-- ✅ STRICT INTERNATIONAL LOGIC: Only show if DB currency is USD --}}
        @if(strtoupper(trim($order->currency)) === 'USD')
            <span class="badge bg-primary ms-2" style="font-size: 0.65rem;">
                <i class="bi bi-globe me-1"></i>INTERNATIONAL (USD)
            </span>
        @endif
    </div>

    <div class="small text-muted mt-1">
        <i class="bi bi-calendar3 me-1"></i>{{ $order->created_at->format('d M, Y | h:i A') }}
    </div>

    <div class="mt-2 d-flex align-items-center">
        @if(strtolower($order->payment_mode) === 'cod')
            <span class="badge bg-warning text-dark fw-bold px-3 py-1">COD</span>
        @else
            <span class="badge bg-success fw-bold px-3 py-1 text-white">PAID</span>
        @endif

        @php
            /** ✅ ULTIMATE CURRENCY SYMBOL LOGIC
             * Check the specific currency column and backup with payment mode string
             */
            $dbCurrency = strtoupper(trim($order->currency));
            $payMode = strtoupper($order->payment_mode);
            
            $isUSD = ($dbCurrency === 'USD' || str_contains($payMode, '(USD)'));
            $symbol = $isUSD ? '$ ' : 'Rs. ';
        @endphp

        <span class="ms-2 fw-bold text-dark">
            {{ $symbol }}{{ number_format($order->total_price, 2) }}
        </span>
    </div>
</td>

                            {{-- CUSTOMER DETAILS --}}
                            <td>
                                <div class="fw-bold fs-6">{{ $order->fname }} {{ $order->lname }}</div>
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-telephone me-1"></i> {{ $order->phone }}
                                </div>
                            </td>

                            {{-- ADDRESS --}}
                            <td>
                                <div class="text-secondary lh-sm" style="max-width: 250px;">
                                    <span class="fw-semibold text-dark">{{ $order->address1 }}</span><br>
                                    <span class="small">{{ $order->city }}, {{ $order->state }}</span>
                                </div>
                            </td>

                            {{-- STATUS LOGIC --}}
                            <td class="text-center">
                                @if($order->status == 3)
                                    <span class="badge rounded-pill bg-info text-dark px-3 py-2 fw-bold">
                                        <i class="bi bi-building me-1"></i> At Head Office
                                    </span>
                                @elseif($order->status == 4)
                                    <span class="badge rounded-pill badge-assigned px-3 py-2 fw-bold">
                                        <i class="bi bi-truck me-1"></i> With Rider
                                    </span>
                                    <div class="mt-1 small fw-bold text-primary">
                                        Assigned to: {{ $order->deliveryBoy?->name ?? 'Rider ID: '.$order->delivery_boy_id }}
                                    </div>
                                @endif
                            </td>

                            {{-- ACTION SECTION --}}
                            <td class="pe-4 text-center">
                                @if($order->status == 3)
                                    <form action="{{ route('admin.orders.assign', $order->id) }}" method="POST" class="mb-2">
                                        @csrf
                                        @method('PUT')
                                        <div class="input-group">
                                            <select name="rider_id" class="form-select form-select-sm rider-select shadow-none" required>
                                                <option value="" selected disabled>Select Rider</option>
                                                @foreach($deliveryPartners as $rider)
                                                    <option value="{{ $rider->id }}">{{ $rider->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-assign btn-success btn-sm px-3">
                                                Assign
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="mb-2">
                                        <span class="text-success small fw-bold"><i class="bi bi-check2-all me-1"></i> Ready for Delivery</span>
                                    </div>
                                @endif

                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-view-details btn-sm w-100 py-2 rounded-3 shadow-sm">
                                    <i class="bi bi-eye me-1"></i> View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-5 text-muted">
                                <i class="bi bi-box-seam d-block mb-3 fs-1 opacity-25"></i>
                                <h5 class="fw-light">No orders currently available for review.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($orders->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>
@endsection