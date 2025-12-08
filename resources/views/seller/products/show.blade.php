@extends('layouts.seller')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
    
    // Helper function to get the correct URL
    $resolveUrl = function($p) {
        if (!$p) return asset('images/placeholder.png'); 
        $clean = preg_replace('/^public\//', '', $p);
        $clean = ltrim($clean, '/');
        // If it's already a full URL, return it
        if (preg_match('#^https?://#i', $clean)) {
            return $clean;
        }
        return Storage::url($clean);
    };
@endphp

<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">

            {{-- HEADER --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">{{ $product->name }}</h4>
                <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Products
                </a>
            </div>

            <div class="row">
                {{-- LEFT COLUMN: IMAGES --}}
                <div class="col-md-5">
                    {{-- 1. MAIN IMAGE --}}
                    <div class="border rounded p-2 mb-2" style="background: #f8f9fa;">
                        <img id="mainImage" 
                             src="{{ $resolveUrl($product->image) }}" 
                             class="img-fluid rounded" 
                             style="width: 100%; height: 350px; object-fit: contain; background: white;"
                             alt="Main Product Image">
                    </div>

                    {{-- 2. GALLERY THUMBNAILS --}}
                    @if($product->images && $product->images->count() > 0)
                        <div class="d-flex flex-wrap gap-2">
                            {{-- Main Image Thumb --}}
                            <img src="{{ $resolveUrl($product->image) }}" 
                                 width="60" height="60" 
                                 class="rounded border" 
                                 style="cursor: pointer; object-fit: cover;"
                                 onclick="changeImage(this.src)">

                            {{-- Gallery Images --}}
                            @foreach($product->images as $img)
                                <img src="{{ $resolveUrl($img->path) }}" 
                                     width="60" height="60" 
                                     class="rounded border" 
                                     style="cursor: pointer; object-fit: cover;" 
                                     onclick="changeImage(this.src)">
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small">No additional gallery images.</p>
                    @endif
                </div>

                {{-- RIGHT COLUMN: DETAILS --}}
                <div class="col-md-7">
                    <table class="table table-borderless align-middle">
                        {{-- Category --}}
                        <tr>
                            <th style="width: 150px;">Category:</th>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                        </tr>

                        {{-- Price --}}
                        <tr>
                            <th>Price:</th>
                            <td class="fs-5 fw-bold text-success">Rs. {{ number_format($product->price, 2) }}</td>
                        </tr>

                        {{-- Stock --}}
                        <tr>
                            <th>Stock:</th>
                            <td>{{ $product->stock }} items</td>
                        </tr>

                        {{-- Status (FIXED ALIGNMENT) --}}
                        <tr>
                            <th>Status:</th>
                            <td>
                                {{-- 1. Standard Approved --}}
                                @if($product->status === 'approved')
                                    <span class="badge bg-success">Approved</span>

                                {{-- 2. Re-Approved (Light Green) --}}
                                @elseif($product->status === 'reapproved')
                                    <span class="badge" style="background-color: #66bb6a;">Re-Approved</span>
                                
                                {{-- 3. Pending --}}
                                @elseif($product->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                    
                                {{-- 4. Re-Approval Pending --}}
                                @elseif($product->status === 'reapproval_pending') 
                                    <span class="badge bg-info text-dark">Re-Approval Pending</span>

                                {{-- 5. Rejected --}}
                                @elseif($product->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>

                                @else
                                    <span class="badge bg-secondary">Unknown: {{ $product->status }}</span>
                                @endif
                            </td>
                        </tr>

                        {{-- Date Added --}}
                        <tr>
                            <th>Date Added:</th>
                            <td>{{ $product->created_at->format('Y-m-d H:i') }}</td>
                        </tr>

                        {{-- Description --}}
                        <tr>
                            <th>Description:</th>
                            <td class="text-muted">{{ $product->description }}</td>
                        </tr>
                    </table>

                    {{-- Edit Button --}}
                    <div class="mt-4">
                        <a href="{{ route('seller.products.edit', $product->id) }}" class="btn btn-warning px-4">
                            <i class="bi bi-pencil"></i> Edit Product
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JAVASCRIPT TO HANDLE IMAGE SWAP --}}
<script>
    function changeImage(src) {
        document.getElementById('mainImage').src = src;
    }
</script>

@endsection