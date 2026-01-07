@extends('layouts.app')

@section('content')
<style>
    /* 1. Background to match Staff Login */
    body { 
        background: url('{{ asset('storage/images/background.jpg') }}') no-repeat center center fixed; 
        background-size: cover;
        height: 100vh; 
        display: flex; 
        align-items: center; 
        justify-content: center;
        font-family: 'Poppins', sans-serif;
    }

    /* 2. Card with Maroon Top Border */
    .reset-card { 
        background: white; 
        padding: 40px 35px; 
        border-radius: 20px; 
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); 
        width: 100%; 
        max-width: 420px; 
        border-top: 5px solid #7a1a1a; /* Maroon Border */
    }

    /* 3. Button with Maroon to Orange Gradient */
    .btn-maroon { 
        background: linear-gradient(to right, #7a1a1a, #d37a00);
        color: white; 
        border: none; 
        padding: 12px; 
        border-radius: 12px; 
        font-weight: 600; 
        width: 100%; 
        transition: all 0.3s ease; 
    }
    .btn-maroon:hover { 
        background: linear-gradient(to right, #a11f1f, #f2a200);
        color: white; 
        transform: translateY(-1px); 
    }

    /* 4. Input Focus Styling matching maroon theme */
    .form-control { border-radius: 12px; padding: 10px 15px; }
    .form-control:focus { 
        border-color: #7a1a1a; 
        box-shadow: 0 0 0 0.2rem rgba(122, 26, 26, 0.25); 
    }

    .login-logo { width: 70px; margin-bottom: 15px; }
</style>

<div class="reset-card">
    <div class="text-center mb-4">
        {{-- Fixed Logo Path from storage --}}
        <img src="{{ asset('storage/images/tradlanka-logo.jpg') }}" alt="TradLanka Logo" class="login-logo">
        <h4 style="color: #7a1a1a; font-weight: 700;">Forgot Password?</h4>
        <p class="text-muted small">Enter your email to receive a secure reset link.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success border-0 small mb-4 shadow-sm" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('staff.password.email') }}">
        @csrf

        <div class="mb-4 text-start">
            <label for="email" class="form-label fw-semibold small">Email Address</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ old('email') }}" required autocomplete="email" autofocus 
                   placeholder="Enter your registered email">

            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <button type="submit" class="btn btn-maroon shadow-sm">
            <i class="bi bi-envelope-at me-2"></i>Send Reset Link
        </button>

        <div class="text-center mt-4">
            <a href="{{ route('staff.login') }}" class="text-decoration-none small" style="color: #d37a00;">
                <i class="bi bi-arrow-left me-1"></i>Back to Login
            </a>
        </div>
    </form>
</div>
@endsection