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
        
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2">
          {{-- Check if image exists, otherwise placeholder --}}
          <img src="https://via.placeholder.com/40" alt="TradLanka"
               class="h-10 w-10 rounded-full bg-white p-1" />
          <h1 class="text-xl font-bold">
            Trad<span class="text-yellow-400">Lanka</span>
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

      {{-- 3. RIGHT SIDE: Navigation Links + Icons --}}
      <div class="flex items-center gap-6 flex-shrink-0">
        
        <nav class="hidden xl:flex items-center gap-6 text-sm font-medium">
          <a href="#" class="hover:text-yellow-400 transition">Shop</a>
          <div class="flex items-center space-x-4">
    
         <div class="relative inline-block text-left">
            <select onchange="window.location.href='/set-currency/'+this.value" 
        class="bg-transparent border-none text-white font-semibold cursor-pointer focus:outline-none">
    <option class="text-black" value="LKR" {{ session('currency') == 'LKR' ? 'selected' : '' }}>LKR</option>
    <option class="text-black" value="USD" {{ session('currency') == 'USD' ? 'selected' : '' }}>USD</option>
</select>
          <a href="{{ route('about') }}" class="...your-classes...">About</a>
          <a href="{{ route('contact') }}" class="text-sm font-medium text-white hover:text-gray-200 transition">
    Contact
</a>
        </nav>

        {{-- Icons --}}
        <div class="flex items-center gap-5 text-xl">
          
          {{-- CART ICON (Fixed to check WEB guard only) --}}
          <a href="{{ Route::has('cart.show') ? route('cart.show') : '#' }}" class="hover:text-yellow-400 relative">
            <i class="fas fa-shopping-cart"></i>
            <span id="cart-badge" class="absolute -top-2 -right-3 bg-yellow-400 text-xs text-black font-bold rounded-full px-1.5 py-0.5 {{ (Auth::guard('web')->check() && \App\Models\Cart::where('user_id', Auth::guard('web')->id())->count() > 0) ? '' : 'hidden' }}">
                @if(Auth::guard('web')->check())
                    {{ \App\Models\Cart::where('user_id', Auth::guard('web')->id())->count() }}
                @else
                    0
                @endif
            </span>
          </a>
         {{-- Order Icon linked to the trackOrder method --}}
<a href="{{ url('track-order') }}" class="hover:text-yellow-400" title="Track Order">
    <i class="fas fa-box-open"></i>
</a>

             {{-- USER ICON (GUEST + LOGGED CUSTOMER) --}}

@auth('web')
    {{-- LOGGED IN → GO TO PROFILE --}}
    <a href="{{ route('user.profile.index') }}"
       class="flex flex-col items-center select-none cursor-pointer hover:opacity-90 transition">
        <i class="fas fa-user-circle text-2xl text-yellow-400"></i>

        <span class="text-[10px] font-bold mt-1 uppercase tracking-wide text-yellow-400 max-w-[80px] truncate">
            {{ strtok(auth('web')->user()->name, ' ') }}
        </span>
    </a>
@else
    {{-- GUEST → OPEN LOGIN MODAL --}}
    <div onclick="openAuthModal('login')"
         class="flex flex-col items-center select-none cursor-pointer hover:opacity-90 transition">
        <i class="fas fa-user-circle text-2xl text-white"></i>
    </div>
@endauth



          {{-- Global Logout Form  --}}
          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
              @csrf
          </form>

        </div>
      </div>
    </div>
  </header>

</body>
</html>