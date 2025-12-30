@extends('layouts.admin')

@section('content')

<style>
    /* Unified Search Bar Styling */
    .search-wrapper {
        max-width: 500px;
        margin-bottom: 25px;
    }

    .unified-search-group {
        border: 2px solid #e0e0e0;
        border-radius: 12px !important;
        overflow: hidden;
        background: #fff;
        transition: all 0.3s ease;
    }

    .unified-search-group:focus-within {
        border-color: #800000 !important; /* Maroon Focus */
        box-shadow: 0 0 0 4px rgba(128, 0, 0, 0.1);
    }

    #orderSearch {
        border: none !important;
        box-shadow: none !important;
        padding: 12px 15px;
    }

    /* Shaped Table Container */
    .custom-shadow-table {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        border-radius: 12px !important;
        overflow: visible !important;
        background: white;
        border: 1px solid #f0f0f0;
    }

    /* Maroon Header */
    .maroon-header {
        background-color: #5b2c2c !important; 
    }

    .maroon-header th {
        background-color: #5b2c2c !important;
        color: #ffffff !important;
        padding: 18px !important;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none !important;
    }

    /* Row Highlights */
    .international-highlight {
        background-color: #f0f7ff !important; 
        border-left: 5px solid #0d6efd !important;
    }

    .tracking-id {
        font-size: 1.1rem;
        color: #0d6efd;
    }

    td {
        position: relative;
    }
</style>

<div class="container-fluid px-4 py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Review Orders & Delivery</h2>
            <p class="text-muted small">Orders managed by Admin after Head Office handover. Track rider dispatch and finalize delivery failures.</p>
        </div>
    </div>

    <div class="search-wrapper mb-4">
        <div class="input-group unified-search-group shadow-sm">
            <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" id="orderSearch" class="form-control" placeholder="Search by Tracking ID, Customer, or Location (City)...">
        </div>
    </div>

    <div class="custom-shadow-table">
        <div class="table-responsive">
            <table class="table align-middle custom-table mb-0" id="orderTable" style="min-width: 1000px;">
                <thead>
                    <tr class="maroon-header">
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
                            $payMode = strtoupper(trim($order->payment_mode));
                            $dbCurrency = strtoupper(trim($order->currency));
                            
                            $isActuallyCod = str_contains($payMode, 'COD');
                            $isStripe = !$isActuallyCod;

                            $isActuallyUSD = ($dbCurrency === 'USD' || str_contains($payMode, '(USD)'));
                            $symbol = $isActuallyUSD ? '$ ' : 'Rs. ';
                        @endphp

                        <tr class="border-bottom {{ $isActuallyUSD ? 'international-highlight' : '' }}">
                            {{-- 1. ORDER DETAILS --}}
                            <td class="ps-4 py-4">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="fw-bold tracking-id">{{ $order->tracking_no }}</span>
                                    @if($isActuallyUSD)
                                        <span class="badge bg-primary ms-2" style="font-size: 0.65rem;">
                                            <i class="bi bi-globe me-1"></i>INTERNATIONAL (USD)
                                        </span>
                                    @endif
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $order->created_at->format('d M, Y | h:i A') }}
                                </div>
                                <div class="mt-2 d-flex align-items-center">
                                    @if($isActuallyCod)
                                        <span class="badge bg-warning text-dark fw-bold px-3 py-1">COD</span>
                                    @else
                                        <span class="badge bg-success fw-bold px-3 py-1 text-white">PAID</span>
                                    @endif
                                    <span class="ms-2 fw-bold text-dark">
                                        {{ $symbol }}{{ number_format($order->total_price, 2) }}
                                    </span>
                                </div>
                            </td>

                            {{-- 2. CUSTOMER --}}
                            <td>
                                <div class="fw-bold fs-6">{{ $order->fname }} {{ $order->lname }}</div>
                                <div class="text-muted small"><i class="bi bi-telephone"></i> {{ $order->phone }}</div>
                            </td>

                            {{-- 3. ADDRESS --}}
                            <td>
                                <div class="text-secondary lh-sm" style="max-width: 250px;">
                                    <span class="fw-semibold text-dark">{{ $order->address1 }}</span><br>
                                    <span class="small">{{ $order->city }}, {{ $order->state }}</span>
                                </div>
                            </td>
                                                {{-- Current Status Column in Admin Index --}}
                                            <td class="text-center">
                                                @if($order->status == 3)
                                                    <span class="badge rounded-pill bg-info text-dark px-3 py-2 fw-bold">At Head Office</span>
                                                @elseif($order->status == 4)
                                                    <span class="badge rounded-pill bg-primary px-3 py-2 fw-bold">With Rider</span>
                                                @elseif($order->status == 5)
                                                    <span class="badge rounded-pill bg-success text-white px-3 py-2 fw-bold">
                                                        <i class="bi bi-check-circle me-1"></i> Delivered
                                                    </span>
                                                @elseif($order->status == 8)
                                                    <span class="badge rounded-pill bg-warning text-dark px-3 py-2 fw-bold">Refund Requested</span>
                                                @elseif($order->status == 9)
                                                    {{-- Rider reported failure, but Admin hasn't confirmed yet --}}
                                                    <span class="badge rounded-pill bg-dark text-white px-3 py-2 fw-bold">
                                                        <i class="bi bi-exclamation-triangle me-1"></i> Delivery Failed
                                                    </span>
                                                    @if($order->cancel_reason)
                                                        <div class="small text-danger mt-1 fw-bold" style="font-size: 0.7rem;">
                                                            Reason: {{ $order->cancel_reason }}
                                                        </div>
                                                    @endif
                                                @elseif($order->status == 6)
                                                    {{-- Finalized state after Admin clicks "Confirm" --}}
                                                    @if($order->cancel_reason)
                                                        <span class="badge rounded-pill bg-secondary text-white px-3 py-2 fw-bold">
                                                            <i class="bi bi-truck-flatbed me-1"></i> Delivery Cancelled
                                                        </span>
                                                        <div class="small text-muted mt-1" style="font-size: 0.7rem;">
                                                            Reason: {{ $order->cancel_reason }}
                                                        </div>
                                                    @else
                                                        <span class="badge rounded-pill bg-danger text-white px-3 py-2 fw-bold">
                                                            <i class="bi bi-cash-stack me-1"></i> Order Refunded
                                                        </span>
                                                    @endif
                                                @endif
                                            </td>

                            {{-- 5. DISPATCH ACTIONS --}}
                            <td class="pe-4 text-center">
                                @if($order->status == 3)
                                    {{-- Assignment Form for Head Office --}}
                                    <form action="{{ route('admin.orders.assign', $order->id) }}" method="POST" class="mb-2">
                                        @csrf @method('PUT')
                                        <div class="input-group input-group-sm">
                                            <select name="rider_id" class="form-select" required>
                                                <option value="" selected disabled>Rider</option>
                                                @foreach($deliveryPartners as $rider)
                                                    <option value="{{ $rider->id }}">{{ $rider->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-success">Assign</button>
                                        </div>
                                    </form>
                                @elseif($order->status == 8 || $order->status == 9)
                                    {{-- Confirmation for Refund or Delivery Failure --}}
                                    <form id="refund-form-{{ $order->id }}" action="{{ route('admin.orders.finalize_refund', $order->id) }}" method="POST" class="mb-2">
                                        @csrf @method('PUT')
                                        <button type="button" 
                                                onclick="confirmAdminAction({{ $order->id }}, '{{ $payMode }}')" 
                                                class="btn {{ $isStripe ? 'btn-danger' : 'btn-secondary' }} btn-sm w-100 py-2 fw-bold">
                                            <i class="bi {{ $isStripe ? 'bi-cash-stack' : 'bi-x-circle' }}"></i> 
                                            {{ ($order->status == 9 || $isActuallyCod) ? 'Confirm Cancellation' : 'Finalize Refund' }}
                                        </button>
                                    </form>
                                    @if($order->cancel_reason)
                                        <div class="small text-danger fw-bold mt-1">Reason: {{ $order->cancel_reason }}</div>
                                    @endif
                                @elseif($order->status == 5)
                                    <span class="text-success small fw-bold d-block mb-2">Completion Verified</span>
                                @elseif($order->status == 6)
                                    <span class="text-muted small fw-bold d-block mb-2">Order Closed</span>
                                @else
                                    <span class="text-primary small fw-bold d-block mb-2">In Transit</span>
                                @endif

                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-secondary btn-sm w-100 py-2 mt-1">
                                    <i class="bi bi-eye"></i> View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center p-5 text-muted">No orders managed at Head Office found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- JS SCRIPTS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/**
 * ✅ Handles both Refund (Stripe) and Cancellation (COD/Failed)
 */
function confirmAdminAction(orderId, paymentMode) {
    const isCod = paymentMode.toUpperCase().includes('COD');
    
    Swal.fire({
        title: isCod ? "Confirm Cancellation?" : "Finalize Refund?",
        text: isCod 
            ? "This will cancel the order and restore product stock to the inventory." 
            : "This will finalize the Stripe refund and restore stock.",
        icon: isCod ? "info" : "warning",
        showCancelButton: true,
        confirmButtonColor: isCod ? "#6c757d" : "#dc2626",
        cancelButtonColor: "#5b2c2c",
        confirmButtonText: "Yes, Proceed",
        cancelButtonText: "No, Keep Pending"
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('refund-form-' + orderId);
            if (form) { form.submit(); }
        }
    });
}


document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('orderSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#orderTable tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? "" : "none";
            });
        });
    }
});
</script>

@endsection