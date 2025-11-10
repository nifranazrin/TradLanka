<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TradLanka Delivery Dashboard</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f9fafb;
            font-family: 'Poppins', sans-serif;
        }

        /* Sidebar */
        .sidebar {
            height: 100vh;
            background-color: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 1rem;
            position: fixed;
            width: 250px;
            top: 0;
            left: 0;
            z-index: 100;
        }

        .sidebar h4 {
            color: #d84315;
            font-weight: 700;
            margin-bottom: 2rem;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #444;
            font-weight: 500;
            margin-bottom: 10px;
            padding: 10px 12px;
            border-radius: 8px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #fff3f1;
            color: #d84315;
        }

        .sidebar a i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* Main */
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        .topbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .topbar .brand {
            font-weight: 700;
            color: #d84315;
        }

        /* Dashboard content */
        .dashboard-content {
            padding: 30px;
            flex: 1;
        }

        /* Hover effects */
        .card:hover {
            transform: translateY(-3px);
            transition: 0.3s ease;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                display: none;
            }
            .main-content {
                margin-left: 0;
            }
        }

        @media (max-width: 576px) {
            .topbar .d-flex {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
<div class="d-flex">
    {{-- Sidebar --}}
    <div class="sidebar">
        <h4>TradLanka Delivery</h4>

        <a href="{{ route('delivery.dashboard') }}" 
           class="{{ request()->is('delivery/dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard Overview
        </a>

        <a href="#">
            <i class="bi bi-box-seam"></i> My Deliveries
        </a>

        <a href="#">
            <i class="bi bi-check2-circle"></i> Completed Orders
        </a>

        <a href="#">
            <i class="bi bi-gear"></i> Account Settings
        </a>
    </div>

    {{-- Main Content --}}
    <div class="main-content w-100">
        {{-- Topbar --}}
        <div class="topbar">
            <div class="brand">
                Delivery Person
            </div>
            <div class="d-flex align-items-center">
                {{-- Notifications --}}
                <a href="#" class="btn btn-light btn-sm me-3 position-relative">
                    <i class="bi bi-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        0
                    </span>
                </a>

                {{-- Profile --}}
                <a href="#" class="btn btn-outline-secondary btn-sm me-3">
                    <i class="bi bi-person-circle me-1"></i> Profile
                </a>

                {{-- Logout --}}
                <form action="{{ route('staff.logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        {{-- Dashboard Page Content --}}
        <div class="dashboard-content">
            @yield('content')
        </div>
    </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Page-specific scripts --}}
@stack('scripts')

</body>
</html>
