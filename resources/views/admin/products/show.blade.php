@extends('layouts.admin')
@section('content')
<div class="container">
    <h3 class="mb-4">Product Details</h3>

    <div class="card shadow-sm">
        <div class="row g-0">
            <div class="col-md-4">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded-start" alt="{{ $product->name }}">
                @else
                    <img src="https://via.placeholder.com/400x400?text=No+Image" class="img-fluid rounded-start" alt="No image">
                @endif
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h5 class="card-title">{{ $product->name }}</h5>
                    <p class="card-text text-muted">{{ $product->description }}</p>

                    <hr>
                    <p><strong>Category:</strong> {{ $product->category->name ?? 'N/A' }}</p>
                    <p><strong>Seller:</strong> {{ $product->seller->name ?? 'Unknown' }}</p>
                    <p><strong>Price:</strong> Rs {{ number_format($product->price, 2) }}</p>
                    <p><strong>Stock:</strong> {{ $product->stock }}</p>
                    <p><strong>Status:</strong>
                        @if ($product->status == 'approved')
                            <span class="badge bg-success">Approved</span>
                        @elseif ($product->status == 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </p>
                </div>

                <div class="card-footer bg-white border-top">
                    <form action="{{ route('admin.products.approve', $product->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i> Approve</button>
                    </form>
                    <form action="{{ route('admin.products.reject', $product->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-danger btn-sm"><i class="bi bi-x-circle"></i> Reject</button>
                    </form>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm float-end">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
