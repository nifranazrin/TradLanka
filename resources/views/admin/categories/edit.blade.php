@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">
            <i class="bi bi-pencil-square" style="color: #800000;"></i> 
            Edit Category: <span style="color: #800000;">{{ $category->name }}</span>
        </h4>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
        {{-- Maroon Header Decoration --}}
        <div style="height: 5px; background-color: #800000;"></div>
        
        <div class="card-body p-4">
            {{-- Added id="editCategoryForm" below --}}
            <form id="editCategoryForm" 
                action="{{ route('admin.categories.update', $category->id) }}" 
                method="POST" 
                enctype="multipart/form-data">

                @csrf
                @method('PUT')

                <div class="row">
                    {{-- Category Name --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-dark">Category Name</label>
                        {{-- 2. Add id="categoryName" to this input --}}
                        <input type="text" id="categoryName" name="name" class="form-control" 
                            style="border-radius: 8px;"
                            value="{{ old('name', $category->name) }}" required>
                    </div>

                    {{-- Category Type (Parent) --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-dark">Category Type (Parent)</label>
                        <select name="parent_id" class="form-select" style="border-radius: 8px;">
                            <option value="">None (Main Category)</option>
                            @foreach($mainCategories as $cat)
                                <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Description --}}
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark">Description</label>
                    <textarea name="description" class="form-control" rows="3" 
                              style="border-radius: 8px;">{{ old('description', $category->description) }}</textarea>
                </div>

                <div class="row mb-4">
                    {{-- 1. Main Image Section --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark">Main Image (Thumbnail)</label>
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded" style="border: 1px dashed #ddd;">
                            @if ($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}" id="mainImagePreview" 
                                     class="rounded shadow-sm border" style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <div id="mainImagePreview" class="bg-white rounded border d-flex align-items-center justify-content-center text-muted" 
                                     style="width: 100px; height: 100px;">No Image</div>
                            @endif
                            
                            <div class="flex-grow-1">
                                <input type="file" name="image" class="form-control mb-1" accept="image/*" onchange="previewImage(event, 'mainImagePreview')">
                                <small class="text-muted">Replace current circular icon.</small>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Banner Image Section --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark">Banner Image (Header)</label>
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded" style="border: 1px dashed #ddd;">
                            @if ($category->banner_image)
                                <img src="{{ asset('storage/' . $category->banner_image) }}" id="bannerImagePreview"
                                     class="rounded shadow-sm border" style="width: 150px; height: 100px; object-fit: cover;">
                            @else
                                <div id="bannerImagePreview" class="bg-white rounded border d-flex align-items-center justify-content-center text-muted"
                                     style="width: 150px; height: 100px;">No Banner</div>
                            @endif

                            <div class="flex-grow-1">
                                <input type="file" name="banner_image" class="form-control mb-1" accept="image/*" onchange="previewImage(event, 'bannerImagePreview')">
                                <small class="text-muted">Replace top page banner.</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="mb-4">
                    <label class="form-label d-block fw-bold text-dark">Category Status</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="statusActive" value="1" 
                               {{ old('status', $category->status) == '1' ? 'checked' : '' }}>
                        <label class="form-check-label text-success fw-bold" for="statusActive">Active</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="statusInactive" value="0" 
                               {{ old('status', $category->status) == '0' ? 'checked' : '' }}>
                        <label class="form-check-label text-danger fw-bold" for="statusInactive">Inactive</label>
                    </div>
                </div>

                <div class="pt-3 border-top d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-light px-4 border">Cancel</a>
                {{-- 3. Change type to "button" and add onclick function --}}
                <button type="button" onclick="validateEditCategory()" class="btn text-white px-4 shadow-sm" style="background-color: #800000;">
                    <i class="bi bi-save me-1"></i> Update Category
                </button>
            </div>
        </form>
        </div>
    </div>
</div>

<script>
function previewImage(event, previewId) {
    const preview = document.getElementById(previewId);
    if (event.target.files && event.target.files[0]) {
        const fileUrl = URL.createObjectURL(event.target.files[0]);
        
        // Handle replacing placeholder div with img tag if necessary
        if (preview.tagName === 'DIV') {
            const newImg = document.createElement('img');
            newImg.id = previewId;
            newImg.className = "rounded shadow-sm border"; 
            newImg.style.width = preview.style.width;
            newImg.style.height = preview.style.height;
            newImg.style.objectFit = 'cover';
            newImg.src = fileUrl;
            preview.parentNode.replaceChild(newImg, preview);
        } else {
            preview.src = fileUrl;
        }
    }
}

function validateEditCategory() {
    const nameInput = document.getElementById('categoryName');
    const nameValue = nameInput.value.trim();
    const form = document.getElementById('editCategoryForm');

    // 

    // Check if the first character is uppercase (A-Z)
    if (nameValue.length > 0 && !/^[A-Z]/.test(nameValue)) {
        
        // SweetAlert Trigger
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Category name must start with a Capital Letter (e.g., "Tea" instead of "tea").',
            confirmButtonColor: '#800000', // Maroon theme matching
        });
        
        return false; // Stop the update
    }

    // If valid, proceed with submission
    form.submit();
}
</script>
@endsection