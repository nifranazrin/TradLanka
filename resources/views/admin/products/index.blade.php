@extends('layouts.admin')

@section('content')
<div class="container">
    <h3 class="mb-4">Review Products</h3>

    <div class="table-responsive">
        <table class="table table-bordered align-middle shadow-sm">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Seller</th>
                    <th>Price (Rs)</th>
                    <th>Status</th>
                    <th>Stock</th>
                    <th>Added On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $key => $product)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->seller->name ?? 'N/A' }}</td>
                    <td>{{ number_format($product->price, 2) }}</td>

                    {{-- ✅ Product Status --}}
                    <td>
                        @if ($product->status == 'approved')
                            <span class="badge bg-success">Approved</span>
                        @elseif ($product->status == 'reapproval_pending')
                            <span class="badge bg-warning text-dark">Re-Approval Needed</span>
                        @elseif ($product->status == 'reapproved')
                            <span class="badge" style="background-color: #90EE90; color: #000;">Re-Approved</span>
                        @elseif ($product->status == 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>

                    <td>{{ $product->stock }}</td>
                    <td>{{ $product->created_at->format('Y-m-d') }}</td>

                    {{-- ✅ Actions --}}
                    <td>
                        {{-- View Button --}}
                        <a href="{{ route('admin.products.show', $product->id) }}" 
                           class="btn btn-sm btn-info" title="View">
                            <i class="bi bi-eye"></i>
                        </a>

                        {{-- ✅ Approve Button (light green if reapproval_pending) --}}
                        <form action="{{ route('admin.products.approve', $product->id) }}" 
                              method="POST" class="d-inline">
                            @csrf
                            <button 
                                class="btn btn-sm 
                                    {{ $product->status === 'reapproval_pending' ? 'btn-light text-success border-success' : 'btn-success' }}" 
                                title="{{ $product->status === 'reapproval_pending' ? 'Re-Approve' : 'Approve' }}">
                                <i class="bi bi-check2-circle"></i>
                            </button>
                        </form>

                        {{-- Reject Button --}}
                        <form action="{{ route('admin.products.reject', $product->id) }}" 
                              method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-danger" title="Reject">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No products found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
