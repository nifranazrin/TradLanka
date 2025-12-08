@extends('layouts.admin')

@section('content')
<style>
    .page-title {
        font-weight: 700;
        color: #333;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .invite-card {
        background: #fff;
        border: 1px solid #eee;
        border-left: 4px solid #a81c1c;
        border-radius: 12px;
        padding: 20px 25px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
    }

    .invite-card h5 {
        font-weight: 600;
        color: #a81c1c;
        margin-bottom: 8px;
    }

    .invite-card p {
        color: #666;
        font-size: 14px;
        margin-bottom: 12px;
    }

    table thead th {
        background-color: #000 !important;
        color: #fff !important;
        font-weight: 600;
        vertical-align: middle;
        font-size: 14px;
    }

    table td {
        font-size: 14px;
        color: #222;
        vertical-align: middle;
    }

    .nic-thumb {
        width: 75px;
        height: 45px;
        border-radius: 6px;
        object-fit: cover;
        border: 1px solid #ddd;
        transition: 0.2s ease;
    }

    .nic-thumb:hover {
        transform: scale(1.1);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .status-badge {
        font-size: 13px;
        padding: 6px 10px;
        border-radius: 6px;
        font-weight: 500;
    }

    .status-pending { background-color: #fff3cd; color: #856404; }
    .status-approved { background-color: #d4edda; color: #155724; }
    .status-inactive { background-color: #f8d7da; color: #721c24; }
    .status-rejected { background-color: #f5c6cb; color: #842029; }

    .action-btn {
        border: none;
        font-size: 13px;
        font-weight: 500;
        padding: 6px 10px;
        border-radius: 6px;
        transition: 0.2s ease;
    }

    .btn-approve { background-color: #198754; color: #fff; }
    .btn-reject { background-color: #dc3545; color: #fff; }
    .btn-approve:hover { background-color: #157347; }
    .btn-reject:hover { background-color: #c82333; }
</style>

<div class="container py-4">

    {{-- Page Title --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            <i class="bi bi-people text-danger"></i> Seller Management
        </h4>
    </div>

    {{-- Invite Card --}}
    <div class="invite-card">
        <h5><i class="bi bi-person-plus-fill me-2"></i> Invite a New Seller</h5>
        <p>Share this registration link with a potential seller. Once they register, their request will appear below for review and approval.</p>

        <div class="input-group">
            <input id="sellerLink" type="text" class="form-control" readonly value="{{ route('seller.register') }}">
            <button id="copyButton" class="btn btn-outline-danger" type="button">
                <i class="bi bi-clipboard me-1"></i> Copy Link
            </button>
        </div>
        <small id="copyMessage" class="text-success mt-2 d-none">✅ Link copied to clipboard!</small>
    </div>

    {{-- Seller Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>NIC Number</th>
                            <th>NIC Image</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td class="text-center">{{ $req->id }}</td>
                                <td>{{ $req->name }}</td>
                                <td>{{ $req->email }}</td>
                                <td>{{ $req->phone }}</td>
                                <td>{{ $req->nic_number }}</td>
                                <td>
                                    @if($req->nic_image)
                                        <img src="{{ asset('storage/' . $req->nic_image) }}" alt="NIC Image" class="nic-thumb">
                                    @else
                                        <span class="text-muted small">No image</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($req->status === 'pending')
                                        <span class="status-badge status-pending">Pending</span>
                                    @elseif($req->status === 'approved')
                                        <span class="status-badge status-approved">Approved</span>
                                    @elseif($req->status === 'inactive')
                                        <span class="status-badge status-inactive">Inactive</span>
                                    @elseif($req->status === 'rejected')
                                        <span class="status-badge status-rejected">Rejected</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="text-center">
                                    {{-- 1. PENDING --}}
                                    @if($req->status === 'pending')
                                        <form action="{{ route('admin.seller.approve', $req->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="action-btn btn-approve"><i class="bi bi-check-circle me-1"></i> Approve</button>
                                        </form>
                                        <form action="{{ route('admin.seller.reject', $req->id) }}" method="POST" class="d-inline ms-1">
                                            @csrf
                                            <button class="action-btn btn-reject"><i class="bi bi-x-circle me-1"></i> Reject</button>
                                        </form>

                                    {{-- 2. APPROVED or INACTIVE --}}
                                    @elseif(in_array($req->status, ['approved', 'inactive']))
                                        <form action="{{ route('admin.seller.toggleStatus', $req->id) }}" method="POST" class="d-inline toggle-status-form">
                                            @csrf
                                            @method('PUT')
                                            @if($req->status === 'approved')
                                                <button type="submit" class="action-btn btn-reject" data-action="deactivate"><i class="bi bi-toggle-off me-1"></i> Deactivate</button>
                                            @else
                                                <button type="submit" class="action-btn btn-approve" data-action="activate"><i class="bi bi-toggle-on me-1"></i> Activate</button>
                                            @endif
                                        </form>

                                    {{-- 3. REJECTED --}}
                                    @elseif($req->status === 'rejected')
                                        {{-- I removed the 'onsubmit' and added class 'restore-form' --}}
                                        <form action="{{ route('admin.seller.restore', $req->id) }}" method="POST" class="restore-form">
                                            @csrf
                                            <button type="submit" class="action-btn btn-secondary text-white" style="background-color: #6c757d;" title="Restore to Pending">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                            </button>
                                        </form>

                                    {{-- 4. FALLBACK --}}
                                    @else
                                        <span class="text-muted">No actions</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No seller requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{--  Copy link Script --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const copyButton = document.getElementById('copyButton');
    const input = document.getElementById('sellerLink');
    const msg = document.getElementById('copyMessage');
    copyButton.addEventListener('click', function () {
        navigator.clipboard.writeText(input.value).then(() => {
            msg.classList.remove('d-none');
            setTimeout(() => msg.classList.add('d-none'), 1500);
        });
    });
});
</script>

{{--  SweetAlert2 Library --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{--  Approval Success Popup --}}
@if(session('seller_approved_data'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const data = @json(session('seller_approved_data'));

    Swal.fire({
        title: '✅ Seller approved successfully!',
        html: `
            <div style="text-align:left; line-height:1.4;">
                <p><b>Original Email:</b> ${escapeHtml(data.original_email)}</p>
                <p><b>Company Email:</b> ${escapeHtml(data.company_email)}</p>
                <p><b>Password:</b> ${escapeHtml(data.password)}</p>
                <button id="copyAllBtn" style="margin-top:8px;font-size:13px;padding:6px 12px;border:none;border-radius:6px;background-color:#198754;color:#fff;cursor:pointer;">
                    📋 Copy All
                </button>
            </div>
        `,
        icon: 'success',
        confirmButtonText: 'Close',
        allowOutsideClick: false,
        allowEscapeKey: true,
        didOpen: () => {
            const btn = document.getElementById('copyAllBtn');
            btn.addEventListener('click', () => {
                const combined = `Original Email: ${data.original_email}\nCompany Email: ${data.company_email}\nPassword: ${data.password}`;
                navigator.clipboard.writeText(combined).then(() => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'All details copied!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                });
            });
        }
    });

    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return String(unsafe)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
});
</script>
@endif

{{--  SWEETALERT LOGIC FOR BUTTONS (Activate, Deactivate, Restore) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // 1. Activate / Deactivate Confirmation
    document.querySelectorAll('.toggle-status-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const action = btn ? btn.getAttribute('data-action') : 'change';
            Swal.fire({
                title: `Are you sure?`,
                text: `Do you want to ${action} this seller?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: action === 'deactivate' ? '#dc3545' : '#198754',
                confirmButtonText: `Yes, ${action}`,
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // 2. Restore Confirmation (The fix for your popup issue)
    document.querySelectorAll('.restore-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Stop the ugly browser popup
            Swal.fire({
                title: 'Restore Seller?',
                text: "This will move the seller back to 'Pending' for re-evaluation.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Restore',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

});
</script>
@endsection