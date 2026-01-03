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

        <div class="nav-right">
            <div class="dropdown text-white">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
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
        <a href="{{ route('delivery.dashboard') }}" class="{{ request()->routeIs('delivery.dashboard') ? 'active' : '' }}">
            <span><i class="bi bi-speedometer2"></i> Dashboard</span>
        </a>

        <a href="{{ route('delivery.my-deliveries') }}" class="{{ request()->routeIs('delivery.my-deliveries') ? 'active' : '' }}">
            <span><i class="bi bi-box-seam"></i> Active Tasks</span>
            {{-- This should be dynamic based on your $orders count in the dashboard --}}
            <span class="badge bg-danger rounded-pill">!</span>
        </a>

        {{-- Unified History Link --}}
        <a href="{{ route('delivery.task-history') }}" class="{{ request()->routeIs('delivery.task-history') ? 'active' : '' }}">
            <span><i class="bi bi-clock-history"></i> Task History</span>
        </a>

        <a href="{{ route('delivery.chat.index') }}" class="{{ request()->is('delivery/chat*') ? 'active' : '' }}">
            <span><i class="bi bi-chat-dots-fill"></i> Staff Chat</span>
            
            @php
                $riderId = Auth::guard('delivery')->id();
                $totalUnread = \App\Models\Message::where('receiver_id', $riderId)
                    ->where('receiver_type', 'delivery')
                    ->where('is_read', false)
                    ->count();
            @endphp

            @if($totalUnread > 0)
                <span class="badge rounded-pill bg-danger">{{ $totalUnread }}</span>
            @endif
        </a>
    </div>

    <div class="main-content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>