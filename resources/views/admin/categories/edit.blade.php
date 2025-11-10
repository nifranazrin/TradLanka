@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h4 class="fw-bold mb-3">Edit Category</h4>

    <div class="card shadow-sm border-0 p-4">
        <form action="{{ route('admin.categories.update', $category->id) }}" 
              method="POST" 
              enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-bold">Category Name</label>
                <input type="text" name="name" class="form-control" 
                       value="{{ old('name', $category->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Current Image</label><br>
                @if ($category->image)
                    <img src="{{ asset('storage/' . $category->image) }}" width="100" height="100" class="rounded mb-2">
                @else
                    <p class="text-muted">No image uploaded yet.</p>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Upload New Image (optional)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>

            <div class="mb-3">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="statusActive" value="1" 
                           {{ $category->status ? 'checked' : '' }}>
                    <label class="form-check-label" for="statusActive">Active</label>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="statusInactive" value="0" 
                           {{ !$category->status ? 'checked' : '' }}>
                    <label class="form-check-label" for="statusInactive">Inactive</label>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-dark">Update Category</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
