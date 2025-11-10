@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <h4 class="fw-bold mb-4">Seller Approvals</h4>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #5a1a1a; color: #fff;">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>NIC Number</th>
                        <th>NIC Image</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td>{{ $req->id }}</td>
                            <td>{{ $req->name }}</td>
                            <td>{{ $req->email }}</td>
                            <td>{{ $req->phone }}</td>
                            <td>{{ $req->nic_number }}</td>
                            <td>
                                @if ($req->nic_image)
                                    <a href="{{ asset('storage/' . $req->nic_image) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $req->nic_image) }}" alt="NIC" width="70" height="50" class="rounded shadow-sm border">
                                    </a>
                                @else
                                    <span class="text-muted">No Image</span>
                                @endif
                            </td>
                            <td>
                                @if ($req->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif ($req->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($req->status === 'pending')
                                    <div class="d-flex justify-content-center gap-2">
                                        {{-- Approve --}}
                                        <form id="approveForm-{{ $req->id }}" action="{{ route('admin.seller.approve', $req->id) }}" method="POST">
                                            @csrf
                                            <button type="button" class="btn btn-success btn-sm px-3" onclick="confirmApprove({{ $req->id }})">
                                                <i class="bi bi-check-circle me-1"></i> Approve
                                            </button>
                                        </form>

                                        {{--  Reject --}}
                                        <form id="rejectForm-{{ $req->id }}" action="{{ route('admin.seller.reject', $req->id) }}" method="POST">
                                            @csrf
                                            <button type="button" class="btn btn-danger btn-sm px-3" onclick="confirmReject({{ $req->id }})">
                                                <i class="bi bi-x-circle me-1"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                @elseif ($req->status === 'approved')
                                    <span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Approved</span>
                                @elseif ($req->status === 'rejected')
                                    <span class="text-danger fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No seller requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{--  SweetAlert confirmation scripts --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmApprove(id) {
    Swal.fire({
        title: 'Approve Seller?',
        text: "Are you sure you want to approve this seller?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Approve'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`approveForm-${id}`).submit();
        }
    });
}

function confirmReject(id) {
    Swal.fire({
        title: 'Reject Seller?',
        text: "Are you sure you want to reject this seller?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#b91c1c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Reject'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`rejectForm-${id}`).submit();
        }
    });
}
</script>
@endpush

<style>
.badge { font-size: 0.85rem; }
.btn-success { background-color: #198754; border: none; }
.btn-success:hover { background-color: #157347; }
.btn-danger { background-color: #b91c1c; border: none; }
.btn-danger:hover { background-color: #991b1b; }
</style>
@endsection
