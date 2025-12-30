@extends('layouts.admin')

@section('content')
{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Professional Maroon Header */
    .maroon-header {
        background-color: #800000 !important;
    }
    .maroon-header th {
        background-color: #420b0b !important;
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

    /* Status Badges */
    .badge-custom {
        font-size: 12px;
        padding: 5px 12px;
        border-radius: 6px;
        font-weight: 600;
        text-transform: uppercase;
    }
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 style="font-weight: 700; color: #333;">
            <i class="bi bi-box-seam" style="color: #800000;"></i> Review Products
        </h4>
    </div>

    <div class="search-container mb-4" style="max-width: 450px;">
        <div class="input-group unified-search-bar shadow-sm">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" id="productSearch" class="form-control border-start-0 ps-0" 
                   placeholder="Search by Product Name or Seller...">
        </div>
    </div>

    <div class="custom-shadow-table">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="productTable">
                <thead>
                    <tr class="maroon-header">
                        <th class="text-center">#</th>
                        <th>Product Name</th>
                        <th>Seller</th>
                        <th class="text-end">Price (Rs)</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Added On</th>
                        <th class="text-center" style="width: 160px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($products as $key => $product)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $key + 1 }}</td>

                        {{-- Product Name --}}
                        <td class="fw-bold text-dark">{{ $product->name }}</td>

                        {{-- Seller --}}
                        <td>{{ $product->seller->name ?? 'Unknown' }}</td>

                        {{-- Price --}}
                        <td class="text-end fw-bold">{{ number_format($product->price, 2) }}</td>

                        {{-- Stock --}}
                        <td class="text-center">
                            @if($product->stock < 5)
                                <span class="text-danger fw-bold">{{ $product->stock }}</span>
                            @else
                                {{ $product->stock }}
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="text-center">
                            @if ($product->status == 'approved' || $product->status == 'active')
                                <span class="badge bg-success badge-custom">Approved</span>
                            @elseif ($product->status == 'reapproval_pending')
                                <span class="badge bg-warning text-dark border border-dark badge-custom">Re-Approval Needed</span>
                            @elseif ($product->status == 'reapproved')
                                <span class="badge badge-custom" style="background-color:#20c997; color: white;">Re-Approved</span>
                            @elseif ($product->status == 'rejected')
                                <span class="badge bg-danger badge-custom">Rejected</span>
                            @else
                                <span class="badge bg-warning text-dark badge-custom">Pending</span>
                            @endif
                        </td>

                        {{-- Date --}}
                        <td class="small text-muted text-center">
                            {{ $product->created_at->format('Y-m-d') }}
                        </td>

                        {{-- ACTION BUTTONS --}}
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                {{-- View --}}
                                <a href="{{ route('admin.products.show', $product->id) }}"
                                   class="btn btn-outline-info"
                                   style="width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;"
                                   title="View">
                                    <i class="bi bi-eye"></i>
                                </a>

                                {{-- Approve --}}
                                @if($product->status !== 'approved' && $product->status !== 'reapproved')
                                    <form id="approve-form-{{ $product->id }}"
                                          action="{{ route('admin.products.approve', $product->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="button"
                                                class="btn btn-outline-success"
                                                style="width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;"
                                                onclick="confirmAction('approve', '{{ $product->id }}', '{{ $product->name }}')"
                                                title="Approve">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Reject --}}
                                @if($product->status !== 'rejected')
                                    <form id="reject-form-{{ $product->id }}"
                                          action="{{ route('admin.products.reject', $product->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="button"
                                                class="btn btn-outline-danger"
                                                style="width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;"
                                                onclick="confirmAction('reject', '{{ $product->id }}', '{{ $product->name }}')"
                                                title="Reject">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-box-seam display-6"></i>
                            <p class="mt-2">No products found for review.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script>
    // Real-time Search Logic
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('productSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll('#productTable tbody tr');

                rows.forEach(row => {
                    const productName = row.cells[1].textContent.toLowerCase();
                    const sellerName = row.cells[2].textContent.toLowerCase();

                    if (productName.includes(filter) || sellerName.includes(filter)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });
        }
    });

    // SweetAlert Action Confirmation
    function confirmAction(type, id, name) {
        let title = (type === 'approve') ? 'Approve Product?' : 'Reject Product?';
        let text = (type === 'approve')
            ? `Are you sure you want to approve "${name}"?`
            : `Are you sure you want to reject "${name}"?`;

        let formId = (type === 'approve')
            ? 'approve-form-' + id
            : 'reject-form-' + id;

        let color = (type === 'approve') ? '#198754' : '#dc3545';

        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: color,
            cancelButtonColor: '#6c757d',
            confirmButtonText: (type === 'approve') ? 'Yes, Approve' : 'Yes, Reject',
            reverseButtons: true
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }
</script>
@endsection