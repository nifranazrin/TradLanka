<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TradLanka | Delivery Dashboard</title>

    {{-- Bootstrap & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { margin: 0; background: #f9fafb; font-family: 'Poppins', sans-serif; }

        /* TOP NAVBAR */
        .seller-navbar {
            position: fixed;
            top: 0; left: 0; right: 0; height: 64px;
            background: #6e2727; 
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            padding: 0 24px;
            z-index: 1001;
        }

        .nav-left { display: flex; align-items: center; gap: 14px; }
        .nav-logo { height: 36px; width: 36px; border-radius: 50%; background: #fff; padding: 3px; }
        .nav-center { text-align: center; color: #fff; font-size: 18px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: 64px; left: 0; width: 250px;
            height: calc(100vh - 64px);
            background: #f5efe1; 
            border-right: 1px solid #e5e7eb;
            padding: 16px;
            z-index: 900;
        }

        /* Unified Sidebar Link Styling to prevent layout shifts */
        .sidebar a {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #4d4a4a;
            text-decoration: none;
            font-weight: 600;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: all 0.2s ease;
        }

        .sidebar a:hover, .sidebar a.active {
            background: #e2dddd;
            color: #6e2727;
        }

        .sidebar a i { font-size: 1.1rem; margin-right: 10px; }

        .main-content {
            margin-left: 250px;
            margin-top: 64px;
            padding: 30px;
            min-height: calc(100vh - 64px);
        }

        @media(max-width: 992px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

    <nav class="seller-navbar">
        <div class="nav-left">
            <img src="{{ asset('logo/tradlanka-logo.jpg') }}" class="nav-logo">
            <span class="fw-bold text-white fs-5">Trad<span style="color:#facc15;">Lanka</span></span>
        </div>
        
        <div class="nav-center">Delivery Management</div>

       <div class="nav-right d-flex align-items-center">
    <div class="dropdown me-3">
        <button class="btn btn-link position-relative text-white border-0 p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-bell fs-4"></i>
            {{-- Bell Badge: Shows total count of new tasks, history updates, and messages --}}
            @if(isset($totalNotificationCount) && $totalNotificationCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.65rem; padding: 0.35em 0.65em;">
                    {{ $totalNotificationCount }}
                </span>
            @endif
        </button>
        
        <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-0 mt-3" style="width: 320px; border-radius: 12px;">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light" style="border-radius: 12px 12px 0 0;">
                <h6 class="mb-0 fw-bold text-dark">Notifications</h6>
                @if(isset($totalNotificationCount) && $totalNotificationCount > 0)
                    <form action="{{ route('delivery.notifications.markAllRead') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm text-primary fw-bold p-0 border-0 bg-transparent" style="font-size: 0.75rem;">Mark all read</button>
                    </form>
                @endif
            </div>

            <div style="max-height: 350px; overflow-y: auto;">
                @if(isset($bellNotifications) && count($bellNotifications) > 0)
                    @foreach($bellNotifications as $notif)
                        <a href="{{ $notif['url'] }}" class="dropdown-item p-3 border-bottom text-wrap">
                            <div class="d-flex align-items-start">
                                <div class="me-3 mt-1">
                                    {{-- Dynamic Icons based on notification type --}}
                                    @if($notif['type'] == 'task') 
                                        <i class="bi bi-box-seam text-success fs-5"></i>
                                    @elseif($notif['type'] == 'chat') 
                                        <i class="bi bi-chat-dots text-primary fs-5"></i>
                                    @else 
                                        <i class="bi bi-exclamation-circle text-danger fs-5"></i> 
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-bold small text-dark">{{ $notif['title'] }}</div>
                                    <div class="text-muted small" style="line-height: 1.2;">{{ $notif['body'] }}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @else
                    <div class="p-5 text-center">
                        <i class="bi bi-bell-slash text-muted display-6 opacity-25"></i>
                        <p class="text-muted small mt-2 mb-0">No new notifications</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="dropdown text-white">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
            <img src="{{ Auth::guard('delivery')->user()->image ? asset('storage/' . Auth::guard('delivery')->user()->image) : asset('images/default-user.png') }}" 
                 class="rounded-circle me-2 border border-white" 
                 style="width: 32px; height: 32px; object-fit: cover;"
                 onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::guard('delivery')->user()->name) }}&background=800000&color=fff';">
            <span class="d-none d-sm-inline">{{ Auth::guard('delivery')->user()->name }}</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
            <li>
                <a class="dropdown-item" href="{{ route('delivery.profile') }}">
                    <i class="bi bi-person me-2"></i> My Profile
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form action="{{ route('staff.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
</div>
    </nav>

     <div class="sidebar">
    {{-- Dashboard --}}
    <a href="{{ route('delivery.dashboard') }}" class="{{ request()->routeIs('delivery.dashboard') ? 'active' : '' }}">
        <span><i class="bi bi-speedometer2"></i> Dashboard</span>
    </a>

    {{-- Active Tasks --}}
    <a href="{{ route('delivery.my-deliveries') }}" class="{{ request()->routeIs('delivery.my-deliveries') ? 'active' : '' }} d-flex justify-content-between align-items-center">
        <span><i class="bi bi-box-seam"></i> Active Tasks</span>
        {{--  Dynamically shows count of orders in status 4 or 10 --}}
        @if(isset($delivery_active_count) && $delivery_active_count > 0)
            <span class="badge bg-danger rounded-pill">{{ $delivery_active_count }}</span>
        @endif
    </a>

        {{-- Task History --}}
<a href="{{ route('delivery.task-history') }}" class="{{ request()->routeIs('delivery.task-history') ? 'active' : '' }} d-flex justify-content-between align-items-center">
    <span><i class="bi bi-clock-history"></i> Task History</span>
    
    @if(isset($delivery_history_count) && $delivery_history_count > 0)
        {{--  Shows actual number instead of 'New' --}}
        <span class="badge bg-danger rounded-pill">{{ $delivery_history_count }}</span>
    @endif
</a>

    {{-- Staff Chat --}}
    <a href="{{ route('delivery.chat.index') }}" class="{{ request()->is('delivery/chat*') ? 'active' : '' }} d-flex justify-content-between align-items-center">
        <span><i class="bi bi-chat-dots-fill"></i> Staff Chat</span>
        {{--  Dynamically shows unread message count --}}
        @if(isset($delivery_unread_messages) && $delivery_unread_messages > 0)
            <span class="badge rounded-pill bg-danger">{{ $delivery_unread_messages }}</span>
        @endif
    </a>
</div>

    <div class="main-content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session('success'))
<script>
    Swal.fire({ 
        icon: 'success',
        title: 'Update Successful',
        text: "{{ session('success') }}",
        position: 'center',        /* Positions the alert in the middle */
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        background: '#6e2727',     /* Maroon Background */
        color: '#facc15',          /* Yellow Text */
        iconColor: '#facc15',      /* Yellow Icon matches text */
        backdrop: `rgba(0,0,0,0.4)` /* Dims the dashboard background for focus */
    });
</script>
@endif
</body>
</html>