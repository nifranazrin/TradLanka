@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom">
            <h4 class="mb-0">
                <i class="bi bi-person-circle me-2 text-danger"></i> Admin Profile
            </h4>
        </div>

        <div class="card-body">
            {{--  Success / Error Alerts --}}
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

            {{--  Profile Update Form --}}
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row align-items-start">
                    {{-- Left Column: Profile Image --}}
                    <div class="col-md-3 text-center mb-4">
                        <img 
                            id="preview"
                            src="{{ $admin->image ? asset('storage/' . $admin->image) : 'https://via.placeholder.com/150' }}" 
                            alt="Profile Image" 
                            class="rounded-circle shadow-sm mb-3" 
                            width="150" height="150"
                        >
                        <div>
                            <label for="image" class="btn btn-outline-secondary btn-sm">Change Photo</label>
                            <input id="image" type="file" name="image" class="d-none" accept="image/*">
                        </div>
                        @error('image') 
                            <div class="text-danger small mt-1">{{ $message }}</div> 
                        @enderror
                    </div>

                    {{--  Right Column: Profile Details --}}
                    <div class="col-md-9">
                        <div class="row g-3">
                            {{-- Full Name --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" 
                                       name="name" 
                                       class="form-control" 
                                       value="{{ old('name', $admin->name) }}" 
                                       required>
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control" 
                                       value="{{ old('email', $admin->email) }}" 
                                       required>
                            </div>

                            {{-- Phone --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" 
                                       name="phone" 
                                       class="form-control" 
                                       value="{{ old('phone', $admin->phone) }}">
                            </div>
                        </div>

                        {{--  Toggle Password Section --}}
                        <div class="mt-4">
                            <button class="btn btn-outline-dark btn-sm" type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#changePasswordSection"
                                    aria-expanded="false"
                                    aria-controls="changePasswordSection">
                                <i class="bi bi-key me-1"></i> Change Password
                            </button>
                        </div>

                        {{--  Hidden Password Section --}}
                        <div class="collapse mt-3" id="changePasswordSection">
                            <div class="card card-body border-0 bg-light">
                                <div class="row g-3">
                                    {{-- Current Password --}}
                                    <div class="col-md-6 position-relative">
                                        <label class="form-label fw-semibold">Current Password</label>
                                        <div class="input-group">
                                            <input type="password" 
                                                   id="currentPassword" 
                                                   name="current_password" 
                                                   class="form-control" 
                                                   placeholder="Enter current password">
                                            <span id="passwordStatus" class="input-group-text bg-white d-none">
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            </span>
                                        </div>
                                        <div id="passwordError" class="text-danger small mt-1 d-none"></div>
                                    </div>

                                    {{-- New Password --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">New Password</label>
                                        <input type="password" 
                                               id="newPassword" 
                                               name="password" 
                                               class="form-control" 
                                               placeholder="Enter new password" 
                                               disabled>
                                    </div>

                                    {{-- Confirm Password --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Confirm Password</label>
                                        <input type="password" 
                                               id="confirmPassword" 
                                               name="password_confirmation" 
                                               class="form-control" 
                                               placeholder="Confirm new password" 
                                               disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{--  Save Button --}}
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{--  JavaScript --}}
<script>
document.getElementById('image').addEventListener('change', function (e) {
    const [file] = e.target.files;
    if (file) document.getElementById('preview').src = URL.createObjectURL(file);
});

const currentPassInput = document.getElementById('currentPassword');
const newPassInput = document.getElementById('newPassword');
const confirmPassInput = document.getElementById('confirmPassword');
const passwordError = document.getElementById('passwordError');
const passwordStatus = document.getElementById('passwordStatus');

// Initially disable new and confirm fields
newPassInput.disabled = true;
confirmPassInput.disabled = true;
passwordError.classList.add('d-none');
passwordStatus.classList.add('d-none');

// t password validity
currentPassInput.addEventListener('input', function() {
    const enteredPassword = this.value.trim();

    // Reset icons
    passwordStatus.classList.add('d-none');
    passwordStatus.innerHTML = '';

    if (enteredPassword.length < 3) {
        passwordError.classList.add('d-none');
        newPassInput.disabled = true;
        confirmPassInput.disabled = true;
        return;
    }

    fetch("{{ route('admin.check-password') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ password: enteredPassword })
    })
    .then(res => res.json())
    .then(data => {
        if (data.valid) {
            passwordError.classList.add('d-none');
            newPassInput.disabled = false;      // enable new password only
            confirmPassInput.disabled = true;   // keep confirm disabled

            //  check
            passwordStatus.classList.remove('d-none');
            passwordStatus.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
        } else {
            passwordError.classList.remove('d-none');
            passwordError.textContent = "Incorrect current password!";
            newPassInput.disabled = true;
            confirmPassInput.disabled = true;

            //  red cross
            passwordStatus.classList.remove('d-none');
            passwordStatus.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
        }
    })
    .catch(() => {
        passwordError.textContent = "Error checking password.";
        passwordError.classList.remove('d-none');
        newPassInput.disabled = true;
        confirmPassInput.disabled = true;
    });
});

//  confirm password only after typing new one
newPassInput.addEventListener('input', function() {
    const v = this.value.trim();
    if (v.length >= 6) {
        confirmPassInput.disabled = false;
    } else {
        confirmPassInput.disabled = true;
    }
});
</script>
@endsection
