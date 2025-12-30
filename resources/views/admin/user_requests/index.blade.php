@extends('layouts.admin')

@section('content')

<style>
    /* General Page Title Styling */
    .page-title {
        font-weight: 700;
        color: #333;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Invite Card UI - Linked to Maroon Theme */
    .invite-card {
        background: #fff;
        border: 1px solid #eee;
        border-left: 5px solid #800000; /* Deep Maroon accent */
        border-radius: 12px;
        padding: 20px 25px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
    }

    .invite-card h5 {
        font-weight: 600;
        color: #800000; 
        margin-bottom: 8px;
    }

    .invite-card p {
        color: #666;
        font-size: 14px;
        margin-bottom: 12px;
    }

    /* Professional Table Wrapper with Rounded Corners & Shadow */
    .custom-shadow-table {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important; 
        border-radius: 12px !important; 
        overflow: hidden !important; /* Critical to clip the maroon header to the rounded corners */
        background: white;
        border: 1px solid #f0f0f0;
        margin-top: 5px;
    }

    /* Maroon Table Header Styling */
    table thead th {
        background-color: #420808 !important; /* Unified Deep Maroon */
        color: #fff !important;
        font-weight: 600;
        vertical-align: middle;
        font-size: 14px;
        padding: 15px !important;
        border: none !important;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Fix for individual corner shaping if needed by the browser */
    table thead tr:first-child th:first-child { border-top-left-radius: 12px !important; }
    table thead tr:first-child th:last-child { border-top-right-radius: 12px !important; }

    table td {
        font-size: 14px;
        color: #222;
        vertical-align: middle;
        padding: 12px 15px !important;
        border-bottom: 1px solid #f8f9fa;
    }

    /* NIC Thumbnail Preview */
    .nic-thumb {
        width: 75px;
        height: 45px;
        border-radius: 6px;
        object-fit: cover;
        border: 1px solid #ddd;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }

    .nic-thumb:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Status Badges */
    .status-badge {
        font-size: 12px;
        padding: 5px 12px;
        border-radius: 6px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }

    .status-pending  { background-color: #fff3cd; color: #856404; }
    .status-approved, .status-active { background-color: #d4edda; color: #155724; }
    .status-rejected, .status-inactive { background-color: #f8d7da; color: #721c24; }

    /* Role Badges - Pill Style */
    .role-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 50px; 
        text-transform: uppercase;
    }

    .role-delivery { background-color: #e3f2fd; color: #0d47a1; }
    .role-seller   { background-color: #f3e5f5; color: #4a148c; }

    /* Action Buttons */
    .action-btn {
        border: none;
        font-size: 13px;
        font-weight: 600;
        padding: 8px 14px;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-approve { background-color: #198754; color: #fff; }
    .btn-reject  { background-color: #dc3545; color: #fff; }
    .btn-restore { background-color: #6c757d; color: #fff; }

    .btn-approve:hover { background-color: #157347; transform: translateY(-1px); }
    .btn-reject:hover  { background-color: #c82333; transform: translateY(-1px); }
    .btn-restore:hover { background-color: #5a6268; transform: translateY(-1px); }

    /* Search Bar UI with Maroon Focus */
    #staffSearch, #categorySearch, #productSearch {
        border-radius: 10px;
        padding: 10px 15px;
        border: 1px solid #ddd;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    #staffSearch:focus, #categorySearch:focus, #productSearch:focus {
        border-color: #800000 !important;
        box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1) !important;
        outline: none;
    }
</style>
<div class="container py-4">

    {{-- Page Title --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            <i class="bi bi-people-fill text-danger"></i> STAFF MANAGEMENT
        </h4>
    </div>

    {{-- Invite Card --}}
    <div class="invite-card">
        <h5><i class="bi bi-person-plus-fill me-2"></i> Invite New Staff</h5>
        <p>Share this registration link with potential sellers or delivery personnel. Once they register, their application will appear below for review.</p>

        <div class="input-group">
            <input id="regLink" type="text" class="form-control" readonly value="{{ route('seller.register') }}">
            <button id="copyButton" class="btn btn-outline-danger" type="button">
                <i class="bi bi-clipboard me-1"></i> Copy Link
            </button>
        </div>
        <small id="copyMessage" class="text-success mt-2 d-none">✅ Link copied to clipboard!</small>
    </div>

    {{-- User Table --}}
    <div class="custom-shadow-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="staffTable">
                <thead>
                    <tr class="maroon-header">
                        <th class="text-center py-3">ID</th>
                        <th class="py-3">Name</th>
                        <th class="py-3">Position</th>
                        <th class="py-3">Email</th>
                        <th class="py-3">Phone</th>
                        <th class="py-3">NIC Number</th>
                        <th class="py-3">NIC Image</th>
                        <th class="text-center py-3">Status</th>
                        <th class="text-center py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td class="text-center">{{ $req->id }}</td>
                            <td class="fw-bold">{{ $req->name }}</td>
                            <td>
                                @if($req->role === 'delivery')
                                    <span class="role-badge bg-primary text-white">Delivery</span>
                                @else
                                    <span class="role-badge bg-info text-dark">Seller</span>
                                @endif
                            </td>
                            <td>{{ $req->email }}</td>
                            <td>{{ $req->phone }}</td>
                            <td>{{ $req->nic_number }}</td>
                            <td>
                                @if($req->nic_image)
                                    <a href="{{ asset('storage/' . $req->nic_image) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $req->nic_image) }}" alt="NIC Image" class="nic-thumb">
                                    </a>
                                @else
                                    <span class="text-muted small">No image</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="status-badge status-{{ $req->status }}">
                                    {{ ucfirst($req->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($req->status === 'pending')
                                    {{-- Approve/Reject for new applications --}}
                                    <form action="{{ route('admin.seller.approve', $req->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="action-btn btn-approve">
                                            <i class="bi bi-check-circle me-1"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.seller.reject', $req->id) }}" method="POST" class="d-inline ms-1">
                                        @csrf
                                        <button type="submit" class="action-btn btn-reject">
                                            <i class="bi bi-x-circle me-1"></i> Reject
                                        </button>
                                    </form>

                                @elseif($req->status === 'rejected')
                                    {{-- Restore for rejected applications --}}
                                    <form action="{{ route('admin.seller.restore', $req->id) }}" method="POST" class="restore-form">
                                        @csrf
                                        <button type="submit" class="action-btn btn-secondary text-white" style="background-color: #6c757d;">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No pending applications found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


{{-- 1. Invite Link Copy Logic --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const copyButton = document.getElementById('copyButton');
    const input = document.getElementById('regLink');
    const msg = document.getElementById('copyMessage');
    
    if (copyButton) {
        copyButton.addEventListener('click', function () {
            navigator.clipboard.writeText(input.value).then(() => {
                msg.classList.remove('d-none');
                setTimeout(() => msg.classList.add('d-none'), 1500);
            });
        });
    }
});
</script>

{{-- 2. Success Popup with Enhanced Credential Display --}}
@if(session('seller_approved_data'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const data = @json(session('seller_approved_data'));

    Swal.fire({
        title: '<span style="color:#198754;">✅ Staff Approved Successfully!</span>',
        html: `
            <div style="text-align:left; line-height:1.8; font-size:14.5px; padding:10px; border-radius:10px; background:#f8f9fa; border:1px solid #e9ecef;">
                <p style="margin-bottom:8px;"><i class="bi bi-person-badge me-2" style="color:#6f42c1;"></i><b>Assigned Role:</b> <span class="badge bg-dark px-2 py-1">${data.role.toUpperCase()}</span></p>
                <p style="margin-bottom:8px;"><i class="bi bi-envelope me-2" style="color:#0dcaf0;"></i><b>Personal Email:</b> <span style="color:#495057;">${data.original_email}</span></p>
                
                <div style="margin:15px 0; border-top:2px dashed #dee2e6;"></div>
                
                <p style="margin-bottom:8px;"><strong>Official Credentials:</strong></p>
                <div style="background:white; padding:12px; border-radius:8px; border:1px solid #ff8a00;">
                    <p style="margin-bottom:5px;"><b>Company Email:</b> <br><code style="color:#ff8a00; font-size:15px; font-weight:bold;">${data.company_email}</code></p>
                    <p style="margin-bottom:0;"><b>Generated Password:</b> <br><code style="color:#ff8a00; font-size:15px; font-weight:bold;">${data.password}</code></p>
                </div>

                <div class="mt-3">
                    <button id="sendEmailBtn" style="width:100%; padding:10px; border:none; border-radius:8px; background-color:#6f42c1; color:#fff; font-weight:bold; transition: 0.3s; margin-bottom:8px;">
                        <i class="bi bi-send-check me-2"></i>Send to Staff Email
                    </button>
                    <button id="copyAllBtn" style="width:100%; padding:10px; border:none; border-radius:8px; background-color:#3b82f6; color:#fff; font-weight:bold; transition: 0.3s;">
                        <i class="bi bi-clipboard-check me-2"></i>Copy All Credentials
                    </button>
                </div>
            </div>
            <p style="font-size:12px; color:#6c757d; margin-top:15px; text-align:center;">Best Regards, TradLanka Admin</p>
        `,
        showConfirmButton: true,
        confirmButtonText: 'Done',
        confirmButtonColor: '#1e293b',
        width: '450px'
    });

    // Integrated Email Sending Logic
    document.getElementById('sendEmailBtn')?.addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
        
        // Add Best Regards to the data object before sending
        const emailData = { 
            ...data, 
            closing: "Best Regards, TradLanka Admin" 
        };

        fetch("{{ route('admin.staff.sendEmail') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(emailData)
        })
        .then(response => response.json())
        .then(res => {
            if(res.success) {
                Swal.showValidationMessage('✅ Email sent successfully!');
                btn.innerHTML = '<i class="bi bi-check-all me-2"></i>Email Sent!';
                btn.style.backgroundColor = '#198754';
            } else {
                Swal.showValidationMessage('❌ Failed: ' + res.message);
                btn.disabled = false;
                btn.innerText = 'Try Again';
            }
        })
        .catch(err => {
            Swal.showValidationMessage('❌ Network Error');
            btn.disabled = false;
            btn.innerText = 'Try Again';
        });
    });

    // Enhanced Copy Logic
    document.getElementById('copyAllBtn')?.addEventListener('click', function() {
        const btn = this;
        const textToCopy = `TradLanka Official Credentials\n--------------------------\nRole: ${data.role.toUpperCase()}\nCompany Email: ${data.company_email}\nPassword: ${data.password}\n\nBest Regards, TradLanka Admin`;
        
        navigator.clipboard.writeText(textToCopy).then(() => {
            const originalColor = btn.style.backgroundColor;
            const originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Copied!';
            btn.style.backgroundColor = '#198754';
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.backgroundColor = originalColor;
            }, 2000);
        });
    });
});
</script>
@endif

{{-- 3. Restore Confirmation Logic (Cleaned of Toggle Logic) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Handle Rejected Request Restore
    document.querySelectorAll('.restore-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Restore Application?',
                text: "Move this user back to 'Pending'?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Restore'
            }).then(r => r.isConfirmed && form.submit());
        });
    });
});
</script>
@endsection