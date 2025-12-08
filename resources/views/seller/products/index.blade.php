@extends('layouts.seller')

@section('content')
{{-- Include SweetAlert2 CDN (If not already in your layout) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="bi bi-plus-circle"></i> Add Product
        </button>
    </div>

    {{-- Products Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th style="width: 100px;">Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price (Rs.)</th>
                        <th>Stock</th>
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
                            <td>
    {{-- 1. Standard Approved (Dark Green / Default Success) --}}
    @if($product->status === 'approved')
        <span class="badge bg-success">Approved</span>

    {{-- 2. Re-Approved (Light Green) --}}
    @elseif($product->status === 'reapproved')
        {{-- I added a custom hex color code (#66bb6a) to make this lighter --}}
        <span class="badge" style="background-color: #66bb6a;">Re-Approved</span>
    
    {{-- 3. Pending States --}}
    @elseif($product->status === 'pending')
        <span class="badge bg-warning text-dark">Pending</span>
        
    @elseif($product->status === 'reapproval_pending') 
        <span class="badge bg-info text-dark">Re-Approval Pending</span>

    {{-- 4. Rejected --}}
    @elseif($product->status === 'rejected')
        <span class="badge bg-danger">Rejected</span>

    @else
        <span class="badge bg-secondary">Unknown: {{ $product->status }}</span>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Add New Product</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="addProductForm" action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold">Product Name</label>
                        {{-- Added ID="productNameInput" for easier selection --}}
                        <input type="text" name="name" id="productNameInput" class="form-control" placeholder="E.g. Ceylon Tea" required>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Category</label>
                        <select name="category_id" class="form-control" required>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Price (Rs.)</label>
                            <input type="number" name="price" min="0" step="0.01" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Stock</label>
                            <input type="number" name="stock" min="0" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Description</label>
                        <textarea name="description" rows="2" class="form-control"></textarea>
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
                    <button type="submit" id="submitBtn" class="btn btn-dark">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SCRIPT WITH VALIDATION --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropArea = document.getElementById('galleryDropArea');
    const galleryInput = document.getElementById('galleryInput');
    const preview = document.getElementById('galleryPreview');
    const form = document.getElementById('addProductForm');
    const nameInput = document.getElementById('productNameInput'); // Get Name Input
    
    const dt = new DataTransfer();

    // 1. Click to Open
    dropArea.addEventListener('click', () => galleryInput.click());

    // 2. Handle File Selection
    galleryInput.addEventListener('change', function() {
        for (let i = 0; i < this.files.length; i++) {
            dt.items.add(this.files[i]);
        }
        updatePreview();
        this.value = ''; 
    });

    // 3. Handle Drag & Drop
    dropArea.addEventListener('dragover', (e) => { e.preventDefault(); dropArea.style.borderColor = 'blue'; });
    dropArea.addEventListener('dragleave', () => { dropArea.style.borderColor = '#dee2e6'; });
    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.style.borderColor = '#dee2e6';
        for (let i = 0; i < e.dataTransfer.files.length; i++) {
            if (e.dataTransfer.files[i].type.startsWith('image/')) {
                dt.items.add(e.dataTransfer.files[i]);
            }
        }
        updatePreview();
    });

    // 4. Update Preview Function
    function updatePreview() {
        preview.innerHTML = '';
        [...dt.files].forEach((file, index) => {
            let div = document.createElement('div');
            div.style.position = 'relative';
            div.style.width = '60px';
            div.style.height = '60px';

            let img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.className = 'rounded border';

            let btn = document.createElement('button');
            btn.innerHTML = '&times;';
            btn.className = 'btn btn-danger btn-sm p-0 rounded-circle';
            btn.style.position = 'absolute';
            btn.style.top = '-5px';
            btn.style.right = '-5px';
            btn.style.width = '20px';
            btn.style.height = '20px';
            btn.style.lineHeight = '18px';
            
            btn.onclick = (e) => {
                e.preventDefault(); 
                e.stopPropagation(); 
                dt.items.remove(index);
                updatePreview();
            };

            div.appendChild(img);
            div.appendChild(btn);
            preview.appendChild(div);
        });
    }

    // 5. SUBMIT HANDLER WITH VALIDATION
    form.addEventListener('submit', function(e) {
        
        // --- NEW: Validation Check ---
        const nameValue = nameInput.value.trim();
        
        // Check if first letter is NOT uppercase
        if (nameValue.length > 0 && nameValue.charAt(0) !== nameValue.charAt(0).toUpperCase()) {
            e.preventDefault(); // Stop the form submission
            
            // Show SweetAlert
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Product Name must start with a Capital Letter!',
                confirmButtonColor: '#212529' // Matches your dark theme
            });
            
            return; // Stop the script here
        }
        // -----------------------------

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