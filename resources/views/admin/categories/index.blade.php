@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark">Manage Categories</h4>
        <button class="btn" data-bs-toggle="modal" data-bs-target="#addCategoryModal"
            style="background-color: #f4b400; color: #000; font-weight: 600; border: none;">
            <i class="bi bi-plus-circle me-1"></i> Add New Category
        </button>
    </div>

    {{-- Categories Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>
                                @if ($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                                        width="50" height="50" class="rounded">
                                @else
                                    <span class="text-muted">No image</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td>{{ $category->description ?? '—' }}</td>
                            <td>{{ $category->slug }}</td>
                            <td>
                                @if ($category->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.categories.edit', $category->id) }}"
                                   class="btn btn-sm"
                                   style="background-color: #f4b400; color: #000; font-weight: 600;">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm text-white"
                                        style="background-color: #a81c1c; border: none;"
                                        onclick="return confirm('Delete this category?')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal for Add Category --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header text-white" style="background-color: #8a4b2b;">
                <h5 class="modal-title fw-semibold" id="addCategoryLabel">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- Name --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter category name" required>
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" placeholder="Short description (optional)" rows="2"></textarea>
                    </div>

                    {{-- Image --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                        <img id="imagePreview" class="mt-2 rounded" style="max-width: 100px; display:none;">
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label class="form-label d-block fw-bold">Status</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="statusActive" value="1" checked>
                            <label class="form-check-label" for="statusActive">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="statusInactive" value="0">
                            <label class="form-check-label" for="statusInactive">Inactive</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn text-white" data-bs-dismiss="modal"
                        style="background-color: #a81c1c; border: none;">Cancel</button>
                    <button type="submit" class="btn" style="background-color: #f4b400; color: #000; font-weight: 600;">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script for image preview --}}
<script>
function previewImage(event) {
    const preview = document.getElementById('imagePreview');
    preview.src = URL.createObjectURL(event.target.files[0]);
    preview.style.display = 'block';
}
</script>
@endsection
