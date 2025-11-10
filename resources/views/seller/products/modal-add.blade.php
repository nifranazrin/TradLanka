<div class="modal fade" id="addProductModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Add New Product</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Category</label>
            <select name="category_id" class="form-select" required>
              @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Price</label>
            <input type="number" name="price" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Stock</label>
            <input type="number" name="stock" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Status</label><br>
            <input type="radio" name="status" value="1" checked> Active
            <input type="radio" name="status" value="0"> Inactive
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-dark">Save Product</button>
        </div>
      </form>
    </div>
  </div>
</div>
