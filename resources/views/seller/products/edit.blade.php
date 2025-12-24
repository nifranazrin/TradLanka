@extends('layouts.seller')

@section('content')
{{-- Include SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    
    {{-- Back Button --}}
    <div class="mb-3">
        <a href="{{ route('seller.products.index') }}" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Products
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h4 class="mb-3 fw-bold">Edit Product: <span class="text-primary">{{ $product->name }}</span></h4>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                    </ul>
                </div>
            @endif

            {{-- MAIN EDIT FORM --}}
            <form id="editProductForm" action="{{ route('seller.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Basic Info --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Product Name</label>
                        <input type="text" name="name" id="productNameInput" class="form-control" value="{{ old('name', $product->name) }}" required>
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

                <div class="mb-3">
                    <label class="fw-bold">Product Unit Type</label>
                    <select name="unit_type" class="form-control" required>
                        <option value="weight" {{ $product->unit_type == 'weight' ? 'selected' : '' }}>Weight (g / kg)</option>
                        <option value="liquid" {{ $product->unit_type == 'liquid' ? 'selected' : '' }}>Liquid (ml / L)</option>
                        <option value="default" {{ $product->unit_type == 'default' ? 'selected' : '' }}>Default (No unit)</option>
                    </select>
                </div>

                {{-- DYNAMIC VARIATIONS SECTION (EDIT MODE) --}}
                <div class="mb-4 p-3 border rounded bg-light">
                    <label class="fw-bold mb-2">Product Variations (Sizes & Prices)</label>
                    <div class="alert alert-info py-1 px-2 mb-2" style="font-size: 13px;">
                        <i class="bi bi-info-circle"></i> Edit existing sizes or add new ones. 
                        <strong>Note:</strong> Removing a row here will delete that size from stock.
                    </div>

                    <table class="table table-bordered table-sm bg-white" id="dynamic_field">
                        <thead>
                            <tr>
                                <th>Size / Unit (e.g. 200g)</th>
                                <th>Price (Rs.)</th>
                                <th>Stock Qty</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="variantTableBody">
                            {{-- LOOP THROUGH EXISTING VARIANTS --}}
                            @if($product->variants && $product->variants->count() > 0)
                                @foreach($product->variants as $index => $variant)
                                    <tr id="row-old-{{ $index }}">
                                        <td>
                                            <input type="text" name="variations[{{ $index }}][unit_label]" 
                                                   value="{{ $variant->unit_label }}" 
                                                   class="form-control form-control-sm" required />
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="variations[{{ $index }}][price]" 
                                                   value="{{ $variant->price }}" 
                                                   class="form-control form-control-sm" required />
                                        </td>
                                        <td>
                                            <input type="number" name="variations[{{ $index }}][stock]" 
                                                   value="{{ $variant->stock }}" 
                                                   class="form-control form-control-sm" required />
                                        </td>
                                        <td>
                                            {{-- Only allow delete if it's not the last remaining row (optional logic, handled in JS) --}}
                                            <button type="button" class="btn btn-danger btn-sm remove-row" data-id="old-{{ $index }}">&times;</button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                {{-- Fallback if no variants exist (Legacy Data) --}}
                                <tr id="row-0">
                                    <td><input type="text" name="variations[0][unit_label]" value="Default" class="form-control form-control-sm" required /></td>
                                    <td><input type="number" step="0.01" name="variations[0][price]" value="{{ $product->price }}" class="form-control form-control-sm" required /></td>
                                    <td><input type="number" name="variations[0][stock]" value="{{ $product->stock }}" class="form-control form-control-sm" required /></td>
                                    <td></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    <button type="button" id="addVariantBtn" class="btn btn-sm btn-success">
                        <i class="bi bi-plus"></i> Add Another Size
                    </button>
                </div>
                {{-- END DYNAMIC VARIATIONS --}}

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
                            <div class="small text-muted text-center mt-1">Current</div>
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
                                <div class="text-center position-relative" style="width: 80px;">
                                    <img src="{{ $resolveUrl($img->path) }}" width="80" height="80" class="rounded border mb-1" style="object-fit:cover;">
                                    
                                    <button type="button" 
                                            class="btn btn-danger btn-sm py-0 w-100 position-absolute bottom-0 start-0 rounded-0 rounded-bottom" 
                                            onclick="deleteImage({{ $img->id }})">
                                        <i class="bi bi-trash" style="font-size: 0.8rem;"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted fst-italic">No additional images yet.</p>
                    @endif

                    {{-- 2. UPLOAD NEW IMAGES --}}
                    <label class="small fw-bold mt-2">Add New Images:</label>
                    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                    <small class="text-muted">Select multiple files to add them to the gallery.</small>
                </div>

                <div class="text-end">
                    <a href="{{ route('seller.products.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" id="submitBtn" class="btn btn-dark px-4">Update Product</button>
                </div>
            </form>
            
            {{-- HIDDEN FORM FOR DELETING IMAGES --}}
            {{-- Make sure your route definition matches: Route::delete('/seller/products/image/{id}', ...) --}}
            <form id="deleteImageForm" action="" method="POST" style="display:none;">
                @csrf
                @method('DELETE') 
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- 1. VARIANT LOGIC (Add/Remove Rows) ---
    // Start index higher than existing count to avoid ID conflicts
    let variantIndex = {{ $product->variants->count() + 100 }}; 
    const variantTableBody = document.getElementById('variantTableBody');
    const addVariantBtn = document.getElementById('addVariantBtn');

    addVariantBtn.addEventListener('click', function() {
        const row = document.createElement('tr');
        row.id = `row-new-${variantIndex}`;
        row.innerHTML = `
            <td><input type="text" name="variations[${variantIndex}][unit_label]" placeholder="e.g. 500g" class="form-control form-control-sm" required /></td>
            <td><input type="number" step="0.01" name="variations[${variantIndex}][price]" placeholder="Price" class="form-control form-control-sm" required /></td>
            <td><input type="number" name="variations[${variantIndex}][stock]" placeholder="Qty" class="form-control form-control-sm" required /></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row" data-id="new-${variantIndex}">&times;</button></td>
        `;
        variantTableBody.appendChild(row);
        variantIndex++;
    });

    // Event delegation for removing rows
    variantTableBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            const id = e.target.getAttribute('data-id');
            const row = document.getElementById(`row-${id}`);
            
            // Optional: Prevent deleting the last row if you want to enforce at least 1 variant
            // if (variantTableBody.children.length > 1) { row.remove(); } else { alert('You need at least one variant.'); }
            
            row.remove();
        }
    });

    // --- 2. VALIDATION (Capital Letter Check) ---
    const form = document.getElementById('editProductForm');
    const nameInput = document.getElementById('productNameInput');

    form.addEventListener('submit', function(e) {
        const nameValue = nameInput.value.trim();
        if (nameValue.length > 0 && nameValue.charAt(0) !== nameValue.charAt(0).toUpperCase()) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Product Name must start with a Capital Letter!',
                confirmButtonColor: '#212529'
            });
            return;
        }
        
        // Show loading state
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
        btn.disabled = true;
    });
});

// --- 3. IMAGE DELETION LOGIC ---
function deleteImage(id) {
    Swal.fire({
        title: 'Delete Image?',
        text: "This cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.getElementById('deleteImageForm');
            // Ensure this URL matches your web.php route for deleting images
            form.action = '/seller/products/image/' + id; 
            form.submit();
        }
    })
}
</script>

@endsection