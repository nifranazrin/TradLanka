<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TradLanka Admin Dashboard</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f9fafb;
            font-family: 'Poppins', sans-serif;
        }

        /* Sidebar */
        .sidebar {
            height: 100vh;
            background-color: #fff;
            border-right: 1px solid #e5e7eb;
            padding: 1rem;
            position: fixed;
            width: 250px;
            overflow-y: auto;
        }

        .sidebar h4 {
            color: #a81c1c;
            font-weight: 700;
            margin-bottom: 2rem;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: #4d4a4a;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: #fff3f1;
            color: #a81c1c;
        }

        .sidebar a i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* Dropdown styling for nested links */
        .dropdown .collapse a {
            font-size: 0.95rem;
            transition: all 0.15s ease-in-out;
            color: #6b6b6b;
            padding-left: 0;
            margin-bottom: 6px;
            display: block;
        }
        .dropdown .collapse a:hover {
            color: #a81c1c !important;
            transform: translateX(4px);
        }

        /* Main */
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
        }

        /* Topbar */
        .topbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-content {
            padding: 30px;
        }

        @media (max-width: 992px) {
            .sidebar {
                display: none;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<div class="d-flex">
    {{-- Sidebar --}}
    <div class="sidebar">
        <h4>TradLanka Admin</h4>

        <a href="{{ route('admin.dashboard') }}" class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard Overview
        </a>

        {{-- User Management --}}
        <a href="{{ route('admin.seller.requests') }}" class="{{ request()->is('admin/seller-requests*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> User Management
        </a>

        {{-- PRODUCT MODERATION DROPDOWN --}}
        <div class="dropdown mb-2">
            <a class="d-flex align-items-center justify-content-between text-decoration-none {{ request()->is('admin/categories*') || request()->is('admin/products*') ? 'active' : '' }}"
               data-bs-toggle="collapse"
               href="#productMenu"
               role="button"
               aria-expanded="{{ (request()->is('admin/categories*') || request()->is('admin/products*')) ? 'true' : 'false' }}"
               aria-controls="productMenu">
                <span><i class="bi bi-box-seam me-2"></i> Product Moderation</span>
                <i class="bi bi-chevron-down small"></i>
            </a>

            <div class="collapse mt-2 ps-3 {{ request()->is('admin/categories*') || request()->is('admin/products*') ? 'show' : '' }}" id="productMenu">
                <a href="{{ route('admin.categories.index') }}" class="{{ request()->is('admin/categories*') ? 'fw-bold text-dark' : '' }}">
                    <i class="bi bi-tags me-2"></i> Manage Categories
                </a>
                <a href="{{ route('admin.products.index') }}" class="{{ request()->is('admin/products*') ? 'fw-bold text-dark' : '' }}">
                    <i class="bi bi-bag-check me-2"></i> Review Products
                </a>
            </div>
        </div>

        {{-- Other Menu Items --}}
        <a href="#"><i class="bi bi-truck"></i> Order & Delivery</a>
        <a href="#"><i class="bi bi-credit-card"></i> Payment & Refunds</a>
        <a href="#"><i class="bi bi-robot"></i> Chatbot Settings</a>
        <a href="#"><i class="bi bi-graph-up"></i> Reports & Analytics</a>
        <a href="#"><i class="bi bi-pencil-square"></i> Website Content</a>
        <a href="#"><i class="bi bi-gear"></i> Settings</a>
    </div>

    {{-- Main Content --}}
    <div class="main-content w-100">
    
        {{-- Topbar --}}
        <div class="topbar d-flex justify-content-between align-items: center px-4 py-2 border-bottom bg-white shadow-sm">

            {{-- Left: Storefront button --}}
            <div>
                <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-shop me-1"></i> View Storefront
                </a>
            </div>

            {{-- Right: Profile Dropdown --}}
            <div class="d-flex align-items-center gap-3">

                @php
                    // ✅ Try to get the currently logged-in admin via guard
                    $admin = Auth::guard('admin')->user();

                    // ✅ Fallback: get from session if guard not active
                    if (!$admin && session()->has('staff_id')) {
                        $admin = \App\Models\Staff::find(session('staff_id'));
                    }
                @endphp

                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        {{-- Profile Image --}}
                        @if($admin && $admin->image)
                            <img src="{{ asset('storage/' . $admin->image) }}" 
                                 alt="Profile" 
                                 class="rounded-circle me-2" 
                                 width="38" height="38" 
                                 style="object-fit: cover;">
                        @else
                            <i class="bi bi-person-circle fs-4 text-secondary me-2"></i>
                        @endif

                        {{-- Admin Name --}}
                        <span class="fw-semibold text-dark">
                            {{ $admin ? $admin->name : 'Admin' }}
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="adminDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.profile.index') }}">
                                <i class="bi bi-person me-2"></i> Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('staff.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        {{-- Page Content --}}
        <div class="dashboard-content">

            {{-- ✅ START: SPECIAL INLINE ALERT BANNER --}}

            
            @if (session('inline_success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {!! session('inline_success') !!}  {{-- Use {!! !!} to render HTML --}}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        
            @if (session('inline_error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {!! session('inline_error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            {{-- ✅ END: INLINE ALERTS --}}


            @yield('content')
        </div>
    </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Global SweetAlert Notifications --}}

{{-- ✅ THIS IS THE NEW SCRIPT --}}
{{-- It listens for 'inline_success' and DOES NOT have a timer --}}
@if (session('inline_success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        html: '{!! session('inline_success') !!}', // Use 'html' to show formatting
        showConfirmButton: true, // Show the "OK" button
        confirmButtonColor: '#198754',
        position: 'center'
        // NO TIMER
    });
</script>
@endif

{{-- ✅ ADDED BACK: For general 'success' popups --}}
@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}', // Simple text
        showConfirmButton: false,
        timer: 2000,
        position: 'center',
    });
</script>
@endif

{{--  This script handles general 'error' popups --}}
@if (session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        confirmButtonColor: '#a81c1c',
        position: 'center'
    });
</script>
@endif

{{-- Custom Scripts --}}
@stack('scripts')

</body>
</html>