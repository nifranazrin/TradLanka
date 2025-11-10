@extends('layouts.seller')

@section('content')
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
                        <th>Image</th>
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
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" width="50" class="rounded">
                                @else
                                    <span class="text-muted">No image</span>
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name ?? '—' }}</td>
                            <td>{{ number_format($product->price, 2) }}</td>
                            <td>{{ $product->stock }}</td>

                            {{--  Product Status --}}
                            <td>
                                @if($product->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending Approval</span>
                                @elseif($product->status === 'reapproval_pending')
                                    <span class="badge bg-warning text-dark">Pending Re-Approval</span>
                                @elseif($product->status === 'reapproved')
                                    <span class="badge" style="background-color:#90EE90; color:#000;">Re-Approved</span>
                                @elseif($product->status === 'approved' || $product->status === 'active')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($product->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-secondary">Unknown</span>
                                @endif
                            </td>

                            <td>{{ $product->created_at->format('Y-m-d') }}</td>

                            {{--  Actions --}}
                            <td>
                                {{-- View button (always available) --}}
                                <a href="{{ route('seller.products.show', $product->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>

                                {{--  Edit button (always allowed for the seller) --}}
                                <a href="{{ route('seller.products.edit', $product->id) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">
                                No products found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Product Modal --}}
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Add New Product</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('seller.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Product Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Price (Rs.)</label>
                        <input type="number" name="price" class="form-control" min="0" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Stock</label>
                        <input type="number" name="stock" class="form-control" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
