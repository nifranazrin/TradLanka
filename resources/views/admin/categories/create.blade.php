@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Add New Category</h4>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0 p-4">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Enter category name">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Parent Category (Optional)</label>
                    <select name="parent_id" class="form-select">
                        <option value="">None (Create as Main Category)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Select a category if you want to make this a sub-category (e.g., select "Beauty" to create "Hair Care").</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Short description (optional)">{{ old('description') }}</textarea>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Main Image (Thumbnail)</label>
                    <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event, 'mainImagePreview')">
                    <img id="mainImagePreview" class="mt-2 rounded shadow-sm" style="max-width: 120px; max-height: 120px; object-fit: cover; display:none;">
                    <small class="text-muted d-block mt-1">Used for icons/thumbnails.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Banner Image (Page Header)</label>
                    <input type="file" name="banner_image" class="form-control" accept="image/*" onchange="previewImage(event, 'bannerImagePreview')">
                    <img id="bannerImagePreview" class="mt-2 rounded shadow-sm" style="max-width: 100%; max-height: 120px; object-fit: cover; display:none;">
                    <small class="text-muted d-block mt-1">Used for the top banner on the category page.</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label d-block fw-bold">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="statusActive" value="1"
                           {{ old('status', '1') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="statusActive">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="statusInactive" value="0"
                           {{ old('status') === '0' ? 'checked' : '' }}>
                    <label class="form-check-label" for="statusInactive">Inactive</label>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-dark px-4">Save Category</button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary ms-2">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(event, previewId) {
        const preview = document.getElementById(previewId);
        if (event.target.files && event.target.files[0]) {
            preview.src = URL.createObjectURL(event.target.files[0]);
            preview.style.display = 'block';
        }
    }
</script>
@endsection