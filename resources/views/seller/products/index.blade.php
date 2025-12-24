@extends('layouts.seller')

@section('content')
{{-- Include SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- CUSTOM CSS FOR MAROON THEME --}}
<style>
    /* Maroon Backgrounds (for modal headers, etc.) */
    .bg-maroon {
        background-color: #410d0d !important;
        color: white !important;
    }

    /* Maroon Buttons */
    .btn-maroon {
        background-color: #550909 !important;
        color: white !important;
        border: 1px solid #800000;
    }
    .btn-maroon:hover {
        background-color: #570404 !important; /* Slightly darker on hover */
        border-color: #600000;
    }

    /* Maroon Table Header */
    .table-maroon th {
        background-color: #570606 !important;
        color: white !important;
        border-color: #993333; /* Slightly lighter border for definition */
    }
</style>

@php
    use Illuminate\Support\Facades\Storage;
    
    // Helper function to get clean URL
    $resolveUrl = function($p) {
        if (!$p) return asset('images/placeholder.png'); 
        $clean = preg_replace('/^public\//', '', $p);
        $clean = ltrim($clean, '/');
        if (preg_match('#^https?://#i', $clean)) {
            return $clean;
        }
        return Storage::url($clean);
    };
@endphp

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">My Products</h4>
        {{-- CHANGED: btn-dark to btn-maroon --}}
        <button class="btn btn-maroon" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="bi bi-plus-circle"></i> Add Product
        </button>
    </div>

    {{-- Products Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                {{-- CHANGED: table-dark to table-maroon --}}
                <thead class="table-maroon">
                    <tr>
                        <th>ID</th>
                        <th style="width: 100px;">Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Starting Price (Rs.)</th>
                        <th>Total Stock</th>
                        <th>Status</th>
                        <th>Added On</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>

                            {{-- Clean Image Column --}}
                            <td>
                                <div style="position: relative; width: 60px; height: 60px;">
                                    <img src="{{ $resolveUrl($product->image) }}" 
                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;" 
                                         alt="Product">
                                    @if($product->images && $product->images->count() > 0)
                                        <div style="position: absolute; bottom: -5px; right: -5px; 
                                                    background-color: #dc3545; color: white; 
                                                    font-size: 10px; font-weight: bold;
                                                    padding: 2px 6px; border-radius: 10px;
                                                    box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                            +{{ $product->images->count() }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $product->name }}
                            </td>

                            <td>{{ $product->category->name ?? '—' }}</td>
                            <td>{{ number_format($product->price, 2) }}</td>
                            
                            <td>
                                @if($product->stock < 5)
                                    <span class="text-danger fw-bold">{{ $product->stock }}</span>
                                @else
                                    {{ $product->stock }}
                                @endif
                            </td>

                            {{-- Status Badges --}}
                            <td>
                                @if($product->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($product->status === 'reapproved')
                                    <span class="badge" style="background-color: #66bb6a;">Re-Approved</span>
                                @elseif($product->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($product->status === 'reapproval_pending') 
                                    <span class="badge bg-info text-dark">Re-Approval Pending</span>
                                @elseif($product->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-secondary">{{ $product->status }}</span>
                                @endif
                            </td>

                            <td>{{ optional($product->created_at)->format('Y-m-d') }}</td>

                            <td>
                                <a href="{{ route('seller.products.show', $product->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('seller.products.edit', $product->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No products found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Product Modal --}}
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {{-- CHANGED: bg-dark to bg-maroon --}}
            <div class="modal-header bg-maroon text-white">
                <h5 class="modal-title">Add New Product</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="addProductForm" action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Product Name</label>
                            <input type="text" name="name" id="productNameInput" class="form-control" placeholder="E.g. Ceylon Tea" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Product Unit Type</label>
                        <select name="unit_type" class="form-control" required>
                            <option value="">-- Select Unit Type --</option>
                            <option value="weight">Weight (g / kg)</option>
                            <option value="liquid">Liquid (ml / L)</option>
                            <option value="default">Default (No unit)</option>
                        </select>
                    </div>

                    {{-- DYNAMIC VARIATIONS SECTION --}}
                    <div class="mb-4 p-3 border rounded bg-light">
                        <label class="fw-bold mb-2">Product Variations (Sizes & Prices)</label>
                        <div class="alert alert-info py-1 px-2 mb-2" style="font-size: 13px;">
                            <i class="bi bi-info-circle"></i> Add at least one size. The main price is auto-calculated.
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
                                {{-- Initial Row --}}
                                <tr>
                                    <td>
                                        <input type="text" name="variations[0][unit_label]" placeholder="e.g. 100g" class="form-control form-control-sm" required />
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="variations[0][price]" placeholder="500" class="form-control form-control-sm" required />
                                    </td>
                                    <td>
                                        <input type="number" name="variations[0][stock]" placeholder="50" class="form-control form-control-sm" required />
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" id="addVariantBtn" class="btn btn-sm btn-success">
                            <i class="bi bi-plus"></i> Add Another Size
                        </button>
                    </div>
                    {{-- END VARIATIONS --}}

                    <div class="mb-3">
                        <label class="fw-bold">Description</label>
                        <textarea name="description" rows="3" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Front Image (Main)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>

                    {{-- Gallery Images --}}
                    <div class="mb-3">
                        <label class="fw-bold">Gallery Images</label>
                        <div id="galleryDropArea" class="border rounded p-3 mb-2" 
                             style="min-height:100px; background:#f9f9f9; cursor:pointer; text-align:center;">
                            <p class="mb-0 mt-3 text-muted"><strong>Click or Drag images here</strong></p>
                        </div>
                        <input type="file" id="galleryInput" name="images[]" class="d-none" multiple accept="image/*">
                        <div id="galleryPreview" class="d-flex flex-wrap gap-2"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    {{-- CHANGED: btn-dark to btn-maroon --}}
                    <button type="submit" id="submitBtn" class="btn btn-maroon">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JAVASCRIPT LOGIC --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // -----------------------------------------------------------------
    // 1. SWEET ALERT LOGIC (With Butter Background & Maroon Button)
    // -----------------------------------------------------------------
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: "{{ session('success') }}",
            background: '#fff9e6',  // Butter background
            confirmButtonColor: '#800000', // Maroon button
            iconColor: '#28a745',
            timer: 4000,
            timerProgressBar: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: "{{ session('error') }}",
            background: '#fff0f0',
            confirmButtonColor: '#800000' // Maroon button
        });
    @endif

    // -----------------------------------------------------------------
    // 2. VARIANT TABLE LOGIC
    // -----------------------------------------------------------------
    let variantIndex = 1;
    const variantTableBody = document.getElementById('variantTableBody');
    const addVariantBtn = document.getElementById('addVariantBtn');

    addVariantBtn.addEventListener('click', function() {
        const row = document.createElement('tr');
        row.id = `row-${variantIndex}`;
        row.innerHTML = `
            <td><input type="text" name="variations[${variantIndex}][unit_label]" placeholder="e.g. 500g" class="form-control form-control-sm" required /></td>
            <td><input type="number" step="0.01" name="variations[${variantIndex}][price]" placeholder="Price" class="form-control form-control-sm" required /></td>
            <td><input type="number" name="variations[${variantIndex}][stock]" placeholder="Qty" class="form-control form-control-sm" required /></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row" data-id="${variantIndex}">&times;</button></td>
        `;
        variantTableBody.appendChild(row);
        variantIndex++;
    });

    variantTableBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            const id = e.target.getAttribute('data-id');
            document.getElementById(`row-${id}`).remove();
        }
    });

    // -----------------------------------------------------------------
// 3. IMAGE UPLOAD & VALIDATION LOGIC (FIXED)
// -----------------------------------------------------------------
const dropArea = document.getElementById('galleryDropArea');
const galleryInput = document.getElementById('galleryInput');
const preview = document.getElementById('galleryPreview');
const form = document.getElementById('addProductForm');
const nameInput = document.getElementById('productNameInput');

const dt = new DataTransfer();

// CLICK → FILE PICKER
dropArea.addEventListener('click', () => galleryInput.click());

// FILE PICKER
galleryInput.addEventListener('change', function () {
    for (let i = 0; i < this.files.length; i++) {
        dt.items.add(this.files[i]);
    }
    updatePreview();
    this.value = ''; // allow re-select same file
});

// DRAG OVER
dropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropArea.classList.add('dragover');
});

// DRAG LEAVE
dropArea.addEventListener('dragleave', () => {
    dropArea.classList.remove('dragover');
});

// DROP
dropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    dropArea.classList.remove('dragover');

    for (let i = 0; i < e.dataTransfer.files.length; i++) {
        if (e.dataTransfer.files[i].type.startsWith('image/')) {
            dt.items.add(e.dataTransfer.files[i]);
        }
    }
    updatePreview();
});

// PREVIEW + REMOVE
function updatePreview() {
    preview.innerHTML = '';

    [...dt.files].forEach((file, index) => {
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        wrapper.style.width = '60px';
        wrapper.style.height = '60px';

        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.className = 'rounded border';
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';

        const btn = document.createElement('button');
        btn.innerHTML = '&times;';
        btn.className = 'btn btn-danger btn-sm p-0 rounded-circle';
        btn.style.position = 'absolute';
        btn.style.top = '-6px';
        btn.style.right = '-6px';
        btn.style.width = '20px';
        btn.style.height = '20px';
        btn.style.lineHeight = '18px';

        btn.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            dt.items.remove(index);
            updatePreview();
        };

        wrapper.appendChild(img);
        wrapper.appendChild(btn);
        preview.appendChild(wrapper);
    });
}

// ✅ FIX: ATTACH FILES BEFORE SUBMIT
form.addEventListener('submit', function () {
    galleryInput.files = dt.files;
        
        // Capital Letter Validation
        if (nameValue.length > 0 && nameValue.charAt(0) !== nameValue.charAt(0).toUpperCase()) {
            e.preventDefault(); 
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Product Name must start with a Capital Letter!',
                background: '#fff9e6', // Butter background
                confirmButtonColor: '#800000' // Maroon button
            });
            return;
        }

        if (dt.files.length > 0) {
            galleryInput.files = dt.files;
        }
        
        const btn = document.getElementById('submitBtn');
        btn.innerText = 'Saving...';
        btn.disabled = true;
    });
});
</script>

@endsection