<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - TradLanka</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: url('{{ asset('storage/images/background.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            padding: 40px 35px;
            text-align: center;
        }

        .login-logo {
            width: 70px;
            margin-bottom: 10px;
        }

        .login-title {
            font-weight: 700;
            color: #7a1a1a;
            margin-bottom: 10px;
        }

        .form-control {
            border-radius: 12px;
            padding: 10px 15px;
        }

        .btn-login {
            background: linear-gradient(to right, #7a1a1a, #d37a00);
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: linear-gradient(to right, #a11f1f, #f2a200);
        }

        .forgot-link {
            color: #d37a00;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
        }

        /* password toggle styles */
        .password-wrapper { position: relative; }
        .password-wrapper .form-control { padding-right: 48px; }
        .toggle-password-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #6c757d;
        }
        .toggle-password-btn:focus { outline: none; box-shadow: 0 0 0 0.15rem rgba(123, 0, 0, 0.12); border-radius: 6px; }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="{{ asset('storage/images/tradlanka-logo.jpg') }}" alt="TradLanka Logo" class="login-logo">
        <h4 class="login-title">TradLanka Staff Portal</h4>
        <p class="text-muted mb-4">Welcome back to your dashboard</p>

        {{--  Alert Messages --}}
        @if (session('error'))
            <div class="alert alert-danger text-start">
                <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
            </div>
        @endif
        @if (session('success'))
            <div class="alert alert-success text-start">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        {{--  Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger text-start">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{--  Login Form --}}
        <form method="POST" action="{{ route('staff.login.submit') }}">
            @csrf

            <div class="mb-3 text-start">
                <label class="fw-semibold">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Enter your email" required autofocus>
            </div>

            <div class="mb-3 text-start">
                <label class="fw-semibold">Password</label>
                <div class="password-wrapper">
                    <input id="password" name="password" type="password" class="form-control" placeholder="Enter your password" required>
                    <button type="button" id="togglePasswordBtn" class="toggle-password-btn" aria-label="Show password" title="Show password">
                        <i id="togglePasswordIcon" class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    
                </div>
                 <a href="{{ route('staff.password.request') }}" class="forgot-link">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-login w-100 mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i> Login
            </button>

            <p class="small text-muted mb-0">
                Use your staff credentials to log in (Admin, Seller, or Delivery).
            </p>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const pwd = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const icon = document.getElementById('togglePasswordIcon');

        if (!pwd || !toggleBtn || !icon) return;

        toggleBtn.addEventListener('click', function () {
            const isPwd = pwd.type === 'password';
            pwd.type = isPwd ? 'text' : 'password';

            if (isPwd) {
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
                toggleBtn.setAttribute('aria-label', 'Hide password');
                toggleBtn.setAttribute('title', 'Hide password');
            } else {
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
                toggleBtn.setAttribute('aria-label', 'Show password');
                toggleBtn.setAttribute('title', 'Show password');
            }

            pwd.focus();
        });
    });
    </script>
</body>
</html>
