<div class="modal fade" id="addProductModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Add New Product</h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="addProductForm" action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="modal-body">

          {{-- CATEGORY --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Category</label>
            <select name="category_id" class="form-select" required>
              @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- NAME --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Product Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>

          {{-- DESCRIPTION --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>

          {{-- PRICE --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Price</label>
            <input type="number" name="price" class="form-control" min="0" step="0.01" required>
          </div>

          {{-- STOCK --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Stock</label>
            <input type="number" name="stock" class="form-control" min="0" required>
          </div>

          {{-- MAIN FRONT IMAGE --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Front Image (Main)</label>
            <input type="file" name="image" class="form-control" accept="image/*">
          </div>

          {{-- GALLERY IMAGES --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Additional Images (Gallery)</label>

            {{-- file input (we will attempt to set its .files) --}}
            <input type="file" id="galleryInput" name="images[]" class="form-control" accept="image/*" multiple>

            <small class="text-muted d-block mb-2">
              You can select multiple images. Pick files multiple times to add more — uploader will accumulate selections.
            </small>

            {{-- preview --}}
            <div id="galleryPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
          </div>

          {{-- STATUS informational --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Status</label><br>
            <span class="badge bg-info text-dark">Status will be set to <strong>Pending</strong> until admin approval.</span>
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

{{-- Script: accumulate files, preview, robust submit --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  const galleryInput = document.getElementById('galleryInput');
  const preview = document.getElementById('galleryPreview');
  const form = document.getElementById('addProductForm');
  const submitBtn = document.getElementById('addProductSubmit');

  if (!galleryInput || !form) return;

  // DataTransfer to accumulate files chosen across multiple picks
  let dt = new DataTransfer();

  // whether we successfully assigned dt.files to input.files in this browser
  let assignSupported = true;

  // helper: render preview thumbnails
  function renderPreview() {
    preview.innerHTML = '';
    Array.from(dt.files).forEach((file, idx) => {
      if (!file.type.startsWith('image/')) return;

      const url = URL.createObjectURL(file);

      const wrap = document.createElement('div');
      wrap.style.width = '84px';
      wrap.style.position = 'relative';

      const img = document.createElement('img');
      img.src = url;
      img.style.width = '84px';
      img.style.height = '84px';
      img.style.objectFit = 'cover';
      img.className = 'rounded border';

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.innerHTML = '&times;';
      btn.className = 'btn btn-sm btn-danger position-absolute';
      btn.style.top = '-6px';
      btn.style.right = '-6px';
      btn.style.borderRadius = '50%';
      btn.style.width = '22px';
      btn.style.height = '22px';
      btn.style.padding = '0';
      btn.addEventListener('click', () => {
        removeFileAt(idx);
      });

      wrap.appendChild(img);
      wrap.appendChild(btn);
      preview.appendChild(wrap);

      img.onload = () => URL.revokeObjectURL(url);
    });

    // Attempt to sync to native input.files (may fail in some browsers)
    try {
      galleryInput.files = dt.files;
      assignSupported = true;
    } catch (err) {
      assignSupported = false;
      // we'll fallback to explicit FormData upload on submit
      console.warn('Could not assign files to input.files (browser limitation). Will fallback to FormData submit.', err);
    }
  }

  function removeFileAt(index) {
    const files = Array.from(dt.files);
    const newDt = new DataTransfer();
    files.forEach((f, i) => {
      if (i !== index) newDt.items.add(f);
    });
    dt = newDt;
    renderPreview();
  }

  // user picks files via input
  galleryInput.addEventListener('change', function (e) {
    const files = Array.from(e.target.files || []);
    files.forEach(f => {
      if (f.type && f.type.startsWith('image/')) {
        // avoid duplicate by name+size
        const exists = Array.from(dt.files).some(x => x.name === f.name && x.size === f.size && x.type === f.type);
        if (!exists) dt.items.add(f);
      }
    });
    // clear native file input so next picks append manually
    galleryInput.value = '';
    renderPreview();
  });

  // form submit handler — fallback safe submit
  form.addEventListener('submit', async function (ev) {
    // If assignSupported is true, try one more time to sync dt.files into input before allowing native submit
    if (assignSupported && dt.files.length > 0) {
      try {
        galleryInput.files = dt.files;
        // native input now has the files — let browser do regular multipart submit
        return;
      } catch (err) {
        assignSupported = false;
        // continue to fallback to fetch
      }
    }

    // If dt is empty and nothing selected, allow normal submit
    if (dt.files.length === 0) {
      return; // no gallery files — native submit fine
    }

    // Otherwise intercept and send via fetch with FormData (append files from dt explicitly)
    ev.preventDefault();

    if (submitBtn) {
      submitBtn.disabled = true;
      const oldText = submitBtn.innerHTML;
      submitBtn.innerHTML = 'Saving...';
      try {
        const fd = new FormData();

        // Append all form fields except images[] — we'll add images from dt
        const nativeFd = new FormData(form);
        for (let pair of nativeFd.entries()) {
          if (pair[0] === 'images[]') continue;
          fd.append(pair[0], pair[1]);
        }

        // Append gallery files from dt
        Array.from(dt.files).forEach(file => {
          fd.append('images[]', file, file.name);
        });

        // Append any remaining native input files (edge-case)
        if (galleryInput.files && galleryInput.files.length) {
          Array.from(galleryInput.files).forEach(f => {
            const duplicate = Array.from(dt.files).some(x => x.name === f.name && x.size === f.size);
            if (!duplicate && f.type.startsWith('image/')) fd.append('images[]', f, f.name);
          });
        }

        // CSRF token from meta tag or blade
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        const res = await fetch(form.action, {
          method: (form.method || 'POST').toUpperCase(),
          body: fd,
          credentials: 'same-origin',
          headers: {
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html,application/json'
          }
        });

        // If server redirected, follow it
        if (res.redirected) {
          window.location = res.url;
          return;
        }

        if (res.ok) {
          // success -> reload to show new product
          window.location.reload();
          return;
        }

        //  JSON error or show generic
        let text = await res.text();
        try {
          const json = JSON.parse(text);
          alert(json.message || json.error || 'Failed to save product.');
        } catch (err) {
          alert('Failed to save product. Server returned an error.');
        }
      } catch (err) {
        console.error('Submit error', err);
        alert('Network or server error while saving product.');
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = oldText || 'Save Product';
        }
      }
    }
  });

});
</script>
