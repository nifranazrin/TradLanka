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

        .nav-center{
            text-align:center;
            color:#fff;
            font-size:20px;
            font-weight:800;
        }

        .nav-right{
            display:flex;
            align-items:center;
            gap:18px;
            white-space:nowrap;
        }

        /* ================= SIDEBAR ================= */
        .sidebar{
            position:fixed;
            top:64px;
            left:0;
            width:250px;
            height:calc(100vh - 64px);
            background:#f5efe1;
            border-right:1px solid #e5e7eb;
            padding:16px;
            z-index:900;
        }

        /* Standardized Red Circular Badges */
         .sidebar .badge {
            width: 20px !important;
            height: 20px !important;
            padding: 0 !important;
            border-radius: 50% !important;
            font-size: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #dc3545 !important; /* Force Red */
            color: white !important;
            font-weight: bold;
            line-height: 1;
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
            transition: all 0.2s;
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

        @media(max-width:992px){
            .sidebar{display:none;}
            .main-content{margin-left:0;}
        }
    </style>
</head>

<body>

@php $seller = Auth::guard('seller')->user(); @endphp

<div class="seller-navbar">
    <div class="nav-left">
        <img src="{{ asset('logo/tradlanka-logo.jpg') }}" class="nav-logo">
        <span class="fw-bold fs-5 text-white">Trad<span style="color:#facc15;">Lanka</span></span>
        <a href="{{ route('home') }}" class="btn btn-light btn-sm fw-semibold ms-5">
            <i class="bi bi-shop me-1"></i> View Storefront
        </a>
    </div>

    <div class="nav-center">SELLER MANAGEMENT</div>

    <div class="nav-right">
        {{-- Bell Dropdown --}}
<div class="dropdown">
    <a href="#" class="position-relative text-white" data-bs-toggle="dropdown">
        <i class="bi bi-bell fs-5"></i>
        @if(isset($notif_counts['total']) && $notif_counts['total'] > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill" style="font-size:0.6rem;">
                {{ $notif_counts['total'] }}
            </span>
        @endif
    </a>

    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="width:340px; max-height: 480px; overflow-y: auto;">
        <li><h6 class="dropdown-header border-bottom pb-2 mb-2">Detailed Notifications</h6></li>

        @php
            // MERGE all categories into one collection for absolute time sorting
            $combinedFeed = collect();

            // 1. Staff Chat Messages
            foreach($latestChatsNotify as $chat) {
        $combinedFeed->push(['type' => 'chat', 'item' => $chat, 'time' => $chat->created_at]);
    }
   
            // 2. Inquiries
            foreach($latestInquiriesNotify as $inquiry) {
                $combinedFeed->push(['type' => 'inquiry', 'item' => $inquiry, 'time' => \Carbon\Carbon::parse($inquiry->created_at)]);
            }
            // 3. New Orders
            foreach($latestOrdersNotify as $order) {
                $combinedFeed->push(['type' => 'order', 'item' => $order, 'time' => $order->created_at]);
            }
            // 4. Product Decisions (Admin)
            foreach($latestProductsNotify as $notif) {
                $combinedFeed->push(['type' => 'product', 'item' => $notif, 'time' => $notif->created_at]);
            }
            // 5. Customer Reviews
            foreach($latestReviewsNotify as $review) {
                $combinedFeed->push(['type' => 'review', 'item' => $review, 'time' => $review->created_at]);
            }

            // SORT: Absolute newest activity always at the very top
            $sortedFeed = $combinedFeed->sortByDesc('time');
        @endphp

           @forelse($sortedFeed as $entry)
    <li class="border-bottom-light">
        @if($entry['type'] == 'chat')
            <a class="dropdown-item py-2" href="{{ route('seller.chat.index') }}">
                <div class="d-flex flex-column">
                    <span class="small text-wrap">
                        <i class="bi bi-chat-dots me-2 text-success"></i>
                        {{-- Corrected display for sender name --}}
                        <strong>Staff Message from {{ $entry['item']->sender->name ?? 'Admin' }}:</strong> 
                        {{ Str::limit($entry['item']->message, 35) }}
                    </span>
                    <small class="text-muted mt-1" style="font-size: 0.7rem;">
                        <i class="bi bi-clock me-1"></i>{{ $entry['time']->diffForHumans() }}
                    </small>
                </div>
            </a>

                @elseif($entry['type'] == 'inquiry')
                    <a class="dropdown-item py-2" href="{{ route('seller.inquiries') }}">
                        <div class="d-flex flex-column">
                            <span class="small text-wrap">
                                <i class="bi bi-info-circle me-2 text-info"></i>
                                New Inquiry from <strong>{{ $entry['item']->name ?? $entry['item']->email ?? 'Guest' }}</strong>
                            </span>
                            <small class="text-muted mt-1" style="font-size: 0.7rem;">
                                <i class="bi bi-clock me-1"></i>{{ $entry['time']->diffForHumans() }}
                            </small>
                        </div>
                    </a>

                @elseif($entry['type'] == 'order')
                    <a class="dropdown-item py-2" href="{{ route('seller.orders.index') }}">
                        <div class="d-flex flex-column">
                            <span class="small text-wrap">
                                <i class="bi bi-truck me-2 text-primary"></i>
                                Order <strong>#{{ $entry['item']->tracking_no }}</strong> from {{ $entry['item']->fname }}
                            </span>
                            <small class="text-muted mt-1" style="font-size: 0.7rem;">
                                <i class="bi bi-clock me-1"></i>{{ $entry['time']->diffForHumans() }}
                            </small>
                        </div>
                    </a>

                @elseif($entry['type'] == 'review')
                    <a class="dropdown-item py-2" href="{{ route('seller.reviews') }}">
                        <div class="d-flex flex-column">
                            <span class="small text-wrap">
                                <i class="bi bi-star me-2 text-warning"></i>
                                New Review for <strong>{{ Str::limit($entry['item']->product->name ?? 'Product', 20) }}</strong>
                            </span>
                            <small class="text-muted mt-1" style="font-size: 0.7rem;">
                                <i class="bi bi-clock me-1"></i>{{ $entry['time']->diffForHumans() }}
                            </small>
                        </div>
                    </a>

                @elseif($entry['type'] == 'product')
                    <a class="dropdown-item py-2" href="{{ route('seller.notifications.read', $entry['item']->id) }}">
                        <div class="d-flex flex-column">
                            <span class="small text-wrap">
                                <i class="bi bi-box-seam me-2 text-success"></i>
                                {{ $entry['item']->data['message'] ?? 'Product Update' }}
                            </span>
                            <small class="text-muted mt-1" style="font-size: 0.7rem;">
                                <i class="bi bi-clock me-1"></i>{{ $entry['time']->diffForHumans() }}
                            </small>
                        </div>
                    </a>
                @endif
            </li>
        @empty
            <li class="dropdown-item text-center small text-muted py-4">No new activities</li>
        @endforelse

        <li><hr class="dropdown-divider"></li>
        <li>
            <form action="{{ route('seller.notifications.markAllRead') }}" method="POST">
                @csrf
                <button type="submit" class="dropdown-item text-center small fw-bold text-primary border-0 bg-transparent w-100">
                    Mark all as read
                </button>
            </form>
        </li>
    </ul>
</div>
        {{-- Profile Dropdown --}}
        <div class="dropdown">
            <a href="#" class="text-white text-decoration-none dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                @if($seller && $seller->image)
                    <img src="{{ asset('storage/' . $seller->image) }}" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover; border: 1px solid #fff;">
                @else
                    <i class="bi bi-person-circle me-1 fs-5"></i> 
                @endif
                {{ $seller->name ?? 'Seller' }}
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

<div class="sidebar">
    <a href="{{ route('seller.dashboard') }}" class="{{ request()->is('seller/dashboard')?'active':'' }}">
        <span><i class="bi bi-speedometer2"></i> Dashboard</span>
    </a>

     {{-- Products Notification --}}
    <a href="{{ route('seller.products.index') }}" class="{{ request()->is('seller/products*')?'active':'' }}">
        <span><i class="bi bi-box-seam-fill"></i> Products</span>
        @if(($notif_counts['product'] ?? 0) > 0)
            <span class="badge">{{ $notif_counts['product'] }}</span>
        @endif
    </a>

     <a href="{{ route('seller.orders.index') }}" class="{{ request()->is('seller/orders*')?'active':'' }}">
        <span><i class="bi bi-cart-check-fill"></i> Orders</span>
        @if(($notif_counts['order'] ?? 0) > 0)
            <span class="badge">{{ $notif_counts['order'] }}</span>
        @endif
    </a>

      <a href="{{ route('seller.inquiries') }}" class="{{ request()->is('seller/inquiries*')?'active':'' }}">
    <span><i class="bi bi-chat-dots-fill"></i> Inquiries</span>
    {{-- This must match the 'inquiry' key in your AppServiceProvider --}}
    @if(($notif_counts['inquiry'] ?? 0) > 0)
        <span class="badge">{{ $notif_counts['inquiry'] }}</span>
    @endif
</a>

     <a href="{{ route('seller.chat.index') }}" class="{{ request()->is('seller/chat*')?'active':'' }}">
        <span><i class="bi bi-people-fill"></i> Staff Chat</span>
        @if(($notif_counts['chat'] ?? 0) > 0)
            <span class="badge">{{ $notif_counts['chat'] }}</span>
        @endif
    </a>

     <a href="{{ route('seller.reviews') }}" class="{{ request()->is('seller/reviews*') ? 'active' : '' }}">
        <span><i class="bi bi-star-fill"></i> Customer Reviews</span>
        @if(($notif_counts['reviews'] ?? 0) > 0)
            <span class="badge">{{ $notif_counts['reviews'] }}</span>
        @endif
    </a>

    <a href="{{ route('seller.reports.index') }}" class="{{ request()->is('seller/reports*') ? 'active' : '' }}">
        <span><i class="bi bi-graph-up-arrow"></i> Reports & Analytics</span>
    </a>
</div>

<div class="main-content">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Notifications Logic: Matches the Admin brand colors you requested --}}
@if (session('success'))
<script>
    Swal.fire({ 
        icon: 'success', 
        title: 'Welcome!', 
        text: "{{ session('success') }}", 
        timer: 2500, 
        showConfirmButton: false,
        background: '#f5efe1',    /* Cream background matches your seller sidebar */
        color: '#6e2727',         /* Maroon text color */
        iconColor: '#6e2727',     /* Maroon icon color */
        backdrop: `rgba(110, 39, 39, 0.2)` /* Subtle maroon dimming */
    });
</script>
@endif
</body>
</html>