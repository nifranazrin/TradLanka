@extends('layouts.admin')

@section('content')
{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-dark mb-0">Review Products</h3>
    </div>

    {{-- REMOVED Bootstrap success/error alerts as you requested --}}
    
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Product Name</th>
                            <th>Seller</th>
                            <th>Price (Rs)</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Added On</th>
                            <th style="width: 160px;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($products as $key => $product)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>

                            {{-- Product Name --}}
                            <td class="fw-bold text-dark">{{ $product->name }}</td>

                            {{-- Seller --}}
                            <td>{{ $product->seller->name ?? 'Unknown' }}</td>

                            {{-- Price --}}
                            <td class="text-end">{{ number_format($product->price, 2) }}</td>

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
                                    <span class="badge bg-success">Approved</span>

                                @elseif ($product->status == 'reapproval_pending')
                                    <span class="badge bg-warning text-dark border border-dark">Re-Approval Needed</span>

                                @elseif ($product->status == 'reapproved')
                                    <span class="badge" style="background-color:#20c997;">Re-Approved</span>

                                @elseif ($product->status == 'rejected')
                                    <span class="badge bg-danger">Rejected</span>

                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
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
</div>

{{-- SweetAlert Confirm --}}
<script>
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
        confirmButtonText: (type === 'approve')
            ? 'Yes, Approve'
            : 'Yes, Reject',
        reverseButtons: true
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}
</script>
@endsection
