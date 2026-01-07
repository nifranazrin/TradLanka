@extends('layouts.app')

@section('content')
<style>
    body { background: #fdf2e9; height: 100vh; display: flex; align-items: center; justify-content: center; }
    .confirm-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 450px; border-top: 5px solid #c06b00; }
    .btn-orange { background-color: #c06b00; color: white; border: none; padding: 12px; border-radius: 10px; font-weight: bold; width: 100%; transition: 0.3s; }
    .btn-orange:hover { background-color: #a05900; color: white; transform: translateY(-1px); }
    .form-control:focus { border-color: #c06b00; box-shadow: 0 0 0 0.2rem rgba(192, 107, 0, 0.25); }
    .input-group-text { background: white; border-left: none; cursor: pointer; color: #666; }
    .password-field { border-right: none; }
</style>

<div class="confirm-card">
    <div class="text-center mb-4">
        <h4 style="color: #444; font-weight: 800;">Confirm Identity</h4>
        <p class="text-muted small">Please confirm your password before continuing to the requested page.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
            <label for="password" class="form-label fw-bold small">Current Password</label>
            <div class="input-group">
                <input id="password" type="password" 
                       class="form-control password-field @error('password') is-invalid @enderror" 
                       name="password" required autocomplete="current-password" autofocus>
                
                <span class="input-group-text" onclick="toggleVisibility()">
                    <i class="bi bi-eye-slash" id="toggleIcon"></i>
                </span>

                @error('password')
                    <span class="invalid-feedback" role="alert" style="display: block;">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        <div class="mb-0">
            <button type="submit" class="btn btn-orange mb-3">
                <i class="bi bi-shield-check me-2"></i>{{ __('Confirm Password') }}
            </button>

            @if (Route::has('staff.password.request'))
                <div class="text-center">
                    <a class="text-decoration-none small" style="color: #c06b00;" href="{{ route('staff.password.request') }}">
                        {{ __('Forgot Your Password?') }}
                    </a>
                </div>
            @endif
        </div>
    </form>
</div>

<script>
    function toggleVisibility() {
        const passwordInput = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        } else {
            passwordInput.type = 'password';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        }
    }
</script>
@endsection