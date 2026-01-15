<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TradLanka - Authentic Sri Lankan Products</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>
    /* Custom Scrollbar */
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
  </style>
</head>

<body class="bg-[#f9f6f3] font-sans">

  <header class="fixed top-0 left-0 right-0 z-50 bg-[#5b2c2c] text-white shadow-lg">
    <div class="w-full flex items-center justify-between px-4 py-3 gap-4">

      {{-- 1. LEFT SIDE: Logo + All Categories --}}
      <div class="flex items-center gap-6 flex-shrink-0">
        
             {{-- Logo Section --}}
<a href="{{ route('home') }}" class="flex items-center gap-3 hover:opacity-90 transition-opacity">
    {{-- Your actual logo file --}}
    <img src="{{ asset('logo/tradlanka-logo.jpg') }}" 
         alt="TradLanka Logo" 
         class="h-10 w-10 rounded-full object-cover border-2 border-[#5b2c2c] bg-white shadow-sm shadow-maroon-100" />
    
    {{-- Brand Name --}}
    <h1 class="text-2xl font-extrabold tracking-tight">
        <span class="text-[#f5f0f0]">Trad</span><span class="text-yellow-500">Lanka</span>
    </h1>
</a>

        {{-- All Categories Dropdown --}}
        <div class="relative group z-50 hidden md:block">
            <button class="bg-[#8a4b2b] group-hover:bg-[#703b23] px-4 py-2 rounded flex items-center gap-2 transition cursor-pointer text-sm font-medium">
              <i class="fas fa-list"></i> All Categories
            </button>

            <ul class="absolute top-full left-0 bg-white text-[#4a2b2b] rounded-lg shadow-xl w-[280px] border border-[#eee] hidden group-hover:block py-2">
                @if(isset($globalCategories) && $globalCategories->count() > 0)
                    @foreach($globalCategories as $cat)
                        @if($cat->status == 1)
                            <li class="relative group/item hover:bg-[#f3f4f6] border-b border-gray-100 last:border-0 transition-colors duration-200">
                                
                                <a href="{{ url('category/'.$cat->slug) }}" class="px-4 py-3 flex items-center w-full gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 border border-gray-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                        <img src="{{ asset('storage/' . $cat->image) }}" 
                                             onerror="this.src='https://via.placeholder.com/40?text=Cat'"
                                             class="w-full h-full object-cover" alt="icon">
                                    </div>

                                    <span class="font-medium text-sm text-gray-700 flex-1 group-hover/item:text-[#8a4b2b]">
                                        {{ $cat->name }}
                                    </span>
                                    
                                    @php
                                        $activeSubcats = $cat->subcategories->where('status', 1);
                                    @endphp

                                    @if($activeSubcats->count() > 0)
                                        <i class="fas fa-angle-double-right text-[12px] text-purple-300 group-hover/item:text-purple-500"></i>
                                    @endif
                                </a>

                                {{-- Subcategories --}}
                                @if($activeSubcats->count() > 0)
                                    <div class="absolute left-full top-0 w-[600px] min-h-full bg-white shadow-2xl border-l border-gray-100 rounded-r-lg hidden group-hover/item:block p-6 z-50">
                                        <div class="border-l-4 border-yellow-400 pl-3 mb-6">
                                            <h3 class="text-lg font-bold text-gray-800">{{ $cat->name }} Shop</h3>
                                        </div>
                                        <div class="grid grid-cols-4 gap-6">
                                            @foreach($activeSubcats as $sub)
                                                <a href="{{ url('category/'.$sub->slug) }}" class="group/sub flex flex-col items-center text-center">
                                                    <div class="w-16 h-16 rounded-full bg-white shadow-sm overflow-hidden border border-gray-200 mb-3 group-hover/sub:border-yellow-400 group-hover/sub:shadow-md transition-all">
                                                        <img src="{{ asset('storage/' . $sub->image) }}" 
                                                             onerror="this.src='https://via.placeholder.com/60?text=Sub'"
                                                             class="w-full h-full object-cover">
                                                    </div>
                                                    <span class="text-xs font-semibold text-gray-600 group-hover/sub:text-[#8a4b2b] leading-tight px-1">
                                                        {{ $sub->name }}
                                                    </span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </li>
                        @endif 
                    @endforeach
                @else
                    <li class="p-4 text-red-500 text-sm">No categories loaded.</li>
                @endif
            </ul>
        </div>
      </div>

      {{-- 2. CENTER: Search Bar --}}
      <div class="flex-1 px-4 hidden lg:flex justify-center">
         <div class="w-full max-w-2xl">
            <livewire:search-bar />
         </div>
      </div>

     {{-- 3. RIGHT SIDE: Nav + Icons --}}
      <div class="flex items-center gap-6 flex-shrink-0">
        <nav class="hidden xl:flex items-center gap-6 text-sm font-medium">
          <select onchange="window.location.href='/set-currency/'+this.value" class="bg-transparent border-none text-white font-semibold cursor-pointer focus:outline-none">
            <option class="text-black" value="LKR" {{ session('currency') == 'LKR' ? 'selected' : '' }}>LKR</option>
            <option class="text-black" value="USD" {{ session('currency') == 'USD' ? 'selected' : '' }}>USD</option>
          </select>
          <a href="{{ route('about') }}" class="hover:text-yellow-400 transition">About</a>
          <a href="{{ route('contact') }}" class="hover:text-yellow-400 transition text-white">Contact</a>
        </nav>

        {{-- Unified Icons Row --}}
        <div class="flex items-center gap-5 text-xl">
          
      {{-- 1. NOTIFICATION BELL --}}
@auth('web')
  <div class="relative group pt-2 pb-2">
      <button class="hover:text-yellow-400 relative transition cursor-pointer flex items-center focus:outline-none">
          <i class="fas fa-bell"></i>
          
          @php 
            $unreadCount = Auth::guard('web')->user()->unreadNotifications->count(); 
          @endphp

          @if($unreadCount > 0)
              <span class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 bg-red-500 text-[10px] text-white font-bold rounded-full w-4 h-4 flex items-center justify-center shadow-sm">
                  {{ $unreadCount > 9 ? '9+' : $unreadCount }}
              </span>
          @endif
      </button>

      {{-- Dropdown Menu --}}
      <div class="absolute right-0 top-full bg-white text-gray-800 rounded-lg shadow-2xl w-72 border border-gray-100 hidden group-hover:block py-2 z-[60]">
          <div class="px-4 py-2 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-lg">
              <span class="text-xs font-bold uppercase text-gray-500 tracking-wider">Notifications</span>
          </div>
          
          <div class="max-h-80 overflow-y-auto custom-scroll">
              @forelse(Auth::guard('web')->user()->notifications->take(5) as $notification)
                  @php
                    $targetUrl = $notification->data['url'] ?? url('track-order?tracking_no=' . ($notification->data['tracking_no'] ?? ''));
                    $notifType = $notification->data['type'] ?? '';
                  @endphp

                  <a href="{{ $targetUrl }}" 
                     class="block px-4 py-3 hover:bg-gray-100 border-b border-gray-50 last:border-0 transition-colors {{ $notification->read_at ? 'opacity-60' : 'bg-yellow-50/40' }}">
                      
                      <p class="text-xs leading-tight text-gray-700">
                         {{ $notification->data['message'] ?? 'Order update received.' }}
                      </p>

                      {{-- 1. Sub-text for successful deliveries --}}
                      @if($notifType == 'delivery_success')
                        <span class="text-[9px] font-bold text-green-600 uppercase mt-1 block">
                            <i class="fas fa-star text-[8px]"></i> Click to leave a review!
                        </span>
                      @endif

                      {{-- 2. NEW: Sub-text for Cancellations --}}
                      @if($notifType == 'order_cancelled')
                        <span class="text-[9px] font-bold text-red-600 uppercase mt-1 block">
                            <i class="fas fa-times-circle text-[8px]"></i> Cancellation Finalized
                        </span>
                      @endif
                      
                      <div class="flex justify-between items-center mt-2">
                          <span class="text-[10px] font-bold text-[#5b2c2c] bg-gray-200 px-1.5 py-0.5 rounded">
                              {{ $notification->data['tracking_no'] ?? 'N/A' }}
                          </span>
                          <span class="text-[10px] text-gray-400 italic">
                              {{ $notification->created_at->diffForHumans() }}
                          </span>
                      </div>
                  </a>
              @empty
                  <div class="px-4 py-8 text-center text-gray-400 text-xs italic">
                      <i class="fas fa-bell-slash mb-2 block text-lg opacity-20"></i>
                      No new notifications.
                  </div>
              @endforelse
          </div>

          @if($unreadCount > 0)
              <div class="px-4 py-2 border-t border-gray-100 bg-gray-50 flex justify-center">
                  <form action="{{ route('user.notifications.markAllRead') }}" method="POST">
                      @csrf
                      <button type="submit" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 uppercase tracking-tight transition flex items-center gap-1">
                          <i class="fas fa-check-double text-[8px]"></i> 
                          Mark all as read
                      </button>
                  </form>
              </div>
          @endif
          <a href="#" class="block text-center py-2 text-[11px] font-bold text-[#5b2c2c] hover:bg-gray-100 transition border-t border-gray-100 uppercase tracking-tighter">
              View All Notifications
          </a>
      </div>
  </div>
@endauth
        {{-- CART --}}
          <a href="{{ route('cart.show') }}" class="hover:text-yellow-400 relative">
            <i class="fas fa-shopping-cart"></i>
            @php $cartCount = Auth::guard('web')->check() ? \App\Models\Cart::where('user_id', Auth::guard('web')->id())->count() : 0; @endphp
            <span id="cart-badge" class="absolute -top-2 -right-3 bg-yellow-400 text-xs text-black font-bold rounded-full px-1.5 py-0.5 {{ $cartCount > 0 ? '' : 'hidden' }}">
              {{ $cartCount }}
            </span>
          </a>

          {{-- TRACK --}}
          <a href="{{ url('track-order') }}" class="hover:text-yellow-400" title="Track Order">
            <i class="fas fa-box-open"></i>
          </a>

          {{-- USER --}}
          @auth('web')
            <a href="{{ route('user.profile.index') }}" class="flex flex-col items-center select-none cursor-pointer hover:opacity-90 transition">
              <i class="fas fa-user-circle text-2xl text-yellow-400"></i>
              <span class="text-[10px] font-bold mt-1 uppercase tracking-wide text-yellow-400 max-w-[80px] truncate">
                {{ strtok(auth('web')->user()->name, ' ') }}
              </span>
            </a>
          @else
            <div onclick="openAuthModal('login')" class="flex flex-col items-center select-none cursor-pointer hover:opacity-90 transition">
              <i class="fas fa-user-circle text-2xl text-white"></i>
            </div>
          @endauth

          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        </div>
      </div>
    </div>
  </header>
</body>
</html>