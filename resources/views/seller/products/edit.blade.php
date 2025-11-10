@extends('layouts.seller')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h4 class="mb-3 text-danger">Edit Product</h4>

            <form action="{{ route('seller.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-bold">Product Name</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Category</label>
                    <select name="category_id" class="form-control" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $category->id == $product->category_id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Price</label>
                    <input type="number" name="price" step="0.01" value="{{ old('price', $product->price) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Stock</label>
                    <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea name="description" rows="4" class="form-control">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Product Image</label><br>
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" width="100" class="rounded mb-2">
                    @endif
                    <input type="file" name="image" class="form-control">
                    <small class="text-muted">Leave empty to keep current image.</small>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-danger px-4">Save Changes</button>
                    <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
