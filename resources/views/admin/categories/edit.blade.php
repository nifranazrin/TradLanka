@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Edit Category: <span class="text-primary">{{ $category->name }}</span></h4>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('admin.categories.update', $category->id) }}" 
                  method="POST" 
                  enctype="multipart/form-data">

                @csrf
                @method('PUT')

                <div class="row">
                    {{-- Category Name --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Category Name</label>
                        <input type="text" name="name" class="form-control" 
                               value="{{ old('name', $category->name) }}" required>
                    </div>

                    {{-- Parent Category (Added this so you can change hierarchy) --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Parent Category</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None (Main Category)</option>
                            @foreach($mainCategories as $cat)
                                <option value="{{ $cat->id }}" {{ $category->parent_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
                </div>

                <div class="row mb-4">
                    {{-- 1. Main Image Section --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Main Image (Thumbnail)</label>
                        <div class="d-flex align-items-start gap-3">
                            {{-- Show Existing or Placeholder --}}
                            @if ($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}" id="mainImagePreview" 
                                     class="rounded shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                            @else
                                <div id="mainImagePreview" class="bg-light rounded d-flex align-items-center justify-content-center text-muted" 
                                     style="width: 120px; height: 120px;">No Image</div>
                            @endif
                            
                            <div class="flex-grow-1">
                                <input type="file" name="image" class="form-control mb-1" accept="image/*" onchange="previewImage(event, 'mainImagePreview')">
                                <small class="text-muted">Upload to replace current thumbnail.</small>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Banner Image Section (NEW) --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Banner Image (Page Header)</label>
                        <div class="d-flex align-items-start gap-3">
                            {{-- Show Existing Banner or Placeholder --}}
                            @if ($category->banner_image)
                                <img src="{{ asset('storage/' . $category->banner_image) }}" id="bannerImagePreview"
                                     class="rounded shadow-sm" style="width: 200px; height: 120px; object-fit: cover;">
                            @else
                                <div id="bannerImagePreview" class="bg-light rounded d-flex align-items-center justify-content-center text-muted"
                                     style="width: 200px; height: 120px;">No Banner</div>
                            @endif

                            <div class="flex-grow-1">
                                <input type="file" name="banner_image" class="form-control mb-1" accept="image/*" onchange="previewImage(event, 'bannerImagePreview')">
                                <small class="text-muted">Upload to replace top banner.</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="mb-3">
                    <label class="form-label d-block fw-bold">Status</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="statusActive" value="1" 
                               {{ old('status', $category->status) == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="statusActive">Active</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="statusInactive" value="0" 
                               {{ old('status', $category->status) == '0' ? 'checked' : '' }}>
                        <label class="form-check-label" for="statusInactive">Inactive</label>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-dark px-4">Update Category</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(event, previewId) {
    const preview = document.getElementById(previewId);
    if (event.target.files && event.target.files[0]) {
        // If the preview element is a DIV (placeholder), replace it with an IMG tag
        if (preview.tagName === 'DIV') {
            const newImg = document.createElement('img');
            newImg.id = previewId;
            // Copy styles/classes
            newImg.className = "rounded shadow-sm"; 
            newImg.style.width = preview.style.width;
            newImg.style.height = preview.style.height;
            newImg.style.objectFit = 'cover';
            
            // Replace the div with the new img
            preview.parentNode.replaceChild(newImg, preview);
            
            // Set source
            newImg.src = URL.createObjectURL(event.target.files[0]);
        } else {
            // If it's already an image, just update the src
            preview.src = URL.createObjectURL(event.target.files[0]);
        }
    }
}
</script>
@endsection