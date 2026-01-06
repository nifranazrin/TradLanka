@extends('layouts.admin')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom">
            <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-person-circle me-2 text-maroon"></i> Admin Profile</h4>
        </div>

        <div class="card-body p-4">
            <form id="adminProfileForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-3 d-flex flex-column align-items-center mb-4 border-end">
                        <img id="preview" src="{{ $admin->image ? asset('storage/'.$admin->image) : asset('images/default-user.png') }}" 
                             class="rounded-circle shadow-sm border mb-3" width="150" height="150" style="object-fit: cover;">
                        
                        <label for="image" class="btn btn-outline-maroon btn-sm rounded-pill px-3">Change Photo</label>
                        <input type="file" name="image" id="image" class="d-none" accept="image/*">
                    </div>

                    <div class="col-md-9 ps-md-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Full Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $admin->name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ $admin->email }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Contact Number</label>
                                <input type="text" name="phone" class="form-control" value="{{ $admin->phone }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Address</label>
                                <input type="text" name="address" class="form-control" value="{{ $admin->address }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <button class="btn btn-outline-dark btn-sm rounded-1" type="button" onclick="togglePassSection()">
                                <i class="bi bi-key me-1"></i> Change Password
                            </button>
                        </div>

                        <div id="changePasswordSection" style="display: none;" class="p-4 rounded border bg-light shadow-sm">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold small">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" id="curPass" name="current_password" class="form-control" placeholder="Verify identity">
                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleEye('curPass', 'eye1')">
                                            <i class="bi bi-eye-slash" id="eye1"></i>
                                        </button>
                                        <span id="passIcon" class="input-group-text bg-white d-none"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">New Password</label>
                                    <div class="input-group">
                                        <input type="password" id="newPass" name="new_password" class="form-control" disabled>
                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleEye('newPass', 'eye2')">
                                            <i class="bi bi-eye-slash" id="eye2"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" id="confirmPass" name="new_password_confirmation" class="form-control" disabled>
                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleEye('confirmPass', 'eye3')">
                                            <i class="bi bi-eye-slash" id="eye3"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-5">
                            <button type="submit" class="btn btn-maroon px-5 py-2 shadow rounded-3">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Eye Toggle Logic
function toggleEye(inputId, eyeId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(eyeId);
    if (input.type === "password") {
        input.type = "text";
        eye.classList.replace("bi-eye-slash", "bi-eye");
    } else {
        input.type = "password";
        eye.classList.replace("bi-eye", "bi-eye-slash");
    }
}

function togglePassSection() {
    var section = document.getElementById("changePasswordSection");
    section.style.display = (section.style.display === "none") ? "block" : "none";
}

// Image Preview
document.getElementById('image').addEventListener('change', function(e) {
    const [file] = e.target.files;
    if (file) document.getElementById('preview').src = URL.createObjectURL(file);
});

// Locked Password Verification Logic
document.getElementById('curPass').addEventListener('input', function() {
    const icon = document.getElementById('passIcon');
    const newP = document.getElementById('newPass');
    const confP = document.getElementById('confirmPass');
    if (this.value.length < 3) { icon.classList.add('d-none'); return; }

    fetch("{{ route('admin.check-password') }}", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        body: JSON.stringify({ password: this.value })
    })
    .then(res => res.json())
    .then(data => {
        icon.classList.remove('d-none');
        if (data.valid) {
            icon.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
            newP.disabled = false; confP.disabled = false;
        } else {
            icon.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
            newP.disabled = true; confP.disabled = true;
        }
    });
});

// Final AJAX Submit with SweetAlert
document.getElementById('adminProfileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);

    fetch("{{ route('admin.profile.update') }}", {
        method: "POST",
        body: formData,
        headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: data.message,
                confirmButtonColor: '#800000'
            }).then(() => location.reload());
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    });
});
</script>

<style>
.text-maroon { color: #800000 !important; }
.btn-maroon { background-color: #800000; color: #fff; border: none; }
.btn-maroon:hover { background-color: #5e0000; }
.btn-outline-maroon { border: 1px solid #800000; color: #800000; }
.btn-outline-maroon:hover { background-color: #800000; color: #fff; }
</style>
@endsection