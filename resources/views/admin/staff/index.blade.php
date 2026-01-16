@extends('layouts.admin')

@section('content')

<style>
    /* Professional Maroon Header */
    .maroon-header {
        background-color: #800000 !important; /* Deep Maroon */
    }

    .maroon-header th {
        background-color: #3b0b0b !important; /* Forces background color on cells */
        color: white !important;
        padding: 15px !important;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 13px;
        border: none !important;
    }

    /* Table Container Shadow */
    .custom-shadow-table {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important; 
        border-radius: 12px;
        overflow: hidden;
        background: white;
        border: 1px solid #f0f0f0;
    }

   /* Container for the search bar */
.search-container {
    max-width: 450px;
    margin-bottom: 25px;
}

/* Remove the gap and fix the borders */
.custom-search-group {
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid #e0e0e0; /* Default gray border */
    transition: all 0.3s ease;
}

/* Style for the icon box */
.search-icon-box {
    background-color: white !important;
    border: none !important;
    padding-right: 5px !important;
    color: #666;
}

/* Style for the input text field */
.search-input-field {
    border: none !important;
    box-shadow: none !important;
    padding: 12px 10px !important;
}

/* THE IMPORTANT PART: Change whole group border to maroon on focus */
.custom-search-group:focus-within {
    border-color: #800000 !important; /* Maroon border */
    box-shadow: 0 0 0 4px rgba(128, 0, 0, 0.1) !important;
}

.badge-role {
    display: inline-block;
    padding: 4px 12px !important;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    border-radius: 50px !important; /* Makes it a pill shape */
    color: #fff !important;
    text-decoration: none;
    border: none !important;
}

.id-thumb {
    transition: transform 0.2s;
    cursor: zoom-in;
}
.id-thumb:hover {
    transform: scale(2.5);
    z-index: 100;
    position: relative;
}

.bg-primary.badge-role { background-color: #6f00ff !important; }
.bg-info.badge-role { background-color: #17a2b8 !important; color: #fff !important; }
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            <i class="bi bi-person-badge-fill" style="color: #800000;"></i> Staff Management
        </h4>
    </div>

    <div class="search-container">
    <div class="input-group custom-search-group">
        <span class="input-group-text search-icon-box">
            <i class="bi bi-search"></i>
        </span>
        <input type="text" id="staffSearch" class="form-control search-input-field" 
               placeholder="Search by Name, Email, or Staff ID...">
    </div>
</div>

    {{-- Fixed the "=" sign that was here --}}
    <div class="custom-shadow-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="staffTable">
                <thead>
                    <tr class="maroon-header">
                        <th class="text-center py-3">ID</th>
                        <th class="py-3">Name</th>
                        <th class="py-3">Role</th>
                        <th class="py-3">Company Email</th>
                        <th class="py-3">Phone Number</th>
                        <th class="py-3">NIC Number</th>
                        <th class="text-center py-3">ID Image</th>
                        <th class="py-3">Address</th>
                        <th class="text-center py-3">Status</th>
                        <th class="text-center py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                  @foreach($staff as $member)
    <tr>
        <td class="text-center fw-bold">#{{ $member->id }}</td>
        <td class="fw-bold text-dark">{{ $member->name }}</td>
        <td>
            <span class="badge-role {{ $member->role == 'delivery' ? 'bg-primary text-white' : 'bg-info text-dark' }}">
                {{ strtoupper($member->role) }}
            </span>
        </td>
        <td>{{ $member->email }}</td>
        <td>{{ $member->phone }}</td>
        <td class="text-secondary">{{ $member->nic_number ?? 'N/A' }}</td>

        
        <td class="text-center">
            @if($member->id_image)
                <div class="position-relative d-inline-block">
                    <img src="{{ asset('storage/' . $member->id_image) }}" 
                         alt="ID Proof" 
                         class="rounded border shadow-sm"
                         style="width: 60px; height: 40px; object-fit: cover; cursor: pointer;"
                         onclick="window.open(this.src, '_blank')">
                </div>
            @else
                <span class="text-muted small">No Image</span>
            @endif
        </td>

        <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            {{ $member->address ?? 'N/A' }}
        </td>

        <td class="text-center">
            <span class="badge {{ $member->status == 'active' ? 'bg-success' : 'bg-danger' }} rounded-pill">
                {{ ucfirst($member->status) }}
            </span>
        </td>
        <td class="text-center">
            <form action="{{ route('admin.staff.toggle', $member->id) }}" method="POST" class="toggle-staff-form">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-sm {{ $member->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                        data-action="{{ $member->status === 'active' ? 'inactivate' : 'activate' }}">
                    <i class="bi {{ $member->status === 'active' ? 'bi-person-x' : 'bi-person-check' }}"></i>
                    {{ $member->status === 'active' ? 'Inactivate' : 'Activate' }}
                </button>
            </form>
        </td>
    </tr>
@endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Success Message Alert
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Updated!',
            text: "{{ session('success') }}",
            timer: 2000,
            showConfirmButton: false
        });
    @endif

    // 2. Real-time Search Logic
    const searchInput = document.getElementById('staffSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#staffTable tbody tr');

            rows.forEach(row => {
                const id = row.cells[0].textContent.toLowerCase();
                const name = row.cells[1].textContent.toLowerCase();
                const email = row.cells[3].textContent.toLowerCase();

                if (name.includes(filter) || email.includes(filter) || id.includes(filter)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    }

    // 3. Status Toggle Confirmation
    document.querySelectorAll('.toggle-staff-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const button = form.querySelector('button');
            const action = button.getAttribute('data-action');
            
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to ${action} this staff member?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: (action === 'inactivate') ? '#dc3545' : '#198754',
                confirmButtonText: `Yes, ${action}!`
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection