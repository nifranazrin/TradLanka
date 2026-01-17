@extends('layouts.admin')

@section('content')

<style>
    /* Consistent Maroon Styling */
    .maroon-header {
        background-color: #800000 !important;
    }

    .maroon-header th {
        background-color: #3b0b0b !important;
        color: white !important;
        padding: 15px !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        border: none !important;
    }

    .custom-shadow-table {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important; 
        border-radius: 12px;
        overflow: hidden;
        background: white;
        border: 1px solid #f0f0f0;
    }

    .text-maroon {
        color: #800000;
    }
</style>

<div class="container py-4">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark">
            <i class="bi bi-person-lines-fill text-maroon"></i> Customer Purchase Profile
        </h4>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-4">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="row">
        {{-- Left Side: Customer Info Card --}}
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-4 text-center">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-person text-secondary h1 mb-0"></i>
                    </div>
                    {{-- Handles Google (name) or Manual (fname/lname) registration --}}
                    <h5 class="fw-bold mb-1">{{ $customer->name ?? ($customer->fname . ' ' . $customer->lname) }}</h5>
                    <p class="text-muted small mb-3">{{ $customer->email }}</p>
                    <hr>
                    <div class="text-start">
                        <p class="mb-1"><strong>Location:</strong> {{ $customer->country ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Phone:</strong> {{ $customer->phone ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Member Since:</strong> {{ $customer->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Side: Order History Table --}}
        <div class="col-md-8">
            <div class="custom-shadow-table">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="maroon-header">
                            <tr>
                                <th class="ps-4">Order ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th class="text-end pe-4">Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->orders as $order)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-primary">#{{ $order->tracking_no }}</span>
                                </td>
                                <td>{{ $order->created_at->format('d M, Y') }}</td>
                                <td>
                                    {{-- Loop through items in this specific order --}}
                                    @foreach($order->items as $item)
                                        <div class="small text-muted">• {{ $item->product->name ?? 'Product' }} (x{{ $item->qty }})</div>
                                    @endforeach
                                </td>
                                <td class="text-end pe-4">
                                    <span class="fw-bold text-success">
                                        Rs. {{ number_format($order->total_price, 2) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center p-5 text-muted">
                                    <i class="bi bi-cart-x display-4 opacity-25"></i>
                                    <p class="mt-2">No purchase history found for this customer.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection