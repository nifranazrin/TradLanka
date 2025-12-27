@extends('layouts.frontend')

@section('content')

{{-- ================================================= --}}
{{-- 1. CUSTOM CSS (POPUP + SLIDER)                    --}}
{{-- ================================================= --}}
<style>
    /* --- SweetAlert Popup Styles --- */
    .cart-alert-popup {
        background: #dfdacc !important;
        border-radius: 16px !important;
        padding: 1.5rem 1.6rem !important;
        border: 1px solid #efeae8;
    }
    .cart-alert-popup .swal2-title {
        font-size: 22px !important;
        font-weight: 800 !important;
        color: #2c1111 !important;
        letter-spacing: 0.3px;
        margin-bottom: 8px !important;
    }
    .cart-alert-popup .swal2-icon {
        transform: scale(0.7);
        margin-top: 5px !important;
        border-color: #5b2c2c !important;
    }
    .cart-alert-popup p { margin: 0; }
    
    /* Confirm Button (View Cart) */
    .cart-alert-popup .swal2-confirm {
        background: #bb8f8f !important;
        color: #300f07 !important;
        border-radius: 8px !important;
        font-weight: 600;
        padding: 10px 24px !important;
        box-shadow: 0 4px 6px rgba(91, 44, 44, 0.2) !important;
    }
    
    /* Cancel Button (Continue Shopping) */
    .cart-alert-popup .swal2-cancel {
        background: #cabf59 !important;
        color: #191a13 !important;
        border-radius: 8px !important;
        font-weight: 600;
        padding: 10px 18px !important;
    }
    .cart-alert-popup .swal2-close { color: #5b2c2c !important; }

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
  

/* 1. The Card: Subtle shadow, more breathing room */
.full-card-height {
    display: flex;
    flex-direction: column;
    height: 85%;
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
   {{-- SECTION: BEST SELLERS --}}
<section class="mt-12 mb-4"> {{-- Reduced mb-12 to mb-4 --}}
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl md:text-2xl font-bold text-[#5b2c2c]">Best Sellers</h2>
       <a href="{{ route('search.page', ['query' => 'best sellers']) }}" 
       class="text-[#5b2c2c] font-bold hover:underline text-base md:text-lg transition-all"> {{-- Increased font size --}}
        Browse more →
       </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 w-full">
    {{-- ✅ Uses $bestSellers variable --}}
    @foreach ($bestSellers->take(5) as $item)
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-100 full-card-height">
            <a href="{{ route('product.show', $item->slug) }}" class="unified-image-wrapper">
                <img src="{{ $item->image ? asset('storage/' . $item->image) : asset('images/placeholder.jpg') }}" alt="{{ $item->name }}">
            </a>
            
            <div class="p-4 text-center flex-grow flex flex-col">
                <div>
                    <h3 class="font-semibold text-gray-800 unified-title-height">
                        {{ $item->name }}
                    </h3>

                    {{-- Review Stars logic: Fixed height keeps boxes level --}}
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
                        {{ $item->display_price }}
                    </p>
                </div>
                
                <button type="button"
                        class="addToCartBtn custom-cart-btn active:scale-95"
                        data-id="{{ $item->id }}" 
                        data-name="{{ $item->name }}" 
                        data-price="{{ $item->display_price }}" 
                        data-image="{{ $item->image ? asset('storage/' . $item->image) : asset('images/placeholder.jpg') }}">
                    Add to Cart
                </button>
            </div>
        </div>
    @endforeach
    </div>
</section>

{{-- SECTION: NEW ARRIVALS --}}
<section class="mt-12 mb-4"> {{-- Reduced mb-12 to mb-4 --}}
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl md:text-2xl font-bold text-[#5b2c2c]">New Arrivals</h2>
        <a href="{{ route('search.page', ['query' => 'new arrivals']) }}" class="text-[#5b2c2c] font-bold hover:underline text-base md:text-lg transition-all">Browse more →</a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 w-full">
    {{--  Uses $products variable --}}
    @foreach ($products->take(5) as $item)
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-100 full-card-height">
            <a href="{{ route('product.show', $item->slug) }}" class="unified-image-wrapper">
                <img src="{{ $item->image ? asset('storage/' . $item->image) : asset('images/placeholder.jpg') }}" alt="{{ $item->name }}">
            </a>
            
            <div class="p-4 text-center flex-grow flex flex-col">
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
                        {{ $item->display_price }}
                    </p>
                </div>
                
                <button type="button"
                        class="addToCartBtn custom-cart-btn active:scale-95"
                        data-id="{{ $item->id }}" 
                        data-name="{{ $item->name }}" 
                        data-price="{{ $item->display_price }}" 
                        data-image="{{ $item->image ? asset('storage/' . $item->image) : asset('images/placeholder.jpg') }}">
                    Add to Cart
                </button>
            </div>
        </div>
    @endforeach
    </div>
</section>
        {{-- SECTION: POPULAR CATEGORIES --}}
        <section class="mt-16 mb-12">
            <h2 class="text-2xl font-bold text-[#5b2c2c] mb-8 text-left">Popular Categories</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-4">
                <div class="flex items-center justify-between bg-green-50 rounded-2xl p-6 shadow-sm hover:shadow-lg transition">
                    <div>
                        <p class="text-green-700 font-medium">Ceylon’s Pride!</p>
                        <h3 class="text-2xl font-extrabold text-gray-800">Tea</h3>
                    </div>
                    <img src="https://via.placeholder.com/100x100?text=Tea" class="w-24 h-24 object-contain">
                </div>
                <div class="flex items-center justify-between bg-orange-50 rounded-2xl p-6 shadow-sm hover:shadow-lg transition">
                    <div>
                        <p class="text-orange-600 font-medium">Taste of Tradition!</p>
                        <h3 class="text-2xl font-extrabold text-gray-800">Spices</h3>
                    </div>
                    <img src="https://via.placeholder.com/100x100?text=Spices" class="w-24 h-24 object-contain">
                </div>
                <div class="flex items-center justify-between bg-yellow-50 rounded-2xl p-6 shadow-sm hover:shadow-lg transition">
                    <div>
                        <p class="text-yellow-600 font-medium">Crafted with Heritage!</p>
                        <h3 class="text-2xl font-extrabold text-gray-800">Handicrafts</h3>
                    </div>
                    <img src="https://via.placeholder.com/100x100?text=Handicraft" class="w-24 h-24 object-contain">
                </div>
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


        {{-- SECTION: RECOMMENDED FOR YOU (Fixed & Unified) --}}
    
        <section class="mt-10 mb-16">
            <h2 class="text-2xl font-bold text-[#5b2c2c] mb-6 text-left">Recommended for You</h2>
            <div class="overflow-hidden relative">
                {{-- Slider Container --}}
                <div id="recommendSlider" class="flex space-x-6 animate-scroll will-change-transform">
                    @foreach (range(1, 10) as $i)
                        @php
                            // Mock Data for Recommended Items
                            $recName = "Recommended Item " . $i;
                            $recPrice = "Rs " . (1500 + ($i * 50)) . ".00";
                            $recImg = "https://via.placeholder.com/300x200?text=Rec+" . $i;
                        @endphp
                        <div class="min-w-[220px] bg-white rounded-lg shadow-md overflow-hidden transform transition hover:-translate-y-1 hover:shadow-lg">
                            <img src="{{ $recImg }}" class="w-full h-36 object-cover">
                            <div class="p-4 text-center">
                                <h3 class="font-semibold text-gray-800 text-sm md:text-base">{{ $recName }}</h3>
                                <p class="text-[#5b2c2c] font-bold mt-1">{{ $recPrice }}</p>
                                
                                {{-- ADD TO CART BUTTON (Recommended - Matches others now) --}}
                                <button type="button" 
                                        class="addToCartBtn mt-3 bg-[#5b2c2c] text-white px-4 py-1 rounded hover:bg-[#4a2424] text-sm flex items-center justify-center mx-auto transition" 
                                        data-id="{{ 100 + $i }}" {{-- Unique ID for Rec items --}}
                                        data-name="{{ $recName }}"
                                        data-price="{{ $recPrice }}"
                                        data-image="{{ $recImg }}">
                                    <i class="fas fa-cart-plus mr-1"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

    </div>
</div>

{{-- ================================================= --}}
{{-- 3. FLOATING BUTTONS                               --}}
{{-- ================================================= --}}
<div class="fixed bottom-6 right-6 flex flex-col items-center space-y-3 z-50">
    <a href="#" id="chatbotBtn" class="bg-[#5b2c2c] text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center hover:scale-110 transition-transform duration-300">
        <i class="fas fa-robot text-xl"></i>
    </a>
    <a href="https://wa.me/94757679793" target="_blank" class="bg-[#25D366] text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center hover:scale-110 transition-transform duration-300">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>
</div>

{{-- ================================================= --}}
{{-- 4. SCRIPTS                                        --}}
{{-- ================================================= --}}
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize Animations
    AOS.init({ once: true, offset: 100 });

    // Auth Status
    const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
    
    // Helper: Update Header Cart Icon
    function updateCartIcon(count) {
        const badge = document.getElementById('cart-badge'); 
        if(badge) {
            badge.innerText = count;
            badge.classList.remove('hidden');
        }
    }

    // ============================================
    // UNIVERSAL ADD TO CART LOGIC
    // ============================================
    const buttons = document.querySelectorAll('.addToCartBtn');

    buttons.forEach(btn => {
        btn.addEventListener('click', function(event) {
            event.preventDefault(); 
            
            // Get Product Data from Button Attributes
            const productId   = this.getAttribute('data-id'); 
            const productName = this.getAttribute('data-name'); 
            const productPrice= this.getAttribute('data-price'); 
            const productImage= this.getAttribute('data-image'); 
            const productQty  = 1; 

            // --- Guest Logic ---
            if (!isLoggedIn) {
                localStorage.setItem('pendingCartItem', JSON.stringify({
                    id: productId,
                    qty: productQty
                }));
                // Open Login Modal
                if (document.getElementById('authModal')) {
                    document.getElementById('authModal').classList.remove('hidden');
                } else if (typeof $ !== 'undefined' && $('#authModal').length) {
                    $('#authModal').modal('show');
                }
                return; 
            }

            // --- Logged In Logic ---
            // Visual feedback on button
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;

            // AJAX Request
            fetch("{{ route('cart.add') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ 
                    product_id: productId,
                    product_qty: productQty
                })
            })
            .then(response => response.json())
            .then(data => {
                // Reset Button
                this.innerHTML = originalHTML;
                this.disabled = false;

                if(data.status === 'success') {
                    // Update Badge
                    if(data.cart_count !== undefined) {
                        updateCartIcon(data.cart_count);
                    }

                    // ✅ SHOW PROFESSIONAL POPUP
                    Swal.fire({
                        title: 'Added to Cart',
                        html: `
                            <div style="display:flex; align-items:center; gap:16px; margin-top:10px;">
                                <img src="${productImage}" 
                                    style="width:70px; height:70px; object-fit:cover; border-radius:10px; border:1px solid #ddd;">
                                <div style="text-align:left;">
                                    <div style="font-weight:600; color:#333; font-size:15px; line-height:1.3;">
                                        ${productName}
                                    </div>
                                    <div style="color:#5b2c2c; font-weight:700; font-size:15px; margin-top:4px;">
                                        ${productPrice}
                                    </div>
                                </div>
                            </div>
                        `,
                        icon: 'success',
                        showCloseButton: true,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-shopping-cart"></i> View Cart',
                        cancelButtonText: 'Continue Shopping',
                        reverseButtons: true,
                        width: 440,
                        padding: '1.5em',
                        backdrop: 'rgba(0,0,0,0.45)',
                        customClass: {
                            popup: 'cart-alert-popup'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('cart.show') }}";
                        }
                    });

                }
                else if(data.status === 'exists') {
                    // Item Exists Alert
                    Swal.fire({
                        icon: 'info',
                        title: 'Already in Cart',
                        text: data.message,
                        confirmButtonText: 'OK',
                        customClass: {
                            popup: 'cart-alert-popup'
                        }
                    });
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                this.innerHTML = originalHTML;
                this.disabled = false;
            });
        });
    });
});
</script>

{{-- ================= ORDER SUCCESS SWEETALERT ================= --}}
@if(session('order_success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Thank You!',
        html: `
            <p style="font-size:16px; line-height:1.6;">
                Thank you for choosing <b>TradLanka</b> ♥ <br>
                Your order has been placed successfully.
            </p>
        `,
        confirmButtonText: 'Continue Shopping',
        confirmButtonColor: '#5b2c2c',
        background: '#fffaf0',
        color: '#5b2c2c',
        iconColor: '#d4af37',
        allowOutsideClick: false
    });
});
</script>
@endif


@endsection