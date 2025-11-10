@extends('layouts.seller')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h4 class="mb-3 text-danger">{{ $product->name }}</h4>

            <div class="row">
                <div class="col-md-4">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded" alt="{{ $product->name }}">
                    @else
                        <p class="text-muted">No image available.</p>
                    @endif
                </div>

                <div class="col-md-8">
                    <p><strong>Category:</strong> {{ $product->category->name ?? 'N/A' }}</p>
                    <p><strong>Description:</strong> {{ $product->description ?? 'No description' }}</p>
                    <p><strong>Price:</strong> Rs. {{ number_format($product->price, 2) }}</p>
                    <p><strong>Stock:</strong> {{ $product->stock }}</p>
                    <p><strong>Status:</strong> 
                        @if($product->status === 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($product->status === 'pending')
                            <span class="badge bg-warning text-dark">Pending Approval</span>
                        @elseif($product->status === 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Products
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
