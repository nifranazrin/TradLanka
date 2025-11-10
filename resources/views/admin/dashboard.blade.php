@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <h2 class="fw-bold mb-1">Welcome back!</h2>
    <h6 class="text-muted mb-4">Admin Dashboard</h6>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card shadow-sm border-0 text-center p-3">
                <h6 class="text-muted">Total Users</h6>
                <h4 class="fw-bold">0</h4>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm border-0 text-center p-3">
                <h6 class="text-muted">Active Sellers</h6>
                <h4 class="fw-bold">0</h4>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm border-0 text-center p-3">
                <h6 class="text-muted">Pending Approvals</h6>
                <h4 class="fw-bold">0</h4>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm border-0 text-center p-3">
                <h6 class="text-muted">Total Orders</h6>
                <h4 class="fw-bold">0</h4>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm border-0 text-center p-3">
                <h6 class="text-muted">Revenue This Month</h6>
                <h4 class="fw-bold">Rs 0.00</h4>
            </div>
        </div>
    </div>

    {{-- Sales Overview Section --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Sales Overview</h5>
            <div class="p-4 text-center text-muted" style="background:#f7f7f7; border-radius:10px;">
                [ Placeholder for Sales Chart or Graph ]
            </div>
        </div>
    </div>

    {{-- Recent Activity Section --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Recent Activity</h5>
            <p class="text-muted mb-0">
                No recent activity yet. System updates and approvals will appear here.
            </p>
        </div>
    </div>
</div>
@endsection

