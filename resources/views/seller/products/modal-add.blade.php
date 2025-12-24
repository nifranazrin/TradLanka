<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="addProductForm" action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-body">
                    <div class="row">
                        {{-- CATEGORY --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- UNIT TYPE --}}
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold form-label">Unit Type</label>
                            <select name="unit_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="weight">Weight (g / kg)</option>
                                <option value="liquid">Liquid (ml / L)</option>
                                <option value="default">Default (No unit)</option>
                            </select>
                        </div>
                    </div>

                    {{-- NAME --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Product Name</label>
                        <input type="text" name="name" id="productNameInput" class="form-control" placeholder="E.g. Ceylon Tea" required>
                        <small class="text-muted">Must start with a Capital letter.</small>
                    </div>

                    {{-- DYNAMIC VARIANTS SECTION (REQUIRED FOR CONTROLLER) --}}
                    <div class="mb-4 p-3 border rounded bg-light">
                        <label class="fw-bold mb-2">Product Variations (Sizes & Prices)</label>
                        <div class="alert alert-info py-1 px-2 mb-2 small">
                            <i class="bi bi-info-circle"></i> Add at least one size. The main price is auto-calculated.
                        </div>

                        <table class="table table-bordered table-sm bg-white" id="variantTable">
                            <thead>
                                <tr>
                                    <th>Size (e.g. 100g)</th>
                                    <th>Price (Rs.)</th>
                                    <th>Stock</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="variantTableBody">
                                {{-- Initial Row --}}
                                <tr>
                                    <td>
                                        <input type="text" name="variations[0][unit_label]" placeholder="Size" class="form-control form-control-sm" required />
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="variations[0][price]" placeholder="Price" class="form-control form-control-sm" required />
                                    </td>
                                    <td>
                                        <input type="number" name="variations[0][stock]" placeholder="Qty" class="form-control form-control-sm" required />
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" id="addVariantBtn" class="btn btn-sm btn-success">
                            <i class="bi bi-plus"></i> Add Another Size
                        </button>
                    </div>
                    {{-- END VARIANTS --}}

                    {{-- DESCRIPTION --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    {{-- MAIN FRONT IMAGE --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Front Image (Main)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>

                    {{-- GALLERY IMAGES --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Gallery Images</label>
                        
                        {{-- Drag & Drop Area --}}
                        <div id="galleryDropArea" class="border rounded p-3 mb-2 text-center" style="background:#f9f9f9; cursor:pointer;">
                            <p class="mb-0 text-muted">Click or Drag images here</p>
                        </div>
                        
                        {{-- Hidden Input --}}
                        <input type="file" id="galleryInput" name="images[]" class="d-none" multiple accept="image/*">
                        
                        {{-- Preview Container --}}
                        <div id="galleryPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button id="addProductSubmit" type="submit" class="btn btn-dark">Save Product</button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- JAVASCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // --- 1. VARIANT LOGIC (Add Rows) ---
    let variantIndex = 1;
    const variantTableBody = document.getElementById('variantTableBody');
    const addVariantBtn = document.getElementById('addVariantBtn');

    if(addVariantBtn) {
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

        // Remove Row Logic
        variantTableBody.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row')) {
                const id = e.target.getAttribute('data-id');
                document.getElementById(`row-${id}`).remove();
            }
        });
    }

    // --- 2. IMAGE UPLOAD LOGIC ---
    const dropArea = document.getElementById('galleryDropArea');
    const galleryInput = document.getElementById('galleryInput');
    const preview = document.getElementById('galleryPreview');
    const form = document.getElementById('addProductForm');
    const submitBtn = document.getElementById('addProductSubmit');
    
    // DataTransfer object to hold files
    const dt = new DataTransfer();

    if(dropArea) {
        // Click to open file dialog
        dropArea.addEventListener('click', () => galleryInput.click());

        // Handle File Selection
        galleryInput.addEventListener('change', function() {
            for (let i = 0; i < this.files.length; i++) {
                dt.items.add(this.files[i]);
            }
            updatePreview();
            this.value = ''; // Clear input to allow re-selecting same file
        });

        // Drag & Drop Effects
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
    }

    function updatePreview() {
        preview.innerHTML = '';
        [...dt.files].forEach((file, index) => {
            let div = document.createElement('div');
            div.style.position = 'relative';
            div.style.width = '70px';
            div.style.height = '70px';

            let img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.className = 'rounded border';

            let btn = document.createElement('button');
            btn.innerHTML = '&times;';
            btn.className = 'btn btn-danger btn-sm p-0 rounded-circle position-absolute';
            btn.style.top = '-5px';
            btn.style.right = '-5px';
            btn.style.width = '20px';
            btn.style.height = '20px';
            btn.style.lineHeight = '18px';
            
            btn.onclick = (e) => {
                e.preventDefault(); 
                dt.items.remove(index);
                updatePreview();
            };

            div.appendChild(img);
            div.appendChild(btn);
            preview.appendChild(div);
        });
    }

    // --- 3. FORM SUBMIT HANDLER ---
    if(form) {
        form.addEventListener('submit', function(e) {
            
            // Name Capitalization Check
            const nameInput = document.getElementById('productNameInput');
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

            // Sync DataTransfer files to Input
            if (dt.files.length > 0) {
                galleryInput.files = dt.files;
            }

            // Button Loading State
            submitBtn.innerText = 'Saving...';
            submitBtn.disabled = true;
        });
    }
});
</script>