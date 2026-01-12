{{-- resources/views/user/profile/sidebar.blade.php --}}
<aside class="bg-white/95 backdrop-blur-sm rounded-lg shadow-lg p-5 text-sm sticky top-24 h-fit border-t-4 border-[#5b2c2c]">
    <p class="font-bold text-gray-800 mb-3 text-lg">My Account</p>

    <ul class="space-y-3 ml-2">
        <li>
            <a href="{{ route('user.profile.index') }}"
               class="{{ request()->routeIs('user.profile.index') ? 'font-bold text-[#5b2c2c]' : 'font-medium text-gray-600' }} hover:underline flex items-center gap-2">
               <i class="fas fa-user"></i> My Profile
            </a>
        </li>
        <li>
            <a href="{{ route('user.profile.address') }}"
               class="{{ request()->routeIs('user.profile.address') ? 'font-bold text-[#5b2c2c]' : 'font-medium text-gray-600' }} hover:text-[#5b2c2c] flex items-center gap-2">
               <i class="fas fa-map-marker-alt w-4"></i> Address Book
            </a>
        </li>
    </ul>

    <hr class="my-4 border-gray-200">

    <p class="font-bold text-gray-800 mb-3 text-lg">Orders</p>
    <ul class="space-y-3 ml-2">
        <li>
            <a href="{{ route('user.orders.index') }}"
               class="{{ request()->routeIs('user.orders.*') ? 'font-bold text-[#5b2c2c]' : 'font-medium text-gray-600' }} hover:text-[#5b2c2c] flex items-center gap-2">
               <i class="fas fa-box w-4"></i> My Orders
            </a>
        </li>
    </ul>

    <hr class="my-4 border-gray-200">

    <p class="font-bold text-gray-800 mb-3 text-lg">Reviews</p>
<ul class="space-y-3 ml-2">
    <li>
        {{-- Link points to user.reviews.index as per your web.php --}}
        <a href="{{ route('user.reviews.index') }}" 
           class="{{ request()->routeIs('user.reviews.*') ? 'font-bold text-[#5b2c2c]' : 'font-medium text-gray-600' }} hover:text-[#5b2c2c] flex items-center justify-between group transition-colors duration-200">
           
            <span class="flex items-center gap-2">
                <i class="fas fa-star text-sm {{ request()->routeIs('user.reviews.*') ? 'text-[#5b2c2c]' : 'text-gray-400 group-hover:text-[#5b2c2c]' }}"></i> 
                My Reviews
            </span>

            {{-- Badge logic: Displays red circle if there are pending reviews to be written --}}
           @if(isset($toReviewCount) && $toReviewCount > 0)
    <span class="bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full font-bold shadow-sm">
        {{ $toReviewCount }}
    </span>
@endif
        </a>
    </li>
</ul>

    <hr class="my-4 border-gray-200">

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="text-red-600 font-bold hover:text-red-800 flex items-center gap-2 transition">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </form>
</aside>