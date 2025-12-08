@extends('layouts.seller')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
    // Helper for clean URLs
    $resolveUrl = function($p) {
        if (!$p) return asset('images/placeholder.png');
        $clean = preg_replace('/^public\//', '', $p);
        $clean = ltrim($clean, '/');
        if (preg_match('#^https?://#i', $clean)) return $clean;
        return Storage::url($clean);
    };
@endphp

<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h4 class="mb-3 text-danger">Edit Product: {{ $product->name }}</h4>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                    </ul>
                </div>
            @endif

            {{-- MAIN EDIT FORM --}}
            <form action="{{ route('seller.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Basic Info --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Product Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Category</label>
                        <select name="category_id" class="form-control" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $category->id == old('category_id', $product->category_id) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Price (Rs.)</label>
                        <input type="number" name="price" step="0.01" class="form-control" value="{{ old('price', $product->price) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Stock</label>
                        <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Description</label>
                    <textarea name="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
                </div>

                <hr>

                {{-- FRONT IMAGE SECTION --}}
                <div class="mb-4">
                    <label class="fw-bold">Front Image (Main)</label>
                    <div class="d-flex align-items-end gap-3">
                        <div>
                            <img src="{{ $resolveUrl($product->image) }}" width="100" class="rounded border">
                            <div class="small text-muted text-center">Current</div>
                        </div>
                        <div class="flex-grow-1">
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Upload to replace the current front image.</small>
                        </div>
                    </div>
                </div>

                {{-- GALLERY IMAGES SECTION --}}
                <div class="mb-4">
                    <label class="fw-bold d-block">Additional Gallery Images</label>
                    
                    {{-- 1. SHOW EXISTING IMAGES --}}
                    @if($product->images && $product->images->count() > 0)
                        <div class="d-flex flex-wrap gap-3 mb-3 p-3 bg-light rounded border">
                            @foreach($product->images as $img)
                                <div class="text-center" style="width: 80px;">
                                    <img src="{{ $resolveUrl($img->path) }}" width="80" height="80" class="rounded border mb-1" style="object-fit:cover;">
                                    
                                    {{-- DELETE BUTTON (Requires a separate small form or ajax, but here we use a simple form attribute) --}}
                                    {{-- NOTE: We cannot nest forms. We will use a separate mechanism or a link for deletion --}}
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-sm py-0 w-100" 
                                            onclick="deleteImage({{ $img->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No additional images yet.</p>
                    @endif

                    {{-- 2. UPLOAD NEW IMAGES --}}
                    <label class="small fw-bold">Add New Images:</label>
                    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                    <small class="text-muted">Select multiple files to add them to the gallery.</small>
                </div>

                <div class="text-end">
                    <a href="{{ route('seller.products.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-danger px-4">Save Changes</button>
                </div>
            </form>
            
            {{-- HIDDEN FORM FOR DELETING IMAGES --}}
            <form id="deleteImageForm" action="" method="POST" style="display:none;">
                @csrf
                @method('DELETE') </form>

        </div>
    </div>
</div>

<script>
    function deleteImage(id) {
        if(confirm('Are you sure you want to delete this image?')) {
            // Update this URL structure to match your route: Route::post('/product/image/{id}/delete', ...)
            let form = document.getElementById('deleteImageForm');
            form.action = '/seller/products/image/' + id + '/delete'; 
            form.submit();
        }
    }
</script>

@endsection