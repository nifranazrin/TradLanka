<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TradLanka Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            margin: 0;
            background: #f9fafb;
            font-family: 'Poppins', sans-serif;
        }

        /* ================= TOP NAVBAR ================= */
        .admin-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: #6e2727; /* Matches Seller Color */
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            padding: 0 24px;
            z-index: 1001;
        }

        .nav-left { display: flex; align-items: center; gap: 14px; white-space: nowrap; }
        .nav-logo { height: 36px; width: 36px; border-radius: 50%; background: #fff; padding: 3px; }
        .nav-center { text-align: center; color: #fff; font-size: 20px; font-weight: 800; letter-spacing: 1px; }
        .nav-right { display: flex; align-items: center; gap: 18px; white-space: nowrap; }

         /* ================= SIDEBAR ================= */
.sidebar {
    position: fixed;
    top: 64px;
    left: 0;
    width: 260px;
    height: calc(100vh - 64px);
    background: #f5efe1; /* Cream Background */
    border-right: 1px solid #e5e7eb;
    padding: 16px;
    z-index: 900;
    overflow-y: auto;
}

/* Base Sidebar Link Style */
.sidebar a {
    display: flex;
    align-items: center; /* Changed from space-between to align-center for better icons */
    color: #4d4a4a;
    text-decoration: none;
    font-weight: 500;
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 4px;
    transition: all 0.2s;
}

/* Sidebar Hover & Active States */
.sidebar a:hover, .sidebar a.active {
    background: #e2dddd;
    color: #6e2727;
}

/* Sidebar Icons */
.sidebar i { 
    margin-right: 10px; 
    font-size: 1.1rem; 
}

/* Fix for invisible white sub-menu links */
#reportSubmenu .nav-link {
    color: #270808 !important; /* Standard dark grey/black */
    font-weight: 500;
    transition: all 0.2s;
    opacity: 1 !important;
    visibility: visible !important;
}

/* Fix for icons inside the sub-menu */
#reportSubmenu .nav-link i {
    color: #4d4a4a !important; 
}

/* Hover state for sub-menu */
#reportSubmenu .nav-link:hover {
    color: #6e2727 !important; /* Maroon color on hover */
    background: #e2dddd;
    border-radius: 8px;
}

/* Active state for the specific report page you are viewing */
#reportSubmenu .nav-link.active-report {
    color: #6e2727 !important;
    font-weight: 700 !important;
}
        /* ================= MAIN CONTENT ================= */
        .main-content {
            margin-left: 260px;
            margin-top: 64px;
            padding: 30px;
            min-height: calc(100vh - 64px);
        }

        .badge-count {
            width: 20px; height: 20px;
            background: #dc3545; color: #fff;
            border-radius: 50%; font-size: 11px;
            display: flex; align-items: center; justify-content: center;
        }

        @media(max-width: 992px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
            .nav-center { font-size: 14px; }
        }
    </style>
</head>

<body>
  @php
    $admin = Auth::guard('admin')->user() ?? \App\Models\Staff::find(session('staff_id'));
    
    // CHANGE: Count from UserRequest model where status is pending
    // This now automatically includes both 'seller' and 'delivery' roles
    $pendingApplications = \App\Models\UserRequest::where('status', 'pending')->count();
    
    $pendingProducts = \App\Models\Product::whereIn('status', ['pending', 'reapproval_pending'])->count();
@endphp
<div class="admin-navbar">
    <div class="nav-left">
        <img src="{{ asset('logo/tradlanka-logo.jpg') }}" class="nav-logo">
        <span class="fw-bold fs-5 text-white">Trad<span style="color:#facc15;">Lanka</span></span>
        <a href="{{ route('home') }}" target="_blank" class="btn btn-light btn-sm fw-semibold ms-4 d-none d-md-inline-block">
            <i class="bi bi-shop me-1"></i> Storefront
        </a>
    </div>

    <div class="nav-center">ADMIN MANAGEMENT</div>

    <div class="nav-right">
        {{-- Notifications Dropdown --}}
        <div class="dropdown">
            <a href="#" class="position-relative text-white" data-bs-toggle="dropdown">
                <i class="bi bi-bell fs-5"></i>
                @if($admin && $admin->unreadNotifications->count() > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill" style="font-size:0.6rem;">
                        {{ $admin->unreadNotifications->count() }}
                    </span>
                @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow" style="width:300px;">
                <li><h6 class="dropdown-header">Recent Notifications</h6></li>
                @forelse($admin->unreadNotifications->take(5) as $n)
                     {{-- This now correctly points to UserApprovalController@readNotification --}}
                        <li><a class="dropdown-item small" href="{{ route('admin.notifications.read', $n->id) }}">{{ $n->data['message'] }}</a></li>
                @empty
                    <li class="dropdown-item text-center small text-muted">No new alerts</li>
                @endforelse
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-center small fw-bold text-primary" href="{{ route('admin.notifications.markAllRead') }}">Mark all as read</a></li>
            </ul>
        </div>

        {{-- Profile --}}
        <div class="dropdown">
            <a href="#" class="text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                @if($admin && $admin->image)
                    <img src="{{ asset('storage/' . $admin->image) }}" class="rounded-circle me-1" width="30" height="30" style="object-fit:cover;">
                @else
                    <i class="bi bi-person-circle me-1"></i>
                @endif
                {{ $admin->name ?? 'Admin' }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="{{ route('admin.profile.index') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('staff.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

@unless(Route::is('admin.products.show'))
<div class="sidebar"> {{-- Ensure only one div here --}}
   {{-- 1. Dashboard --}}
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
        <span><i class="bi bi-speedometer2"></i> Dashboard</span>
    </a>

    {{-- 2. Staff Requests --}}
    <a href="{{ route('admin.seller.requests') }}" class="{{ request()->is('admin/user-requests*') ? 'active' : '' }}">
        <span><i class="bi bi-people"></i> Staff Requests</span>
        @if($pendingApplications > 0) 
            <span class="badge-count">{{ $pendingApplications }}</span> 
        @endif
    </a>

    {{-- 3. Staff Management --}}
    <a href="{{ route('admin.staff.index') }}" class="{{ request()->is('admin/staff-management*') ? 'active' : '' }}">
        <span><i class="bi bi-person-badge"></i> Staff Management</span>
    </a>

    {{-- 4. Review Products --}}
    <a href="{{ route('admin.products.index') }}" class="{{ request()->is('admin/products*') ? 'active' : '' }}">
        <span><i class="bi bi-bag-check"></i> Review Products</span>
        @if($pendingProducts > 0) <span class="badge-count">{{ $pendingProducts }}</span> @endif
    </a>

    {{-- 5. Categories --}}
    <a href="{{ route('admin.categories.index') }}" class="{{ request()->is('admin/categories*') ? 'active' : '' }}">
        <span><i class="bi bi-tags"></i> Categories</span>
    </a>

    {{-- 6. Orders --}}
    <a href="{{ route('admin.orders.review') }}" class="{{ request()->is('admin/orders*') || request()->is('admin/review-orders*') ? 'active' : '' }}">
        <span><i class="bi bi-truck"></i> Orders</span>
    </a>
    
    {{-- 7. Reports & Analysis Dropdown --}}
<div class="nav-item">
    <a href="#reportSubmenu" data-bs-toggle="collapse" 
       class="d-flex align-items-center sidebar-link-fixed {{ request()->is('admin/reports*') || request()->is('admin/seller-analytics*') ? 'active' : '' }}" 
       style="text-decoration: none; padding: 10px 15px; color: rgb(36, 3, 3); display: block;">
        <i class="bi bi-bar-chart-line-fill me-2"></i> 
        <span>Reports & Analysis</span>
        
        {{-- Notification Badge for the Main Dropdown --}}
        @php
            $pendingReports = DB::table('submitted_reports')->where('status', 'pending')->count();
        @endphp
        @if($pendingReports > 0)
            <span class="badge rounded-pill bg-danger ms-2" style="font-size: 10px;">{{ $pendingReports }}</span>
        @endif
        
        <i class="bi bi-chevron-down ms-auto small"></i>
    </a>
    
    <ul class="collapse nav flex-column ms-3 {{ request()->is('admin/reports*') || request()->is('admin/seller-analytics*') ? 'show' : '' }}" 
        id="reportSubmenu" 
        style="background: transparent; list-style: none; padding: 0;">
        
        {{-- New: Seller Submissions with Notification --}}
        <li class="nav-item">
            <a href="{{ route('admin.seller.analytics.index') }}" 
               class="nav-link py-2 {{ request()->is('admin/seller-analytics*') ? 'fw-bold text-dark' : '' }}"
               style="display: flex; align-items: center; text-decoration: none; color: #4d4a4a !important;"> 
                <i class="bi bi-person-badge me-2"></i>
                <span>Seller Submissions</span>
                @if($pendingReports > 0)
                    <span class="badge rounded-pill bg-danger ms-auto me-3" style="font-size: 9px;">{{ $pendingReports }}</span>
                @endif
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.reports.inventory') }}" 
               class="nav-link py-2 {{ request()->is('admin/reports/inventory') ? 'fw-bold text-dark' : '' }}"
               style="display: flex; align-items: center; text-decoration: none; color: #4d4a4a !important;"> 
                <i class="bi bi-box-seam me-2"></i>
                <span>Inventory & Stock</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.reports.sales') }}" 
               class="nav-link py-2 {{ request()->is('admin/reports/sales') ? 'fw-bold text-dark' : '' }}"
               style="display: flex; align-items: center; text-decoration: none; color: #4d4a4a !important;"> 
                <i class="bi bi-currency-dollar me-2"></i>
                <span>Sales & Revenue</span>
            </a>
        </li>
    </ul>
</div>

{{-- 8. Staff Chat (Replaces Settings) --}}
<a href="{{ route('admin.chat.index') }}" class="{{ request()->is('admin/chat*') ? 'active' : '' }}">
    <span><i class="bi bi-chat-dots"></i> Staff Chat</span>
</a>
    {{-- 8. Settings --}}
    <a href="#" class="{{ request()->is('admin/settings*') ? 'active' : '' }}">
        <span><i class="bi bi-gear"></i> Settings</span>
    </a>

    {{-- 9. Web Content --}}
    <a href="{{ route('admin.banner.edit') }}" class="{{ request()->is('admin/banner*') || request()->is('admin/website-content*') ? 'active' : '' }}">
        <span><i class="bi bi-pencil-square"></i> Web Content</span>
    </a>
</div>
@endunless

<div class="main-content" style="{{ Route::is('admin.products.show') ? 'margin-left:0;' : '' }}">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Notifications Logic --}}
@if (session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Done!', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false });
</script>
@endif

@stack('scripts')
</body>
</html>