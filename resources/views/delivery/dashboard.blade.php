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
                    <h6 class="text-muted mb-0">Pending Orders</h6>
                </div>
                <h3 class="fw-bold text-warning mb-0">0</h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-3 text-center">
                <div class="d-flex justify-content-center align-items-center mb-2">
                    <i class="bi bi-truck text-primary fs-4 me-2"></i>
                    <h6 class="text-muted mb-0">In Transit</h6>
                </div>
                <h3 class="fw-bold text-primary mb-0">0</h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 p-3 text-center">
                <div class="d-flex justify-content-center align-items-center mb-2">
                    <i class="bi bi-check-circle text-success fs-4 me-2"></i>
                    <h6 class="text-muted mb-0">Delivered Today</h6>
                </div>
                <h3 class="fw-bold text-success mb-0">0</h3>
            </div>
        </div>
    </div>

    {{-- SEARCH / FILTER BAR --}}
    <div class="mb-3">
        <input type="text" class="form-control" placeholder="🔍 Search by Order ID, Customer, or Location" disabled>
    </div>

    {{-- ASSIGNED ORDERS SECTION --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Assigned Orders</h5>
            <p class="text-muted mb-4">Once you start receiving deliveries, they will appear here.</p>

            {{-- Future backend data goes here --}}
            {{-- Example:
                @foreach($orders as $order)
                    <div class="order-card">{{ $order->id }}</div>
                @endforeach
            --}}

            {{-- EMPTY STATE MESSAGE --}}
            <div class="text-center text-muted mt-4">
                <i class="bi bi-clipboard-x fs-1"></i>
                <p class="mt-2">No assigned orders yet.</p>
            </div>
        </div>
    </div>
</div>
@endsection
