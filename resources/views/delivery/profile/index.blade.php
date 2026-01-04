@extends('layouts.delivery')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom">
            <h4 class="mb-0">
                <i class="bi bi-person-circle me-2 text-maroon"></i> Delivery Profile
            </h4>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form id="riderProfileForm" action="{{ route('delivery.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row align-items-start">
                    <div class="col-md-3 text-center mb-4">
                         <img id="preview" src="{{ $rider->image ? asset('storage/' . $rider->image) : asset('images/default-user.png') }}"
                             class="rounded-circle shadow-sm mb-3" width="150" height="150">
                        <div>
                            <label for="image" class="btn btn-outline-maroon btn-sm">Change Photo</label>
                            <input id="image" type="file" name="image" class="d-none" accept="image/*">
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $rider->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $rider->email) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contact Number</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $rider->phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Address</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $rider->address) }}">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-outline-dark btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#changePasswordSection">
                                <i class="bi bi-key me-1"></i> Change Password
                            </button>
                        </div>

                        <div class="collapse mt-3" id="changePasswordSection">
                            <div class="card card-body border-0 bg-light">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Current Password</label>
                                        <div class="input-group">
                                            <input type="password" id="currentPassword" name="current_password" class="form-control" placeholder="Enter current password">
                                            <span id="passwordStatus" class="input-group-text bg-white d-none"></span>
                                        </div>
                                        <div id="passwordError" class="text-danger small mt-1 d-none"></div>
                                    </div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">New Password</label><input type="password" id="newPassword" name="new_password" class="form-control"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Confirm Password</label><input type="password" id="confirmPassword" name="new_password_confirmation" class="form-control"></div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-maroon px-4"><i class="bi bi-save me-1"></i> Save Changes</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('image').addEventListener('change', function (e) {
    const [file] = e.target.files;
    if (file) document.getElementById('preview').src = URL.createObjectURL(file);
});

// ✅ AJAX Password Validation (Adapted for Delivery)
document.getElementById('currentPassword').addEventListener('input', function() {
    const enteredPassword = this.value.trim();
    const status = document.getElementById('passwordStatus');
    const error = document.getElementById('passwordError');

    if (enteredPassword.length < 3) return;

    fetch("{{ route('delivery.check-password') }}", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        body: JSON.stringify({ password: enteredPassword })
    })
    .then(res => res.json())
    .then(data => {
        status.classList.remove('d-none');
        if (data.valid) {
            error.classList.add('d-none');
            status.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
        } else {
            error.classList.remove('d-none');
            error.textContent = "Incorrect current password!";
            status.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
        }
    });
});
</script>

<style>
.text-maroon { color: #800000 !important; }
.btn-maroon { background-color: #800000; color: #fff; border: none; }
.btn-maroon:hover { background-color: #5e0000; color: #fff; }
.btn-outline-maroon { border: 1px solid #800000; color: #800000; }
</style>
@endsection