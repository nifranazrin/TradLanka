@extends('layouts.app')

@section('content')
<style>
    body { 
        background: url('{{ asset('storage/images/background.jpg') }}') no-repeat center center fixed; 
        background-size: cover;
        height: 100vh; 
        display: flex; 
        align-items: center; 
        justify-content: center;
        margin: 0;
    }
    .reset-card { 
        background: white; 
        padding: 40px; 
        border-radius: 20px; 
        box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
        width: 100%; 
        max-width: 450px; 
        border-top: 5px solid #5c0505; /* TradLanka Orange */
    }
    .btn-orange { 
        background-color: #770e0e; 
        color: white; 
        border: none; 
        padding: 12px; 
        border-radius: 10px; 
        font-weight: bold; 
        width: 100%; 
        transition: 0.3s; 
    }
    .btn-orange:hover { 
        background-color: #640707; 
        color: white;
    }
    .input-group-text {
        background: white;
        cursor: pointer;
        border-left: none;
        color: #6c757d;
    }
    .password-field {
        border-right: none;
    }
    .password-field:focus {
        border-color: #ced4da;
        box-shadow: none;
    }
</style>

<div class="reset-card">
    <div class="text-center mb-4">
        <h4 style="color: #7e0a0a; font-weight: 700;">Set New Password</h4>
        <p class="text-muted small">Enter your new credentials below to regain access.</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        {{-- Hidden Token Required by Laravel --}}
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- Email Field (Readonly for security) --}}
        <div class="mb-3 text-start">
            <label class="form-label fw-bold small">Email Address</label>
            <input type="email" name="email" value="{{ $email ?? old('email') }}" 
                   class="form-control bg-light @error('email') is-invalid @enderror" readonly>
            @error('email')
                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        {{-- New Password Field --}}
        <div class="mb-3 text-start">
            <label class="form-label fw-bold small">New Password</label>
            <div class="input-group">
                <input id="password" type="password" name="password" 
                       class="form-control password-field @error('password') is-invalid @enderror" 
                       required autocomplete="new-password" placeholder="Min. 8 characters">
                <span class="input-group-text" onclick="toggleVisibility('password', 'eye1')">
                    <i class="bi bi-eye-slash" id="eye1"></i>
                </span>
            </div>
            @error('password')
                <span class="text-danger small" style="font-size: 0.75rem;"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        {{-- Confirm Password Field --}}
        <div class="mb-4 text-start">
            <label class="form-label fw-bold small">Confirm New Password</label>
            <div class="input-group">
                <input id="password-confirm" type="password" name="password_confirmation" 
                       class="form-control password-field" required autocomplete="new-password" 
                       placeholder="Repeat new password">
                <span class="input-group-text" onclick="toggleVisibility('password-confirm', 'eye2')">
                    <i class="bi bi-eye-slash" id="eye2"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-orange">
            <i class="bi bi-shield-check me-2"></i>Reset Password
        </button>
    </form>
</div>

<script>
    /**
     * Toggles password visibility and switches eye icons
     */
    function toggleVisibility(inputId, eyeId) {
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
</script>
@endsection