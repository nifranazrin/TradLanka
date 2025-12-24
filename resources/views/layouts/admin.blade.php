<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TradLanka Admin Dashboard</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Tailwind CSS (for Admin UI components like Banner editor) -->
    <script src="https://cdn.tailwindcss.com"></script>

    



    {{-- Inline CSS to ensure styles load correctly on nested routes --}}
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
            z-index: 1000;
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

        /* Main Content Wrapper */
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            width: calc(100% - 250px);
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
                width: 100%;
            }
        }
    </style>
</head>


<body>


<div class="d-flex">
    {{-- Sidebar: hidden on admin.products.show --}}
    @unless(Route::is('admin.products.show'))
    <div class="sidebar">
        <h4>TradLanka Admin</h4>

        <a href="{{ route('admin.dashboard') }}" class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard Overview
        </a>

        {{-- === USER MANAGEMENT WITH COUNTER FOR PRODUCT=== --}}
        <a href="{{ route('admin.seller.requests') }}" class="{{ request()->is('admin/seller-requests*') ? 'active' : '' }} d-flex justify-content-between align-items-center">
            <span><i class="bi bi-people"></i> User Management</span>
            
            @php
                // Count Pending Sellers
                $pendingSellers = \App\Models\Staff::where('role', 'seller')->where('status', 'pending')->count();
            @endphp

            @if($pendingSellers > 0)
                <span class="badge bg-danger rounded-pill" style="font-size: 0.75rem;">{{ $pendingSellers }}</span>
            @endif
        </a>
    
        {{-- === REVIEW PRODUCTS WITH COUNTER === --}}
        <a href="{{ route('admin.products.index') }}" class="{{ request()->is('admin/products*') ? 'active' : '' }} d-flex justify-content-between align-items-center pe-3">
            <span><i class="bi bi-bag-check me-2"></i> Review Products</span>

            @php
                // Count Products that are 'pending' OR 'reapproval_pending'
                $pendingProducts = \App\Models\Product::whereIn('status', ['pending', 'reapproval_pending'])->count();
            @endphp

            @if($pendingProducts > 0)
                <span class="badge bg-danger rounded-pill" style="font-size: 0.75rem;">{{ $pendingProducts }}</span>
            @endif
        </a>

        {{-- === MANAGE CATEGORIES (Now visible directly) === --}}
        <a href="{{ route('admin.categories.index') }}" class="{{ request()->is('admin/categories*') ? 'active' : '' }}">
            <i class="bi bi-tags me-2"></i> Manage Categories
        </a>

        {{-- Other Menu Items --}}
          <a href="{{ route('admin.orders.review') }}" class="nav-link">
    <i class="fa fa-truck"></i> Review Orders
</a>
        <a href="#"><i class="bi bi-credit-card"></i> Payment & Refunds</a>
        <a href="#"><i class="bi bi-robot"></i> Chatbot Settings</a>
        <a href="#"><i class="bi bi-graph-up"></i> Reports & Analytics</a>
        <a href="{{ route('admin.banner.edit') }}">
    <i class="bi bi-pencil-square"></i> Website Content
</a>
        <a href="#"><i class="bi bi-gear"></i> Settings</a>
    </div>
    @endunless

    {{-- Main Content --}}
    <div class="main-content" style="{{ Route::is('admin.products.show') ? 'margin-left:0;width:100%;' : '' }}">
        
        {{-- Topbar --}}
        <div class="topbar">
            {{-- Left: Storefront button --}}
            <div>
                @unless(Route::is('admin.products.show'))
                    <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-shop me-1"></i> View Storefront
                    </a>
                @endunless
            </div>

            {{-- Right: Profile Dropdown --}}
            <div class="d-flex align-items-center gap-3">
                @unless(Route::is('admin.products.show'))
                    @php
                        // Get admin from guard or fallback to session
                        $admin = Auth::guard('admin')->user();
                        if (!$admin && session()->has('staff_id')) {
                            $admin = \App\Models\Staff::find(session('staff_id'));
                        }
                    @endphp

                    {{-- NOTIFICATION BELL START --}}
                    <div class="dropdown">
                        <a href="#" class="text-decoration-none text-dark position-relative me-3" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell fs-4 text-secondary"></i>
                            
                            {{-- Red Badge for Unread Count --}}
                            @if($admin && $admin->unreadNotifications->count() > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                    {{ $admin->unreadNotifications->count() }}
                                </span>
                            @endif
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notifDropdown" style="width: 320px; max-height: 400px; overflow-y: auto;">
                            <li><h6 class="dropdown-header fw-bold text-uppercase text-muted" style="font-size: 0.75rem;">Notifications</h6></li>
                            <li><hr class="dropdown-divider"></li>

                            @if($admin)
                                @forelse($admin->unreadNotifications as $notification)
                                    <li>
                                        {{-- Link includes logic to mark as read --}}
                                        <a class="dropdown-item d-flex align-items-start gap-2 py-2" 
                                           href="{{ route('admin.notifications.read', $notification->id) }}">
                                            
                                            {{-- Icon based on type --}}
                                            <div class="mt-1">
                                                @if(($notification->data['type'] ?? '') == 'new')
                                                    <i class="bi bi-plus-circle-fill text-primary"></i>
                                                @else
                                                    <i class="bi bi-pencil-square text-warning"></i>
                                                @endif
                                            </div>

                                            <div>
                                                <p class="mb-0 small fw-semibold text-wrap">{{ $notification->data['message'] }}</p>
                                                <small class="text-muted" style="font-size: 0.7rem;">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </a>
                                    </li>
                                @empty
                                    <li><a class="dropdown-item text-center text-muted small" href="#">No new notifications</a></li>
                                @endforelse
                            @endif
                            
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-center small text-primary fw-bold" href="{{ route('admin.notifications.markAllRead') }}">
                                    Mark all as read
                                </a>
                            </li>
                        </ul>
                    </div>
                    {{-- NOTIFICATION BELL END --}}

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
                @endunless
            </div>
        </div>

        {{-- Page Content --}}
        <div class="dashboard-content">
            @yield('content')
        </div>
    </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Global SweetAlert Notifications --}}

@if (session('inline_success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        html: {!! json_encode(session('inline_success')) !!}, 
        showConfirmButton: true,
        confirmButtonColor: '#198754',
        position: 'center'
    });
</script>
@endif

@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: {!! json_encode(session('success')) !!}, 
        showConfirmButton: false,
        timer: 2000,
        position: 'center'
    });
</script>
@endif

@if (session('error') || session('inline_error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: {!! json_encode(session('error') ?? session('inline_error')) !!}, 
        confirmButtonColor: '#a81c1c',
        position: 'center'
    });
</script>
@endif

{{-- Custom Scripts --}}
@stack('scripts')

</body>
</html>