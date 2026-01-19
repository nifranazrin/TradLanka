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
        box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
        width: 100%; 
        max-width: 420px; 
        border-top: 5px solid #660303; /* TradLanka Customer Orange */
    }
    .btn-orange { 
        background-color: #640707; 
        color: white; 
        border: none; 
        padding: 12px; 
        border-radius: 10px; 
        font-weight: bold; 
        width: 100%; 
        transition: 0.3s; 
    }
    .btn-orange:hover { 
        background-color: #660707; 
        color: white;
    }
    .form-control:focus {
        border-color: #5c0505;
        box-shadow: 0 0 0 0.25 cold rgba(192, 107, 0, 0.25);
    }
</style>

<div class="reset-card">
    <div class="text-center mb-4">
        <h4 style="color: #350505; font-weight: 700;">Customer Password Reset</h4>
        <p class="text-muted small">Enter your email and we'll send a secure link to your inbox.</p>
    </div>

    {{-- Success Message --}}
    @if (session('status'))
        <div class="alert alert-success border-0 small mb-4" style="background-color: #e6fffa; color: #234e52;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        
        <div class="mb-4 text-start">
            <label for="email" class="form-label fw-bold small">Email Address</label>
            <input id="email" type="email" name="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   value="{{ old('email') }}" required autocomplete="email" autofocus 
                   placeholder="yourname@example.com">
            
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <button type="submit" class="btn btn-orange shadow-sm">
            <i class="bi bi-envelope-paper me-2"></i>Send Reset Link
        </button>
        
        <div class="text-center mt-4">
            <a href="{{ url('/') }}" class="text-decoration-none small text-muted hover:text-dark">
                <i class="bi bi-arrow-left"></i> Back to Home
            </a>
        </div>
    </form>
</div>
@endsection