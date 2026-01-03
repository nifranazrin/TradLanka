<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TradLanka Seller Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    margin:0;
    background:#f9fafb;
    font-family:'Poppins',sans-serif;
}

/* ================= TOP NAVBAR ================= */
.seller-navbar{
    position:fixed;
    top:0;
    left:0;
    right:0;
    height:64px;
    background:#6e2727;
    display:grid;
    grid-template-columns:auto 1fr auto;
    align-items:center;
    padding:0 24px;
    z-index:1001;
}

/* LEFT */
.nav-left{
    display:flex;
    align-items:center;
    gap:14px;
    white-space:nowrap;
}

.nav-logo{
    height:36px;
    width:36px;
    border-radius:50%;
    background:#fff;
    padding:3px;
}

/* CENTER */
.nav-center{
    text-align:center;
    color:#fff;
    font-size:20px;
    font-weight:800;
}

/* RIGHT */
.nav-right{
    display:flex;
    align-items:center;
    gap:18px;
    white-space:nowrap;
}

/* ================= SIDEBAR ================= */
.sidebar{
    position:fixed;
    top:64px;              /* BELOW NAVBAR */
    left:0;
    width:250px;
    height:calc(100vh - 64px);
    background:#f5efe1;
    border-right:1px solid #e5e7eb;
    padding:16px;
    z-index:900;
}

.sidebar .badge {
    width: 19px;
    height: 19px;
    padding: 0;
    border-radius: 50%;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}


.sidebar-header{
    background:#6b1f1f;
    color:#fff;
    font-weight:700;
    padding:14px;
    border-radius:8px;
    margin-bottom:16px;
    text-align:center;
}

.sidebar a{
    display:flex;
    justify-content:space-between;
    align-items:center;
    color:#4d4a4a;
    text-decoration:none;
    font-weight:500;
    padding:10px 12px;
    border-radius:8px;
    margin-bottom:8px;
}

.sidebar a:hover,
.sidebar a.active{
    background:#e2dddd;
    color:#501818;
}

.sidebar i{
    margin-right:8px;
}

/* ================= MAIN CONTENT ================= */
.main-content{
    margin-left:250px;
    margin-top:64px;
    padding:30px;
}

/* ================= RESPONSIVE ================= */
@media(max-width:992px){
    .sidebar{display:none;}
    .main-content{margin-left:0;}
}
</style>
</head>

<body>

@php
$seller = Auth::guard('seller')->user();
$notifCounts = ['product'=>0,'order'=>0,'inquiry'=>0,'chat'=>0];
if($seller){
    foreach($seller->unreadNotifications as $n){
        $t = $n->data['type'] ?? null;
        if($t && isset($notifCounts[$t])) $notifCounts[$t]++;
    }
}
@endphp

<!-- ================= TOP NAV ================= -->
<div class="seller-navbar">

    <div class="nav-left">
        <img src="{{ asset('logo/tradlanka-logo.jpg') }}" class="nav-logo">
        <span class="fw-bold fs-5">
    <span class="text-white">Trad</span><span style="color:#facc15;">Lanka</span>
</span>


        <a href="{{ route('home') }}" class="btn btn-light btn-sm fw-semibold ms-5">
            <i class="bi bi-shop me-1"></i> View Storefront
        </a>
    </div>

    <div class="nav-center">SELLER MANAGEMENT</div>

    <div class="nav-right">

        {{--  NOTIFICATION (UNCHANGED LOGIC) --}}
        <div class="dropdown">
            <a href="#" class="position-relative text-white" data-bs-toggle="dropdown">
                <i class="bi bi-bell fs-5"></i>
                @if($seller && $seller->unreadNotifications->count() > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill" style="font-size:0.6rem;">
                        {{ $seller->unreadNotifications->count() }}
                    </span>
                @endif
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow" style="width:320px;max-height:400px;overflow:auto;">
                <li><h6 class="dropdown-header text-muted small">Notifications</h6></li>
                <li><hr class="dropdown-divider"></li>

                @forelse($seller->unreadNotifications as $notification)
                    <li>
                        <a class="dropdown-item d-flex gap-2" href="{{ route('seller.notifications.read',$notification->id) }}">
                            <i class="bi bi-info-circle text-primary mt-1"></i>
                            <div>
                                <div class="small fw-semibold">{{ $notification->data['message'] }}</div>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="dropdown-item text-muted text-center small">No new notifications</li>
                @endforelse
                @if($seller && $seller->unreadNotifications->count() > 0)
    <li><hr class="dropdown-divider"></li>
    <li>
        <form method="POST" action="{{ route('seller.notifications.markAllRead') }}">
            @csrf
            <button class="dropdown-item text-center small fw-bold text-primary">
                Mark all as read
            </button>
        </form>
    </li>
@endif
            </ul>
        </div>

        {{-- PROFILE --}}
        <div class="dropdown">
            <a href="#" class="text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-1"></i> {{ $seller->name }}
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="{{ route('seller.profile') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('seller.logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                    </form>
                </li>
            </ul>
        </div>

    </div>
</div>

<!-- ================= SIDEBAR ================= -->
<div class="sidebar">

    <a href="{{ route('seller.dashboard') }}" class="{{ request()->is('seller/dashboard')?'active':'' }}">
        <span><i class="bi bi-speedometer2"></i> Dashboard</span>
    </a>

    <a href="{{ route('seller.products.index') }}">
        <span><i class="bi bi-box-seam-fill"></i> Products</span>
        @if($notifCounts['product'])
            <span class="badge bg-danger">{{ $notifCounts['product'] }}</span>
        @endif
    </a>

    <a href="{{ route('seller.orders.index') }}">
        <span><i class="bi bi-cart-check-fill"></i> Orders</span>
        @if($notifCounts['order'])
            <span class="badge bg-danger">{{ $notifCounts['order'] }}</span>
        @endif
    </a>

    <a href="{{ route('seller.inquiries') }}">
        <span><i class="bi bi-chat-dots-fill"></i> Inquiries</span>
        @if($notifCounts['inquiry'])
            <span class="badge bg-danger">{{ $notifCounts['inquiry'] }}</span>
        @endif
    </a>

    <a href="{{ route('seller.chat.index') }}">
        <span><i class="bi bi-people-fill"></i> Staff Chat</span>
        @if($notifCounts['chat'])
            <span class="badge bg-danger">{{ $notifCounts['chat'] }}</span>
        @endif
    </a>


     {{-- Customer Reviews with Notification Badge --}}
<a href="#" class="{{ request()->is('admin/reviews*') ? 'active' : '' }}">
    <span><i class="bi bi-star-fill"></i> Customer Reviews</span>
    @if(isset($notifCounts['review']) && $notifCounts['review'] > 0)
        <span class="badge bg-danger">{{ $notifCounts['review'] }}</span>
    @endif
</a>

{{-- Reports & Analytics with Notification Badge --}}
<a href="#" class="{{ request()->is('admin/reports*') ? 'active' : '' }}">
    <span><i class="bi bi-graph-up-arrow"></i> Reports & Analytics</span>
    @if(isset($notifCounts['report']) && $notifCounts['report'] > 0)
        <span class="badge bg-danger">{{ $notifCounts['report'] }}</span>
    @endif
</a>



</div>


<!-- ================= CONTENT ================= -->
<div class="main-content">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
