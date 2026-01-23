<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TradLanka Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>


    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        

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
    justify-content: space-between;
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
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    min-width: 20px;
    height: 20px;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2px;
    margin-left: 10px; /* Backup spacing if justify-content isn't enough */
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
    
    // 1. SIDEBAR COUNTS (Already working)
    $pendingApplications = \App\Models\UserRequest::where('status', 'pending')->count();
    $pendingProducts = \App\Models\Product::whereIn('status', ['pending', 'reapproval_pending'])->count();
    $newReviewsCount = \App\Models\Review::where('is_read', 0)->count();
    $pendingOrdersCount = \App\Models\Order::where('status', 3)->count();
    $pendingReports = \Illuminate\Support\Facades\DB::table('submitted_reports')->where('status', 'pending')->count();

    // 2. BELL DROPDOWN DATA (The missing part)
    // Fetch actual records for the loops
    $latestOrdersNotify = \App\Models\Order::where('status', 3)->latest()->take(3)->get();
    
    $latestProductsNotify = \App\Models\Product::whereIn('status', ['pending', 'reapproval_pending'])
                                                ->latest()->take(3)->get();
    
    $latestReviewsNotify = \App\Models\Review::with('user')->where('is_read', 0)
                                              ->latest()->take(3)->get();
                                              
    $latestSellerRequestsNotify = \App\Models\UserRequest::where('status', 'pending')
                                                         ->latest()->take(3)->get();

    $latestChatsNotify = collect();
    $unreadMessages = 0;
    if ($admin) {
        // Eager load 'sender' to show the Seller's name instead of 'Admin'
        $latestChatsNotify = \App\Models\Message::with('sender')
            ->where('receiver_id', $admin->id)
            ->where('receiver_type', 'admin')
            ->where('is_read', 0)
            ->latest()->take(3)->get();
            
        $unreadMessages = $latestChatsNotify->count();
    }

    // 3. CALCULATE TOTAL FOR THE BELL BADGE
    $totalAlerts = $pendingApplications + $pendingProducts + $newReviewsCount + 
                   $unreadMessages + $pendingOrdersCount + $pendingReports;
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
        <div class="dropdown">
    <a href="#" class="position-relative text-white" data-bs-toggle="dropdown">
        <i class="bi bi-bell fs-5"></i>
        
        @php
            /* Use the pre-calculated totalAlerts variable from AppServiceProvider 
               to avoid "Undefined variable" errors */
        @endphp

        @if(isset($totalAlerts) && $totalAlerts > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill" style="font-size:0.6rem;">
                {{ $totalAlerts }}
            </span>
        @endif
    </a>
    
    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="width:340px; max-height: 480px; overflow-y: auto;">
        <li><h6 class="dropdown-header border-bottom pb-2 mb-2">Detailed Notifications</h6></li>

        {{-- 1. New Orders with specific Customer Name and Relative Time --}}
        @foreach($latestOrdersNotify as $order)
    <li class="border-bottom-light">
        <a class="dropdown-item py-2" href="{{ route('admin.orders.show', $order->id) }}">
            <div class="d-flex flex-column">
                <span class="small text-wrap">
                    <i class="bi bi-building me-2 text-primary"></i>
                    <strong>#{{ $order->tracking_no }}</strong> Arrived at Head Office
                </span>
                <small class="text-muted mt-1" style="font-size: 0.7rem;">
                    <i class="bi bi-clock me-1"></i>{{ $order->created_at->diffForHumans() }}
                </small>
            </div>
        </a>
    </li>
        @endforeach

        {{-- 2. Pending Products --}}
        @foreach($latestProductsNotify as $prod)
            <li class="border-bottom-light">
                <a class="dropdown-item py-2" href="{{ url('admin/products/'.$prod->id.'/edit') }}">
                    <div class="d-flex flex-column">
                        <span class="small text-wrap">
                            <i class="bi bi-bag-check me-2 text-info"></i>
                            Product Review: <strong>{{ Str::limit($prod->name, 25) }}</strong>
                        </span>
                        <small class="text-muted mt-1" style="font-size: 0.7rem;">
                            <i class="bi bi-clock me-1"></i>{{ $prod->created_at->diffForHumans() }}
                        </small>
                    </div>
                </a>
            </li>
        @endforeach

        {{-- 3. New Reviews with specific Reviewer Name --}}
        @foreach($latestReviewsNotify as $review)
            <li class="border-bottom-light">
                <a class="dropdown-item py-2" href="{{ route('admin.reviews') }}">
                    <div class="d-flex flex-column">
                        <span class="small text-wrap">
                            <i class="bi bi-star me-2 text-warning"></i>
                            New Review from <strong>{{ $review->user->name ?? 'Guest User' }}</strong>
                        </span>
                        <small class="text-muted mt-1" style="font-size: 0.7rem;">
                            <i class="bi bi-clock me-1"></i>{{ $review->created_at->diffForHumans() }}
                        </small>
                    </div>
                </a>
            </li>
        @endforeach

        {{-- 4. Staff Requests - Specific Applicant Name --}}
        @foreach($latestSellerRequestsNotify as $request)
            <li class="border-bottom-light">
                <a class="dropdown-item py-2" href="{{ route('admin.seller.requests') }}">
                    <div class="d-flex flex-column">
                        <span class="small text-wrap">
                            <i class="bi bi-person-badge me-2 text-primary"></i>
                            Staff Req: <strong>{{ $request->name }}</strong> is pending
                        </span>
                        <small class="text-muted mt-1" style="font-size: 0.7rem;">
                            <i class="bi bi-clock me-1"></i>{{ $request->created_at->diffForHumans() }}
                        </small>
                    </div>
                </a>
            </li>
        @endforeach

        {{-- 5. Staff Chat - Specific Staff Name & Message --}}
          @foreach($latestChatsNotify as $chat)
    <li class="border-bottom-light bg-light-message">
        <a class="dropdown-item py-2" href="{{ route('admin.chat.index') }}">
            <div class="d-flex flex-column">
                <span class="small text-wrap">
                    <i class="bi bi-chat-dots me-2 text-success"></i>
                    {{-- Dynamically pull the sender's name --}}
                    Staff Message from <strong>{{ $chat->sender->name ?? 'Staff' }}</strong>
                </span>
                <p class="small text-muted mb-0 text-truncate" style="font-size: 0.75rem;">
                    {{ $chat->message }}
                </p>
                <small class="text-muted mt-1" style="font-size: 0.7rem;">
                    <i class="bi bi-clock me-1"></i>{{ $chat->created_at->diffForHumans() }}
                </small>
            </div>
        </a>
    </li>
        @endforeach

        {{-- 6. Reports Summary --}}
        @if(isset($pendingReports) && $pendingReports > 0)
            <li class="border-bottom-light">
                <a class="dropdown-item py-2" href="{{ route('admin.seller.analytics.index') }}">
                    <div class="d-flex flex-column">
                        <span class="small text-wrap">
                            <i class="bi bi-bar-chart me-2 text-danger"></i>
                            <strong>{{ $pendingReports }}</strong> Pending Reports
                        </span>
                        <small class="text-muted" style="font-size: 0.7rem;">Review analytical logs</small>
                    </div>
                </a>
            </li>
        @endif

        @if(!isset($totalAlerts) || $totalAlerts == 0)
            <li class="dropdown-item text-center small text-muted py-4">No new notifications</li>
        @endif

        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-center small fw-bold text-primary" href="{{ route('admin.notifications.markAllRead') }}">Mark all as read</a></li>
    </ul>
</div>
{{-- Profile --}}
        <div class="dropdown">
    {{-- Added d-flex and align-items-center to the link below --}}
    <a href="#" class="text-white text-decoration-none dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
        @if($admin && $admin->image)
            {{-- Added me-2 for spacing --}}
            <img src="{{ asset('storage/' . $admin->image) }}" class="rounded-circle me-2" width="30" height="30" style="object-fit:cover;">
        @else
            <i class="bi bi-person-circle me-2"></i>
        @endif
        
        {{-- The name will now sit perfectly centered next to the image --}}
        <span>{{ $admin->name ?? 'Admin' }}</span>
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

      {{-- 6. Customer Management --}}
<a href="{{ route('admin.customers.index') }}" class="{{ request()->is('admin/customers*') ? 'active' : '' }}">
    <span><i class="bi bi-person-vcard"></i> Customer Management</span>
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
<a href="{{ route('admin.orders.review') }}" 
   class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }} d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
        <i class="bi bi-truck me-2"></i>
        <span>Orders</span>
    </div>
    {{-- This will now only show if status is 3 --}}
    @if(isset($pendingOrdersCount) && $pendingOrdersCount > 0)
        <span class="badge-count">{{ $pendingOrdersCount }}</span>
    @endif
</a>




{{-- 7. Reviews Sidebar Link --}}
<a href="{{ route('admin.reviews') }}" class="{{ request()->is('admin/reviews*') ? 'active' : '' }} d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center">
        <i class="bi bi-star me-2"></i>
        <span>Reviews</span>
    </div>
    {{-- Use the variable we just fixed above --}}
    @if(isset($newReviewsCount) && $newReviewsCount > 0)
        <span class="badge-count">{{ $newReviewsCount }}</span>
    @endif
</a>

    {{-- 8. Reports & Analysis Dropdown --}}
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
                <span>Inventory reports</span>
                @if($pendingReports > 0)
                    <span class="badge rounded-pill bg-danger ms-auto me-3" style="font-size: 9px;">{{ $pendingReports }}</span>
                @endif
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

{{-- 8. Staff Chat --}}
<a href="{{ route('admin.chat.index') }}" class="nav-link {{ request()->is('admin/chat*') ? 'active' : '' }} d-flex align-items-center">
    <div class="d-flex align-items-center">
        <i class="bi bi-chat-dots me-2"></i>
        <span>Staff Chat</span>
    </div>
    @if(isset($unreadMessages) && $unreadMessages > 0)
        <span class="badge-count ms-auto">{{ $unreadMessages }}</span>
    @endif
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