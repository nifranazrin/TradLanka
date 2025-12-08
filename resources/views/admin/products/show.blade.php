@extends('layouts.admin')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;

    // Helper to get clean URL
    $resolveUrl = function($p) {
        if (!$p) return 'https://via.placeholder.com/400x400?text=No+Image';
        $clean = preg_replace('/^public\//', '', $p);
        $clean = ltrim($clean, '/');
        if (preg_match('#^https?://#i', $clean)) {
            return $clean;
        }
        return Storage::url($clean);
    };
@endphp

{{-- Load SweetAlert2 Library --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <div class="mb-4">
        <h3>Product Details</h3>
    </div>

    <div class="card shadow-sm">
        <div class="row g-0">
            {{-- LEFT COLUMN: IMAGES --}}
            <div class="col-md-4 p-3 text-center border-end">
                {{-- Main Image --}}
                <div class="mb-3">
                    <img id="mainAdminImage"
                         src="{{ $resolveUrl($product->image) }}"
                         class="img-fluid rounded"
                         style="max-height: 350px; object-fit: contain;"
                         alt="{{ e($product->name) }}"
                         loading="lazy">
                </div>

                {{-- Gallery Thumbs --}}
                @if($product->images && $product->images->count() > 0)
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3 p-2 bg-light rounded">
                        <img src="{{ $resolveUrl($product->image) }}"
                             width="60" height="60"
                             class="rounded border"
                             style="cursor: pointer; object-fit: cover;"
                             onclick="document.getElementById('mainAdminImage').src = this.src"
                             title="Main Image" loading="lazy">

                        @foreach($product->images as $img)
                            <img src="{{ $resolveUrl($img->path) }}"
                                 width="60" height="60"
                                 class="rounded border"
                                 style="cursor: pointer; object-fit: cover;"
                                 onclick="document.getElementById('mainAdminImage').src = this.src"
                                 loading="lazy">
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- RIGHT COLUMN: DETAILS --}}
            <div class="col-md-8">
                <div class="card-body h-100 d-flex flex-column">
                    <h5 class="card-title display-6 fs-4">{{ $product->name }}</h5>
                    <p class="card-text text-muted">{{ $product->description }}</p>

                    <hr>
                    <div class="flex-grow-1">
                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Category:</div>
                            <div class="col-sm-8">{{ $product->category->name ?? 'N/A' }}</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Seller:</div>
                            <div class="col-sm-8">{{ $product->seller->name ?? 'Unknown' }}</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Price:</div>
                            <div class="col-sm-8 text-success fw-bold">Rs {{ number_format($product->price, 2) }}</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Stock:</div>
                            <div class="col-sm-8">{{ $product->stock }} items</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-sm-4 fw-bold">Status:</div>
                            <div class="col-sm-8">
                                @if($product->status == 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($product->status == 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending Approval</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ACTION BUTTONS SECTION --}}
                    <div class="mt-4 pt-3 border-top bg-light p-3 rounded">
                        <h6 class="fw-bold mb-3">Admin Actions</h6>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            
                            {{-- 1. Back Button --}}
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>

                            {{-- 2. Approve Form --}}
                            {{-- NOTICE: I removed @method('PUT') below --}}
                            <form id="approve-form-{{ $product->id }}" action="{{ route('admin.products.approve', $product->id) }}" method="POST">
                                @csrf
                                <button type="button" class="btn btn-success text-white" 
                                        onclick="confirmAction('approve-form-{{ $product->id }}', 'Approve Product?', 'Are you sure you want to approve this product?', 'success', 'Yes, Approve!')">
                                    <i class="fas fa-check-circle"></i> Approve
                                </button>
                            </form>

                            {{-- 3. Reject Form --}}
                            {{-- NOTICE: I removed @method('PUT') below --}}
                            <form id="reject-form-{{ $product->id }}" action="{{ route('admin.products.reject', $product->id) }}" method="POST">
                                @csrf
                                <button type="button" class="btn btn-danger" 
                                        onclick="confirmAction('reject-form-{{ $product->id }}', 'Reject Product?', 'Are you sure you want to reject this product?', 'warning', 'Yes, Reject!')">
                                    <i class="fas fa-times-circle"></i> Reject
                                </button>
                            </form>
                            
                        </div>
                    </div>
                    {{-- End Action Buttons --}}

                </div>
            </div>
        </div>
    </div>
</div>

{{-- JAVASCRIPT FOR SWEETALERT --}}
<script>
    function confirmAction(formId, title, text, icon, confirmButtonText) {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: confirmButtonText,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user clicks "Yes", submit the form programmatically
                document.getElementById(formId).submit();
            }
        });
    }
</script>

@endsection