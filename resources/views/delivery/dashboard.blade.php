@extends('layouts.delivery')

@section('content')
<div class="container-fluid">
    {{-- HEADER --}}
    <h2 class="fw-bold mb-1">Welcome back!</h2>
    <h6 class="text-muted mb-4">Delivery Person Dashboard</h6>

    {{-- DASHBOARD OVERVIEW CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-3 text-center">
                <div class="d-flex justify-content-center align-items-center mb-2">
                    <i class="bi bi-hourglass-split text-warning fs-4 me-2"></i>
                    <h6 class="text-muted mb-0">Assigned / Pending</h6>
                </div>
                <h3 class="fw-bold text-warning mb-0">{{ $pendingCount }}</h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-3 text-center">
                <div class="d-flex justify-content-center align-items-center mb-2">
                    <i class="bi bi-check-circle text-success fs-4 me-2"></i>
                    <h6 class="text-muted mb-0">Delivered Today</h6>
                </div>
                <h3 class="fw-bold text-success mb-0">{{ $deliveredTodayCount }}</h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-3 text-center">
                <div class="d-flex justify-content-center align-items-center mb-2">
                    <i class="bi bi-x-circle text-danger fs-4 me-2"></i>
                    <h6 class="text-muted mb-0">Failed / Not Received</h6>
                </div>
                <h3 class="fw-bold text-danger mb-0">{{ $failedCount }}</h3>
            </div>
        </div>
    </div>

    {{-- SEARCH BAR --}}
    <div class="mb-3">
        <form action="{{ route('delivery.my-deliveries') }}" method="GET">
            <input type="text" name="search" class="form-control" placeholder="🔍 Search by Order ID, Customer, or Location">
        </form>
    </div>

    {{-- ACTIVE DELIVERIES TABLE --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Recent Active Orders</h5>

            @if($activeDeliveries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tracking No</th>
                                <th>Customer</th>
                                <th>Location</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeDeliveries as $order)
                                <tr>
                                    <td class="fw-bold">#{{ $order->tracking_no }}</td>
                                    <td>{{ $order->fname }} {{ $order->lname }}</td>
                                    <td>{{ $order->city }}</td>
                                    <td class="fw-bold">
                                        {{-- ✅ CORRECTED: Use order currency or session preference --}}
                                        @php 
                                            $symbol = ($order->currency == 'USD' || session('currency') == 'USD') ? '$' : 'Rs.'; 
                                        @endphp
                                        {{ $symbol }} {{ number_format($order->total_price, 2) }}
                                    </td>
                                    <td>
                                        @if($order->status == 4)
                                            <span class="badge bg-primary">In Transit</span>
                                        @elseif($order->status == 6)
                                            <span class="badge bg-danger">Not Received</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('delivery.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted mt-4">
                    <i class="bi bi-clipboard-x fs-1"></i>
                    <p class="mt-2">No active orders assigned at the moment.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection