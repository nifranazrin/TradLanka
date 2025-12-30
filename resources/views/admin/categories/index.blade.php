@extends('layouts.admin')

@section('content')
<style>
    /* Professional Maroon Header */
    .maroon-header {
        background-color: #800000 !important;
    }
    .maroon-header th {
        background-color: #420707 !important;
        color: white !important;
        padding: 15px !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        border: none !important;
    }

    /* Unified Search Bar Focus */
    .unified-search-bar {
        border: 2px solid #e0e0e0;
        border-radius: 10px !important;
        overflow: hidden;
        transition: all 0.3s ease;
        max-width: 450px;
    }
    .unified-search-bar:focus-within {
        border-color: #800000 !important;
        box-shadow: 0 0 0 4px rgba(128, 0, 0, 0.1) !important;
    }

    /* Table Container Shadow */
    .custom-shadow-table {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        border-radius: 12px;
        overflow: hidden;
        background: white;
        border: 1px solid #f0f0f0;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark">
             <i class="bi bi-grid-fill" style="color: #800000;"></i> Manage Categories
        </h4>
        <button class="btn shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal"
            style="background-color: #f4b400; color: #000; font-weight: 600; border: none;">
            <i class="bi bi-plus-circle me-1"></i> Add New Category
        </button>
    </div>

    {{-- Unified Search Bar --}}
    <div class="input-group unified-search-bar shadow-sm mb-4">
        <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-search text-muted"></i>
        </span>
        <input type="text" id="categorySearch" class="form-control border-start-0 ps-0" 
               placeholder="Search by Name or Category Type...">
    </div>

    {{-- Categories Table with Shadow and Maroon Header --}}
    <div class="custom-shadow-table">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="categoryTable">
                <thead>
                    <tr class="maroon-header">
                        <th class="text-center">ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category Type</th> {{-- Renamed from Parent --}}
                        <th>Description</th>
                        <th>Slug</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td class="text-center text-muted fw-bold">{{ $category->id }}</td>
                            
                            <td>
                                <div class="d-flex flex-column align-items-start">
                                    @if ($category->image)
                                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                                            width="50" height="50" class="rounded border shadow-sm" style="object-fit: cover;">
                                    @else
                                        <span class="text-muted text-xs italic">No Icon</span>
                                    @endif

                                    @if ($category->banner_image)
                                        <span class="badge bg-secondary mt-1" style="font-size: 0.6rem;">+ Banner</span>
                                    @endif
                                </div>
                            </td>

                            <td class="fw-semibold text-dark">{{ $category->name }}</td>
                            
                            <td>
                                @if($category->parent)
                                    <span class="badge bg-info text-dark shadow-sm">{{ $category->parent->name }}</span>
                                @else
                                    <span class="badge bg-success shadow-sm">Main Category</span>
                                @endif
                            </td>

                            <td class="text-muted small">{{ Str::limit($category->description ?? '—', 40) }}</td>
                            <td class="text-secondary small">{{ $category->slug }}</td>
                            
                            <td class="text-center">
                                @if ($category->status == 1)
                                    <span class="badge bg-success rounded-pill px-3">Active</span>
                                @else
                                    <span class="badge bg-danger rounded-pill px-3">Inactive</span>
                                @endif
                            </td>
                            
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.categories.edit', $category->id) }}"
                                       class="btn btn-sm shadow-sm"
                                       style="background-color: #f4b400; color: #000; font-weight: 600;">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm text-white shadow-sm confirm-delete"
                                                style="background-color: #a81c1c; border: none;">
                                                <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-grid-3x3-gap fs-1 d-block mb-2"></i>
                                No categories found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal for Add Category --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background-color: #800000;"> {{-- Matches Maroon Theme --}}
                <h5 class="modal-title fw-semibold" id="addCategoryLabel">Add New Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter category name" 
                                   value="{{ old('name') }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Category Type (Parent)</label>
                            <select name="parent_id" class="form-select">
                                <option value="">None (Create as Main Category)</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" placeholder="Short description (optional)" rows="2">{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Main Image (Thumbnail)</label>
                            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event, 'imagePreview')">
                            <img id="imagePreview" class="mt-2 rounded border shadow-sm" style="max-width: 100px; max-height: 100px; object-fit: cover; display:none;">
                            <small class="text-muted d-block mt-1">Used for circular icons.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Banner Image (Page Header)</label>
                            <input type="file" name="banner_image" class="form-control" accept="image/*" onchange="previewImage(event, 'bannerPreview')">
                            <img id="bannerPreview" class="mt-2 rounded border shadow-sm" style="max-width: 100%; max-height: 100px; object-fit: cover; display:none;">
                            <small class="text-muted d-block mt-1">Used for top banners.</small>
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
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn text-white px-4" data-bs-dismiss="modal"
                        style="background-color: #a81c1c; border: none;">Cancel</button>
                    <button type="submit" class="btn px-4" style="background-color: #f4b400; color: #000; font-weight: 600;">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Image Previews
    function previewImage(event, previewId) {
        const preview = document.getElementById(previewId);
        if (event.target.files && event.target.files[0]) {
            preview.src = URL.createObjectURL(event.target.files[0]);
            preview.style.display = 'block';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Real-time Search Logic
        const searchInput = document.getElementById('categorySearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll('#categoryTable tbody tr');

                rows.forEach(row => {
                    const name = row.cells[2].textContent.toLowerCase();
                    const type = row.cells[3].textContent.toLowerCase();

                    if (name.includes(filter) || type.includes(filter)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });
        }

        // Professional Delete Confirmation
        document.querySelectorAll('.confirm-delete').forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('.delete-form');
                Swal.fire({
                    title: 'Delete Category?',
                    text: "Deleting this may affect linked products and subcategories!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#a81c1c',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    });
</script>
@endsection