@extends('layouts.app')

@section('content')
<style>
    body { background: #fdf2e9; height: 100vh; display: flex; align-items: center; justify-content: center; }
    .reset-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 450px; border-top: 5px solid #c06b00; }
    .btn-orange { background-color: #c06b00; color: white; border: none; padding: 12px; border-radius: 10px; font-weight: bold; width: 100%; transition: 0.3s; }
    .btn-orange:hover { background-color: #a05900; color: white; transform: translateY(-1px); }
    .form-control:focus { border-color: #c06b00; box-shadow: 0 0 0 0.2rem rgba(192, 107, 0, 0.25); }
    .input-group-text { background: white; border-left: none; cursor: pointer; color: #666; }
    .password-field { border-right: none; }
</style>

<div class="reset-card">
    <div class="text-center mb-4">
        <h4 style="color: #444; font-weight: 800;">Set New Password</h4>
        <p class="text-muted small">Choose a secure password for your staff account.</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        {{-- Hidden Reset Token --}}
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- Email Address (Readonly) --}}
        <div class="mb-3">
            <label class="form-label fw-bold small">Email Address</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                   value="{{ $email ?? old('email') }}" required readonly>
            @error('email')
                <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        {{-- New Password with Eye Toggle --}}
        <div class="mb-3">
            <label class="form-label fw-bold small">New Password</label>
            <div class="input-group">
                <input id="password" type="password" name="password" 
                       class="form-control password-field @error('password') is-invalid @enderror" 
                       placeholder="At least 8 characters" required autofocus>
                <span class="input-group-text" onclick="toggleVisibility('password', 'eye1')">
                    <i class="bi bi-eye-slash" id="eye1"></i>
                </span>
                @error('password')
                    <span class="invalid-feedback" style="display:block;"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>

        {{-- Confirm Password with Eye Toggle --}}
        <div class="mb-4">
            <label class="form-label fw-bold small">Confirm Password</label>
            <div class="input-group">
                <input id="password-confirm" type="password" name="password_confirmation" 
                       class="form-control password-field" placeholder="Repeat new password" required>
                <span class="input-group-text" onclick="toggleVisibility('password-confirm', 'eye2')">
                    <i class="bi bi-eye-slash" id="eye2"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-orange">
            <i class="bi bi-shield-lock me-2"></i>Reset Password
        </button>
    </form>
</div>

<script>
    /**
     * Toggles visibility of password inputs
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