@extends('layouts.admin')
@section('content')
<div class="container-fluid px-4 py-5">
    <h2 class="fw-bold text-dark mb-4">Sales & Revenue Analysis</h2>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <small class="text-muted fw-bold">LOCAL REVENUE (LKR)</small>
                <h3 class="fw-bold text-success mt-1">Rs. {{ number_format($totalLKR, 2) }}</h3>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <small class="text-muted fw-bold">INTL REVENUE (USD)</small>
                <h3 class="fw-bold text-primary mt-1">$ {{ number_format($totalUSD, 2) }}</h3>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm p-3 bg-dark text-white">
                <small class="opacity-75 fw-bold">STRIPE PAYMENTS</small>
                <h3 class="fw-bold mt-1">Total Verified</h3>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Recent Completed Transactions</h5>
        </div>
        <div class="table-responsive">
            <table class="table align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Method</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders->take(10) as $order)
                    <tr>
                        <td>{{ $order->created_at->format('d M, Y') }}</td>
                        <td class="fw-bold">#{{ $order->tracking_no }}</td>
                        <td>{{ $order->fname }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $order->payment_mode }}</span></td>
                        <td class="fw-bold text-dark">
                            {{ $order->currency == 'USD' ? '$' : 'Rs.' }} {{ number_format($order->total_price, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection