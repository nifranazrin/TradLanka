<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TradLanka Seller</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background-color: #fff;
            border-right: 1px solid #dee2e6;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            /* Added z-index to keep it above other content */
            z-index: 1000; 
        }

        .sidebar .brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #800000;
            margin-bottom: 30px; /* Increased bottom margin for logo */
            text-align: center;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: 0.2s;
            
            /* --- CHANGED: Added Gap & Spacing --- */
            margin-bottom: 10px;   /* The gap between items */
            margin-left: 10px;     /* Space from left edge */
            margin-right: 10px;    /* Space from right edge */
            border-radius: 8px;    /* Rounded corners */
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #800000;
            color: #fff;
            /* Border radius is already set in the main class above */
        }

        .sidebar a i {
            margin-right: 12px;
            font-size: 18px;
        }

        /* Top Navbar */
        .navbar-top {
            height: 60px;
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 240px;
            right: 0;
            z-index: 100;
        }

        .navbar-top a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: 0.2s;
        }

        .navbar-top a:hover {
            color: #800000;
        }

        .navbar-top button {
            border: 1px solid #800000;
            color: #800000;
            background: transparent;
            border-radius: 6px;
            padding: 6px 12px;
            transition: 0.3s;
        }

        .navbar-top button:hover {
            background-color: #800000;
            color: #fff;
        }

        /* Main content */
        .main-content {
            margin-left: 240px;
            padding: 20px 40px;
        }

        .content-wrapper {
            margin-top: 80px;
        }

        html, body {
            height: 100%;
            overflow-x: hidden;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
                padding: 15px;
            }

            .navbar-top {
                left: 200px;
            }
        }
    </style>
</head>

<body>

    {{-- Sidebar --}}
    <div class="sidebar">
        <div class="brand">TradLanka Seller</div>

        <a href="{{ route('seller.dashboard') }}" class="{{ request()->is('seller/dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard Overview
        </a>

        <a href="{{ route('seller.products.index') }}" class="{{ request()->is('seller/products*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Product Management
        </a>

        <a href="#"><i class="bi bi-cart"></i> Orders</a>
        <a href="#"><i class="bi bi-truck"></i> Delivery Management</a>
        <a href="#"><i class="bi bi-chat-dots"></i> Customer Inquiries</a>
        <a href="#"><i class="bi bi-bar-chart"></i> Sales Reports</a>
        <a href="#"><i class="bi bi-gear"></i> Settings</a>
    </div>

    {{-- Top Navbar --}}
    <div class="navbar-top">
        <a href="#" class="fw-bold"><i class="bi bi-shop me-2 text-maroon"></i> View Storefront</a>

        <div class="d-flex align-items-center gap-4">
            <a href="{{ route('seller.profile') }}" class="text-decoration-none">
                <i class="bi bi-person-circle me-1 text-maroon"></i> My Profile
            </a>

            <form action="{{ route('staff.logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
            </form>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="main-content">
        <div class="content-wrapper">
            @yield('content')
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- SUCCESS ALERT --}}
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: {!! json_encode(session('success')) !!},
            confirmButtonColor: '#800000'
        });
    </script>
    @endif

    {{-- ERROR ALERT --}}
    @if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: {!! json_encode(session('error')) !!},
            confirmButtonColor: '#800000'
        });
    </script>
    @endif

</body>
</html>
