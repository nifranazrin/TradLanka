@extends('layouts.seller')

@section('content')
<div class="container-fluid">
    @php
        $sellerName = session('staff_name');
    @endphp

    <h2 class="fw-bold mb-1">
        Welcome back, {{ $sellerName ?? 'Seller' }} 
    </h2>
    <h6 class="text-muted mb-4">Seller Dashboard</h6>

    {{-- Dashboard Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 p-3">
                <h6 class="text-muted">Total Products</h6>
                <h4 class="fw-bold">0</h4>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 p-3">
                <h6 class="text-muted">Orders Today</h6>
                <h4 class="fw-bold">0</h4>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 p-3">
                <h6 class="text-muted">Pending Deliveries</h6>
                <h4 class="fw-bold">0</h4>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 p-3">
                <h6 class="text-muted">Monthly Revenue</h6>
                <h4 class="fw-bold text-warning">Rs 0.00</h4>
            </div>
        </div>
    </div>

    {{-- Sales Overview Section --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Sales Overview</h5>
            <div class="p-4 text-center text-muted" style="background:#f7f7f7; border-radius:10px;">
                [ Placeholder for Sales Overview Graph ]
            </div>
        </div>
    </div>

    {{-- Recent Orders Placeholder --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Recent Orders</h5>
            <p class="text-muted mb-0">
                No recent orders available. Orders will appear here when customers make purchases.
            </p>
        </div>
    </div>
</div>
@endsection
