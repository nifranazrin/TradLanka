@extends('layouts.frontend')

@section('content')


{{-- ================================================= --}}
{{-- 1. CUSTOM CSS (POPUP + SLIDER)                    --}}
{{-- ================================================= --}}
<style>
   /* --- Corrected SweetAlert Popup Styles (Butter & Maroon) --- */
.cart-alert-popup {
    background: #f3d69f !important; /* Light Butter Background */
    border-radius: 16px !important;
    padding: 1.5rem 1.6rem !important;
    border: 2px solid #5b2c2c; /* Maroon Border */
}

/* Maroon Title */
.cart-alert-popup .swal2-title {
    font-size: 24px !important;
    font-weight: 800 !important;
    color: #5b2c2c !important; /* Brand Maroon */
    letter-spacing: 0.3px;
    margin-bottom: 8px !important;
}

/* Maroon Success Icon */
.cart-alert-popup .swal2-icon.swal2-success {
    border-color: #5b2c2c !important;
}
.cart-alert-popup .swal2-success-ring {
    border: .25em solid rgba(91, 44, 44, 0.2) !important;
}
.cart-alert-popup .swal2-success-line-tip, 
.cart-alert-popup .swal2-success-line-long {
    background-color: #5b2c2c !important;
}

/* View Cart Button - Maroon */
.cart-alert-popup .swal2-confirm {
    background: #5b2c2c !important; 
    color: #ebe5e5 !important;
    border-radius: 8px !important;
    font-weight: 700;
    text-transform: uppercase;
    padding: 10px 24px !important;
}

/* Continue Shopping Button - Green */
.cart-alert-popup .swal2-cancel {
    background: #198754 !important; 
    color: #ffffff !important;
    border-radius: 8px !important;
    font-weight: 600;
    padding: 10px 18px !important;
}

/* Ensure product text inside the popup is visible */
.cart-alert-popup div {
    color: #2c0a0a !important;
}

    /* --- Recommended Slider Animation --- */
    @keyframes scroll { 
        0% { transform: translateX(0); } 
        100% { transform: translateX(-50%); } 
    }
    .animate-scroll { 
        display: flex; 
        animation: scroll 30s linear infinite; 
    }
    .animate-scroll:hover {
        animation-play-state: paused; /* Pauses when user hovers */
    }

  
    /* Gold Label for Best Sellers to match your "Buy Now" button */
    .badge-best-seller {
        background: linear-gradient(45deg, #d97706, #f59e0b);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Green Label for New Arrivals */
    .badge-new-arrival {
        background-color: #198754;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

/* 1. The Card: Subtle shadow, more breathing room */
.full-card-height {
    display: flex;
    flex-direction: column;
    height: 95%;
    background: #ffffff;
    border-radius: 4px; /* Shorter radius is more modern */
    border: 1px solid #f2f2f2;
    transition: all 0.3s cubic-bezier(.25,.8,.25,1);
    overflow: hidden;
}

.full-card-height:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.05); /* Very light hover shadow */
    border-color: #e0e0e0;
}

/* 2. Image: Standardized Square with high-quality fill */
.unified-image-wrapper {
    width: 100%;
    aspect-ratio: 1 / 1; 
    background: #fff;
    overflow: hidden;
}



.unified-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Keeps the full image presence you liked */
    display: block;
}

/* 3. Title: Professional and Clean */
.unified-title-height {
    min-height: 40px; /* Locked height so even 1-word titles don't break alignment */
    font-size: 0.92rem;
    font-weight: 500; /* Medium weight is more elegant than Bold */
    color: #333;
    line-height: 1.4;
    text-align: center;
    padding: 0 5px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* 4. Elegant Action Button */
.custom-cart-btn {
    background-color: #5b2c2c;
    color: #ffffff;
    padding: 10px 0; 
    border-radius: 2px; /* Nearly square buttons look premium */
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    width: 100%;
    margin-top: auto;
    border: none;
    cursor: pointer;
}

</style>

@php
    $currencySymbol = session('currency') === 'USD' ? '$' : 'Rs.';
@endphp

{{-- Floating Toast Fallback --}}
<div id="cartToast" class="hidden fixed bottom-6 right-6 bg-green-600 text-white px-5 py-3 rounded-lg shadow-lg z-50 transition-all duration-500 flex items-center space-x-2">
    <i class="fas fa-check-circle text-white text-lg"></i>
    <span id="toastMessage">Added to cart!</span>
</div>

{{-- Main Wrapper --}}
<div class="w-full mt-0 pt-0">

    {{-- Hero Banner --}}
    <div class="w-full mt-0">
        @include('frontend.partials.banner')
    </div>

    {{-- Page Content --}}
    <div class="px-6 lg:px-10">

    {{-- SECTION: SHOP BY CATEGORY --}}
<section class="mt-10 mb-12">
    <h2 class="text-2xl font-bold text-[#5b2c2c] mb-6 text-center">Shop by Category</h2>
    @if($categories->isEmpty())
        <p class="text-center text-gray-500">No categories available yet.</p>
    @else
        <div class="flex flex-wrap justify-center items-start gap-y-8 gap-x-4 md:gap-x-10 pb-4">
            @foreach ($categories as $category)
                @if($category->parent_id === null) 
                    <a href="{{ route('categories.show', $category->slug) }}" class="flex flex-col items-center cursor-pointer group no-underline w-[110px] sm:w-[130px]">
                        {{-- Circle Container --}}
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full overflow-hidden bg-[#f7f0ef] shadow-md hover:scale-105 transition-transform">
                            @if ($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-full h-full object-cover group-hover:opacity-90">
                            @else
                                <img src="https://via.placeholder.com/100x100?text={{ urlencode($category->name) }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        {{-- Text Label --}}
                        <p class="mt-3 text-xs sm:text-sm font-medium text-gray-700 group-hover:text-[#5b2c2c] transition-colors text-center leading-tight">
                            {{ $category->name }}
                        </p>
                    </a>
                @endif
            @endforeach
        </div>
    @endif
</section>


{{-- SECTION: RECOMMENDED FOR YOU (AI POWERED) --}}
<section class="mt-10 mb-16">
    <h2 class="text-2xl font-bold text-[#5b2c2c] mb-6">Recommended for You</h2>

    <div class="flex space-x-6 overflow-x-auto pb-4 custom-scroll">
        @if(isset($recommendedProducts) && $recommendedProducts->isNotEmpty())
            @foreach ($recommendedProducts as $product)
                <div class="min-w-[220px] bg-white rounded-lg shadow-md">
                    <a href="{{ route('product.show', $product->slug) }}">
                        <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-36 object-cover">
                        <div class="p-4 text-center">
                            <h3 class="font-semibold text-gray-800 truncate">{{ $product->name }}</h3>
                                 <p class="text-[#5b2c2c] font-bold">
                                    {{ $currencySymbol }} {{ number_format($product->price, 2) }}
                                </p>
                        </div>
                    </a>
                </div>
            @endforeach
        @else
            <p class="text-gray-500 w-full text-center">View items to see personalized suggestions!</p>
        @endif
    </div>
</section>



    {{-- SECTION: BEST SELLERS --}}
<section class="mt-12 mb-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl md:text-2xl font-bold text-[#5b2c2c]">Best Sellers</h2>
        <a href="{{ route('search.page', ['query' => 'best sellers', 'browse' => 'all']) }}" 
           class="text-[#5b2c2c] font-bold hover:underline">
            Browse more →
        </a>
    </div>

   <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 w-full">
    @foreach ($bestSellers as $item)
        @if($item)
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-100 full-card-height flex flex-col">
            <div class="relative unified-image-wrapper">
                <div class="absolute top-2 left-2 z-10 pointer-events-none">
                    <span class="bg-[#d97706] text-white text-[9px] font-bold px-2 py-1 rounded shadow-sm flex items-center gap-1">
                        <i class="fas fa-crown"></i> BEST SELLER
                    </span>
                </div>
                
                <a href="{{ route('product.show', $item->slug) }}">
                    <img src="{{ $item->image ? asset('storage/' . $item->image) : asset('images/placeholder.jpg') }}" 
                         alt="{{ $item->name }}" 
                         class="w-full h-full object-cover"
                         onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
                </a>
            </div>
            
            <div class="p-4 text-center flex-grow flex flex-col justify-between">
                <div>
                    <h3 class="font-semibold text-gray-800 unified-title-height">
                        {{ $item->name }}
                    </h3>

                    <div class="flex justify-center items-center h-5 mb-1">
                    
                        @php $avgRating = $item->reviews->avg('rating'); @endphp
                        @if($avgRating > 0)
                            <div class="flex text-yellow-400 text-[10px]">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="{{ $i <= round($avgRating) ? 'fas' : 'far' }} fa-star"></i>
                                @endfor
                                <span class="text-gray-400 ml-1">({{ $item->reviews->count() }})</span>
                            </div>
                        @else
                            <div class="h-5"></div>
                        @endif
                    </div>

                    <p class="text-[#5b2c2c] font-bold text-lg mb-2">
                        {{ $currencySymbol }} {{ number_format($item->price, 2) }}
                    </p>
                </div>
                
                {{-- DYNAMIC BUTTON: Switches based on stock --}}
                @if($item->stock > 0)
                    <button type="button"
                            class="addToCartBtn custom-cart-btn active:scale-95"
                            data-id="{{ $item->id }}" 
                            data-name="{{ $item->name }}" 
                            data-price="{{ $item->price }}" 
                            data-image="{{ $item->image ? asset('storage/' . $item->image) : asset('images/placeholder.jpg') }}">
                        Add to Cart
                    </button>
                @else
                    {{-- Daraz-Style Wishlist Button --}}
                <button type="button"
                        class="addToCartBtn custom-cart-btn bg-gray-400 opacity-60 active:scale-95"
                        style="background-color: #6c757d !important;"
                        data-id="{{ $item->id }}" 
                        data-name="{{ $item->name }}" 
                        data-price="{{ $item->price }}" 
                        data-image="{{ $item->image ? asset('storage/' . $item->image) : asset('images/placeholder.jpg') }}"
                        data-is-wishlist="true"> {{-- Flag to trigger "Saved to Wishlist" alert --}}
                    <i class="fas fa-heart mr-1"></i> Add to Wishlist
                </button>
            @endif {{-- Closes the stock check: if($item->stock > 0) --}}
        </div>
    </div>
    @endif {{-- Closes the existence check: if($item) --}}
@endforeach {{-- Closes the product loop --}}
</div>
</section>

{{-- SECTION: NEW ARRIVALS --}}
<section class="mt-12 mb-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl md:text-2xl font-bold text-[#5b2c2c]">New Arrivals</h2>
        <a href="{{ route('search.page', ['query' => 'new arrivals', 'browse' => 'all']) }}" 
   class="text-[#5b2c2c] font-bold hover:underline">
   Browse more →
</a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 w-full">
    @foreach ($products->take(5) as $item)
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-100 full-card-height">
            {{-- Image Wrapper with Green Badge --}}
            <div class="relative unified-image-wrapper">
                {{-- NEW ARRIVAL BADGE (GREEN) --}}
                <div class="absolute top-2 left-2 z-10 pointer-events-none">
                    <span class="bg-[#198754] text-white text-[9px] font-bold px-2 py-1 rounded shadow-sm">
                        NEW ARRIVAL
                    </span>
                </div>

                <a href="{{ route('product.show', $item->slug) }}">
                    <img src="{{ $item->image ? asset('storage/' . $item->image) : asset('images/placeholder.jpg') }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                </a>
            </div>
            
           <div class="p-4 text-center flex-grow flex flex-col justify-between">
    <div>
        <h3 class="font-semibold text-gray-800 unified-title-height">
            {{ $item->name }}
        </h3>

        <div class="flex justify-center items-center h-5 mb-1">
            @php $avgRating = $item->reviews->avg('rating'); @endphp
            @if($avgRating > 0)
                <div class="flex text-yellow-400 text-[10px]">
                    @for ($i = 1; $i <= 5; $i++)
                        <i class="{{ $i <= round($avgRating) ? 'fas' : 'far' }} fa-star"></i>
                    @endfor
                    <span class="text-gray-400 ml-1">({{ $item->reviews->count() }})</span>
                </div>
            @else
                <div class="h-5"></div>
            @endif
        </div>

        <p class="text-[#5b2c2c] font-bold text-lg mb-2">
            {{ $currencySymbol }} {{ number_format($item->price, 2) }}
        </p>
    </div>

    {{-- DYNAMIC BUTTON: Switches based on stock level --}}
    @if($item->stock > 0)
        {{-- Standard Add to Cart button --}}
        <button type="button"
                class="addToCartBtn custom-cart-btn active:scale-95"
                data-id="{{ $item->id }}" 
                data-name="{{ $item->name }}" 
                data-price="{{ $item->price }}" 
                data-image="{{ $item->image ? asset('storage/' . $item->image) : asset('images/placeholder.jpg') }}">
            Add to Cart
        </button>
    @else
        {{-- Daraz-Style Wishlist Button --}}
        {{-- Daraz-Style Wishlist Button: Added addToCartBtn class and data attributes --}}
    <button type="button"
            class="addToCartBtn custom-cart-btn bg-gray-400 opacity-60 active:scale-95"
            style="background-color: #6c757d !important;"
            data-id="{{ $item->id }}" 
            data-name="{{ $item->name }}" 
            data-price="{{ $item->price }}" 
            data-image="{{ $item->image ? asset('storage/' . $item->image) : asset('images/placeholder.jpg') }}"
            data-is-wishlist="true"> {{-- Added flag for JS to show "Wishlist" alert --}}
        <i class="fas fa-heart mr-1"></i> Add to Wishlist
    </button>
@endif {{-- Closes if($item->stock > 0) --}}
        </div>
    </div>
@endforeach
    </div>
</section>

 {{-- SECTION: POPULAR CATEGORIES --}}
<section class="mt-16 mb-12">
    <h2 class="text-2xl font-bold text-[#5b2c2c] mb-8 text-left">Popular Categories</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-4">
        
        @php
            $catStyles = [
                0 => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-700', 'tag' => 'Ceylon’s Pride!'],
                1 => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'text' => 'text-orange-600', 'tag' => 'Taste of Tradition!'],
                2 => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-600', 'tag' => 'Crafted with Heritage!']
            ];
        @endphp

        @foreach($popularCategories as $index => $cat)
        <a href="{{ url('category/'.$cat->slug) }}" 
           class="flex items-center justify-between {{ $catStyles[$index]['bg'] }} {{ $catStyles[$index]['border'] }} border rounded-2xl p-6 shadow-sm hover:shadow-lg transition group">
            <div class="flex-1">
                <p class="{{ $catStyles[$index]['text'] }} font-medium text-xs">{{ $catStyles[$index]['tag'] }}</p>
                <h3 class="text-2xl font-extrabold text-gray-800">{{ $cat->name }}</h3>
            </div>
            
            <div class="w-24 h-24 flex items-center justify-center">
                @if($cat->image)
                    {{-- Added border and padding to the image itself --}}
                    <img src="{{ asset('storage/' . $cat->image) }}" 
                         class="w-full h-full object-cover rounded-lg border-2 border-white shadow-sm group-hover:scale-105 transition-transform" 
                         alt="{{ $cat->name }}"
                         onerror="this.src='https://via.placeholder.com/100x100?text={{ $cat->name }}'">
                @else
                    <div class="w-full h-full bg-white rounded-lg border-2 border-white flex items-center justify-center text-gray-300">
                        <i class="fas fa-box fa-2x"></i>
                    </div>
                @endif
            </div>
        </a>
        @endforeach
        
    </div>
</section>

             {{-- SECTION: ADVERTISEMENT BANNER --}}
<section class="mt-14 mb-14 relative overflow-hidden">

    @php
        $bgImage = 'https://via.placeholder.com/1200x400?text=Default+Banner';
        $title = 'Special Offers';
        $btnLink = '#';
        $btnText = 'View';

        if(isset($banner)) {
            if(\Illuminate\Support\Str::startsWith($banner->image_path, 'http')) {
                $bgImage = $banner->image_path;
            } else {
                $bgImage = asset('storage/' . $banner->image_path);
            }
            $title = $banner->title;
            $btnLink = $banner->button_link;
            $btnText = $banner->button_text;
        }
    @endphp

    <div class="relative w-full h-[260px] md:h-[420px] overflow-hidden rounded-xl">

        {{-- Banner Image --}}
        <img 
            src="{{ $bgImage }}"
            alt="Advertisement Banner"
            class="absolute inset-0 w-full h-full object-cover"
        >

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-r from-[#2b0f0f]/80 via-[#5b2c2c]/60 to-transparent"></div>

        {{-- Content --}}
        <div class="relative h-full flex items-center">
            <div class="max-w-6xl mx-auto px-6 text-center w-full">

                <h2 class="text-white text-2xl md:text-4xl font-extrabold mb-6 drop-shadow-lg leading-tight">
                    {{ $title }}
                </h2>

                <div class="flex justify-center">
                    <a href="{{ $btnLink }}"
                       class="inline-flex items-center gap-2
                              bg-yellow-400 hover:bg-yellow-500
                              text-black font-semibold
                              px-8 py-3
                              rounded-lg shadow-lg
                              transition duration-200">
                        {{ $btnText }}
                    </a>
                </div>

            </div>
        </div>

    </div>
</section>


      
{{-- ================================================= --}}
{{-- FLOATING BUTTONS --}}
{{-- ================================================= --}}

<div class="fixed bottom-6 right-6 flex flex-col items-center space-y-3 z-50">

    <!-- Chatbot Button -->
    

  <a href="https://wa.me/94757679793" target="_blank"
   class="bg-[#25D366] text-white w-12 h-12 rounded-full shadow-lg
          flex items-center justify-center hover:scale-110 transition"
   style="z-index:10000;">
    <i class="fab fa-whatsapp text-2xl"></i>
</a>



{{-- ================================================= --}}
{{-- 4. SCRIPTS                                        --}}
{{-- ================================================= --}}
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    //  FIX 1: Define isLoggedIn at the top level so all functions can see it.
    const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};

    document.addEventListener('DOMContentLoaded', function() {
        // 2. Initialize Animations (AOS)
        if (typeof AOS !== 'undefined') {
            AOS.init({ once: true, offset: 100 });
        }

    function updateCartIcon(count) {
            const badge = document.getElementById('cart-badge'); 
            if(badge) {
                badge.innerText = count;
                badge.classList.remove('hidden');
                
                // Optional: Add a small "pop" animation
                badge.style.transform = "scale(1.3)";
                setTimeout(() => { badge.style.transform = "scale(1)"; }, 200);
            }
        }

        // --- SUCCESS ORDER ALERT ---

        @if(session('order_success'))
            Swal.fire({
                title: 'Thank You! ⭐',
                text: 'Your order has been placed successfully. We will notify you once it is out for delivery.',
                icon: 'success',
                confirmButtonText: '<i class="fas fa-shopping-bag"></i> Continue Shopping',
                customClass: { popup: 'cart-alert-popup' }
            }).then((result) => {
                if (result.isConfirmed) window.location.href = "{{ route('home') }}";
            });
        @endif


        // ============================================
        // 4. UNIVERSAL ADD TO CART & WISHLIST LOGIC
        // ============================================
    const buttons = document.querySelectorAll('.addToCartBtn');

        buttons.forEach(btn => {
            btn.addEventListener('click', function(event) {
                event.preventDefault(); 
                
                const productId    = this.getAttribute('data-id'); 
                const productName  = this.getAttribute('data-name'); 
                const rawPrice     = this.getAttribute('data-price'); 
                const productImage = this.getAttribute('data-image'); 
                // CHECK IF THIS IS A WISHLIST ACTION
                const isWishlist   = this.getAttribute('data-is-wishlist') === 'true';
                const productQty   = 1; 

                // --- GUEST LOGIC ---
                if (!isLoggedIn) {
                    // Save intent to session via background request
                    fetch("{{ route('cart.save-intent') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Content-Type": "application/json",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({ product_id: productId, product_qty: productQty })
                    }).then(() => {
                        const authModal = document.getElementById('authModal');
                        if(authModal) {
                            authModal.classList.remove('hidden');
                        } else {
                            window.location.href = "/login";
                        }
                    });
                    return; 
                }

                // --- LOGGED IN LOGIC ---
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.disabled = true;

                fetch("{{ route('cart.add') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({ 
                        product_id: productId,
                        product_qty: productQty,
                        product_variant_id: null 
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Server returned ' + response.status);
                    return response.json();
                })
                .then(data => {
                    this.innerHTML = originalHTML;
                    this.disabled = false;

                    if(data.status === 'success') {
                        if(data.cart_count !== undefined) updateCartIcon(data.cart_count);

                        const formattedPrice = parseFloat(rawPrice).toLocaleString(undefined, {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        const currencySymbol = "{{ session('currency') === 'USD' ? '$' : 'Rs.' }}";

                        // --- DARAZ LOGIC: CUSTOMIZE ALERT BASED ON STOCK ---
                        const alertTitle = isWishlist ? 'Saved to Wishlist' : 'Added to Cart';
                        const alertIcon  = isWishlist ? 'info' : 'success';
                        const alertText  = isWishlist 
                            ? 'This item is out of stock but has been saved in your cart. We will notify you when it returns!' 
                            : 'Product has been successfully added to your shopping cart.';

                        Swal.fire({
                            title: alertTitle,
                            html: `
                                <p style="font-size:14px; color:#5b2c2c; margin-bottom:15px; text-align:center;">${alertText}</p>
                                <div style="display:flex; align-items:center; gap:16px; margin-top:10px; background:rgba(255,255,255,0.5); padding:12px; border-radius:12px; border:1px solid rgba(91,44,44,0.1);">
                                    <img src="${productImage}" style="width:65px; height:65px; object-fit:cover; border-radius:10px; border:1px solid #ddd;">
                                    <div style="text-align:left;">
                                        <div style="font-weight:600; color:#5b2c2c; font-size:14px; line-height:1.3;">${productName}</div>
                                        <div style="color:#e95b2c; font-weight:700; font-size:16px; margin-top:4px;">${currencySymbol} ${formattedPrice}</div>
                                    </div>
                                </div>`,
                            icon: alertIcon,
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-shopping-cart"></i> View Cart',
                            cancelButtonText: 'Continue Shopping',
                            customClass: { popup: 'cart-alert-popup' }
                        }).then((result) => {
                            if (result.isConfirmed) window.location.href = "{{ route('cart.show') }}";
                        });
                    }
                })
                .catch(error => {
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Connection Issue', 
                        text: 'Could not reach the server. Please check your connection.',
                        customClass: { popup: 'cart-alert-popup' }
                    });
                });
            });
        });
    });
</script>



@endsection