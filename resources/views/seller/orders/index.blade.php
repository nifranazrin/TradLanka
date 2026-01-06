@extends('layouts.seller')

@section('content')

{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .order-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 6px 20px rgba(0,0,0,0.06);
        overflow: hidden;
    }

    .custom-table thead th {
        background-color: #5b2c2c;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 16px;
        border: none;
    }

    .custom-table td {
        padding: 16px;
        vertical-align: top;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
        color: #374151;
    }

    .badge-custom {
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-block;
    }

    .badge-new { background: #fef3c7; color: #92400e; }
    .badge-received { background: #e0f2fe; color: #075985; }
    .badge-packed { background: #ede9fe; color: #5b21b6; }
    .badge-delivered { background: #dcfce7; color: #166534; }

    .btn-action {
        width: 100%;
        border-radius: 6px;
        padding: 8px 12px;
        font-size: 0.8rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        margin-bottom: 8px;
    }

    .btn-receive { background: #2563eb; color: #fff; }
    .btn-pack { background: #7c3aed; color: #fff; }
    .btn-handover { background: #16a34a; color: #fff; }

    .contact-link {
        color: #2563eb;
        text-decoration: underline;
    }
</style>

                <div class="container-fluid px-4 py-5">

                    <div class="mb-4">
                        <h2 class="h3 fw-bold text-dark mb-1">Order Management</h2>

                        <p class="text-muted small">
                            Receive orders, review items, pack and hand over to Head Office for delivery.
                        </p>
                    </div>

                        <div class="row mb-4 justify-content-center">
                    <div class="col-md-6"> {{-- Centered and optimized width --}}
                        <form action="{{ route('seller.orders.index') }}" method="GET">
                            <div class="input-group">
                                {{-- Input field matching product search style --}}
                                <input type="text" name="search" class="form-control" 
                                    placeholder="Search Tracking #, Customer, or City..." 
                                    value="{{ request('search') }}"
                                    style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">
                                
                                {{-- Maroon Button matching your reference image --}}
                                <button class="btn" type="submit" 
                                        style="background-color: #5b2c2c; color: white; border-top-right-radius: 8px; border-bottom-right-radius: 8px; padding: 0 20px;">
                                    <i class="bi bi-search"></i>
                                </button>

                                {{-- Clear Search Button --}}
                                @if(request('search'))
                                    <a href="{{ route('seller.orders.index') }}" class="btn btn-outline-secondary ms-2 rounded-circle">
                                        <i class="bi bi-x"></i>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                    {{-- SweetAlert: General success --}}
                    @if(session('success'))
                        <script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: "{{ session('success') }}",
                                background: '#381313',
                                color: '#facc15',
                                confirmButtonColor: '#facc15'
                            });
                        </script>
                    @endif

                    {{-- SweetAlert: Handover success --}}
                    @if(session('handover_success'))
                        <script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Handed Over',
                                text: "{{ session('handover_success') }}",
                                background: '#381313',
                                color: '#facc15',
                                confirmButtonColor: '#facc15'
                            });
                        </script>
                    @endif

                    <div class="order-card">
                        <div class="table-responsive">
                            <table class="table custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th width="25%">Order</th>
                                        <th width="20%">Customer</th>
                                        <th width="25%">Delivery Address</th>
                                        <th width="15%" class="text-center">Status</th>
                                        <th width="15%" class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                @foreach($orders as $order)
                                    @php $status = (int) $order->status; 
                                    $isUSD = str_contains($order->payment_mode, 'USD');
                                    @endphp

                                <tr style="{{ $isUSD ? 'background-color: #f0f9ff; border-left: 5px solid #0284c7;' : '' }}">

                                    <tr>
                                        {{-- ORDER --}}
                                            <td>
                                                <strong>{{ $order->tracking_no }}</strong>

                                                {{-- NEW: International Badge --}}
                                                @if($isUSD)
                                                    <span class="badge bg-primary text-white ms-1" style="font-size: 0.65rem; vertical-align: middle;">
                                                        <i class="bi bi-globe me-1"></i> INTERNATIONAL
                                                    </span>
                                                @endif
                                                
                                                {{-- FIX 2: Corrected Time for Sri Lanka --}}
                                            <div class="small text-muted">
                                                {{ $order->created_at->timezone('Asia/Colombo')->format('d M Y, h:i A') }}
                                            </div>

                                                <div class="mt-2">
                                                    @if(str_contains(strtolower($order->payment_mode), 'cod'))
                                                        <span class="badge bg-warning text-dark fw-bold">COD</span>
                                                    @else
                                                        <span class="badge bg-success fw-bold">PAID</span>
                                                    @endif

                                                     <strong class="ms-2 {{ $isUSD ? 'text-primary' : '' }}">
                                                        {{-- DYNAMIC CURRENCY LOGIC --}}
                                                        @if($isUSD)
                                                            ${{ number_format($order->total_price, 2) }}
                                                        @else
                                                            Rs. {{ number_format($order->total_price, 2) }}
                                                        @endif
                                                    </strong>
                                                 </div>
                                            </td>
                        {{-- CUSTOMER --}}
                        <td>
                            <strong>{{ $order->fname }} {{ $order->lname }}</strong>
                            <div class="small mt-1">
                                <a href="tel:{{ $order->phone }}" class="contact-link d-block">
                                    {{ $order->phone }}
                                </a>
                                <a href="mailto:{{ $order->email }}" class="contact-link">
                                    {{ $order->email }}
                                </a>
                            </div>
                        </td>

                        {{-- ADDRESS --}}
                        <td>
                            <div class="small bg-light p-2 rounded border">
                                {{ $order->address1 }}<br>
                                {{ $order->city }}, {{ $order->state }}<br>
                                ZIP: {{ $order->zipcode }}
                            </div>
                        </td>

                              {{-- STATUS COLUMN --}}
                            <td class="text-center">
                                @if($status === 0)
                                    <span class="badge-custom badge-new">New Order</span>
                                @elseif($status === 1)
                                    <span class="badge-custom badge-received">Received – Waiting to Pack</span>
                                @elseif($status === 2)
                                    <span class="badge-custom badge-packed">Packed</span>
                                @elseif($status === 3)
                                    <span class="badge-custom badge-delivered">At Head Office</span>
                                @elseif($status === 4)
                                    <span class="badge-custom" style="background: #e0f2fe; color: #075985;">
                                        <i class="bi bi-truck me-1"></i> Handed to Delivery
                                    </span>
                                @elseif($status === 5)
                                    <span class="badge-custom" style="background: #dcfce7; color: #166534;">
                                        <i class="bi bi-check-circle me-1"></i> Delivered
                                    </span>
                                @elseif($status === 10)
                                    <span class="badge-custom" style="background: #ede9fe; color: #5b21b6;">
                                        <i class="bi bi-airplane me-1"></i> Arrived in Destination Country
                                    </span>
                                @elseif($status === 6)
                                    <span class="badge-custom" style="background: #b82222; color: #f1e8e4;">Order Cancelled</span>
                                @elseif($status === 7)
                                    <span class="badge-custom" style="background: #fef3c7; color: #940606; border: 1px solid #f59e0b;">
                                        ⚠️ Cancellation Requested
                                    </span>
                                @elseif($status === 8)
                                    <span class="badge-custom" style="background: #f0fdf4; color: #166534; border: 1px solid #22c55e;">
                                        ✅ Approved for Refund
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Status: {{ $status }}</span>
                                @endif
                            </td>


                          {{-- ACTION COLUMN --}}
                                <td class="text-center">
                                    @php 
                                        $rawStatus = (string) $order->status; 
                                    @endphp

                                    {{-- 1. Seller Fulfillment Flow (Keep active buttons for 0, 1, 2) --}}
                                    @if($rawStatus === '0')
                                        <form method="POST" action="{{ route('seller.orders.update', $order->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="1">
                                            <button class="btn-action btn-receive">Receive Order</button>
                                        </form>
                                    @elseif($rawStatus === '1')
                                        <form method="POST" action="{{ route('seller.orders.update', $order->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="2">
                                            <button class="btn-action btn-pack">Pack Order</button>
                                        </form>
                                    @elseif($rawStatus === '2')
                                        <form method="POST" action="{{ route('seller.orders.update', $order->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="3">
                                            <button class="btn-action btn-handover">Hand Over to Head Office</button>
                                        </form>
                                    @endif

                                    {{-- 2. Cancellation Flow (Approve Button for status 7) --}}
                                    @if($rawStatus === '7')
                                        <form method="POST" action="{{ route('seller.orders.approve_cancel', $order->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn-action fw-bold shadow-sm" style="background: #dc2626; color: #fff;">
                                                Approve Cancellation
                                            </button>
                                            <p class="text-danger mt-1 fw-bold" style="font-size: 0.65rem; line-height: 1;">
                                                *Sends to Head Office for Refund
                                            </p>
                                        </form>
                                    @endif


                                    {{-- Always show View Details button --}}
                                    <a href="{{ route('seller.orders.show', $order->id) }}"
                                    class="btn btn-outline-secondary btn-sm mt-1 w-100">
                                    <i class="bi bi-eye me-1"></i> View Details
                                    </a>
                                </td>

                                                        </tr>
                @endforeach
                </tbody>
            </table>
        </div>
                {{-- This generates the < 1 2 3 > page links you marked --}}
                <div class="p-3 border-top bg-light d-flex justify-content-center">
                    {{ $orders->links() }}
                </div>
    </div>
</div>

@endsection