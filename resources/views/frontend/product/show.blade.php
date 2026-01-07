@extends('layouts.frontend')

@section('content')
{{-- Sticky Header --}}
@include('frontend.partials.header')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $images = $product->images ?? collect();
    $mainPathRaw = $product->image ?? optional($images->first())->path;
    $mainPath = $mainPathRaw ? preg_replace('/^public\//', '', $mainPathRaw) : null;

    if ($mainPath) {
        $mainUrl = Str::startsWith($mainPath, ['http://','https://']) ? $mainPath : Storage::url($mainPath);
    } else {
        $mainUrl = 'https://via.placeholder.com/900x700?text=No+Image';
    }

    // Determine if this is a variable product or simple product
    $hasVariants = $product->variants && $product->variants->count() > 0;
    
    // Logic for Approved Reviews
    $approvedReviews = $product->reviews->where('status', 1);
@endphp

{{-- 1. LIGHTBOX MODAL --}}
<div id="imageLightbox" class="fixed inset-0 z-[150] hidden bg-black bg-opacity-90 flex items-center justify-center p-4" onclick="closeLightbox()">
    <div class="relative max-w-4xl w-full h-full flex items-center justify-center">
        <img id="lightboxImg" src="" class="max-h-full max-w-full object-contain rounded-lg shadow-2xl">
        <button class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300">&times;</button>
        <p class="absolute bottom-4 text-white text-sm bg-black bg-opacity-50 px-3 py-1 rounded">Click anywhere to close</p>
    </div>
</div>

<div class="bg-[#f8f4f1] min-h-screen pt-6 pb-20">
    {{-- Main Container --}}
    <div class="max-w-6xl mx-auto px-6 lg:px-10 mt-6">

        {{-- BREADCRUMB --}}
        <nav aria-label="breadcrumb" class="mb-6 text-sm text-gray-600 w-full">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ url('/') }}" class="hover:text-[#5b2c2c] transition flex items-center gap-1">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                
                @if(isset($breadcrumbCategory))
                    <li><span class="text-gray-400">/</span></li>
                    <li>
                        <a href="{{ url('category/'.$breadcrumbCategory->slug) }}" class="hover:text-[#5b2c2c] transition font-medium">
                            {{ $breadcrumbCategory->name }}
                        </a>
                    </li>
                @endif
        
                <li><span class="text-gray-400">/</span></li>
                <li class="font-bold text-[#5b2c2c] truncate max-w-[200px]">{{ $product->name }}</li>
            </ol>
        </nav>
        
        {{-- GRID CONTENT --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

            {{-- LEFT COLUMN: MAIN IMAGE + GALLERY --}}
            <div>
                <div class="w-full h-[420px] rounded-xl overflow-hidden cursor-zoom-in group relative bg-white shadow-sm border border-gray-100">
                    <img src="{{ $mainUrl }}"
                         id="mainImage"
                         class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105"
                         alt="{{ $product->name }}">
                </div>

                {{-- Thumbnails --}}
                <div class="flex space-x-3 mt-4 overflow-x-auto py-2">
                    <img src="{{ $mainUrl }}"
                         class="gallery-thumb w-20 h-20 rounded-lg object-cover border-2 border-[#5b2c2c] cursor-pointer flex-shrink-0 hover:opacity-80 transition bg-white"
                         data-src="{{ $mainUrl }}">

                    @if($images->count())
                        @foreach($images->sortBy('sort_order')->values() as $gimg)
                            @php
                                $gpath = $gimg->path ? preg_replace('/^public\//', '', $gimg->path) : null;
                                $gurl = $gpath ? (Str::startsWith($gpath, ['http','https']) ? $gpath : Storage::url($gpath)) : '';
                            @endphp
                            @if($gurl)
                                <img src="{{ $gurl }}"
                                     class="gallery-thumb w-20 h-20 rounded-lg object-cover border-2 border-transparent hover:border-[#5b2c2c] cursor-pointer flex-shrink-0 transition bg-white"
                                     data-src="{{ $gurl }}">
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- RIGHT COLUMN: PRODUCT DETAILS --}}
            <div class="text-gray-800">
                <h1 class="text-3xl font-extrabold text-[#5b2c2c] mb-2">{{ $product->name }}</h1>
                
                <div class="flex items-center gap-2 mb-4">
                    <div class="flex text-yellow-500 text-sm">
                        @php 
                            $reviewCount = $approvedReviews->count();
                            $avgRating = $reviewCount > 0 ? $approvedReviews->avg('rating') : 0; 
                        @endphp
                        @for($i = 1; $i <= 5; $i++)
                            <i class="{{ $i <= round($avgRating) ? 'fas' : 'far' }} fa-star"></i>
                        @endfor
                    </div>
                    <span class="text-sm text-gray-600">
                        ({{ number_format($avgRating, 1) }}) 
                        <a href="#reviews-section" class="ml-1 hover:underline text-[#5b2c2c] font-medium">
                            {{ $reviewCount }} Reviews
                        </a>
                    </span>
                </div>      

                
                        <div class="flex items-baseline gap-2 mb-4">
                            <span class="text-sm text-gray-500">Price:</span>
                            <div id="displayPrice" class="text-3xl font-bold text-[#e95b2c]">
                               
                                {{ session('currency') == 'USD' ? '$' : 'Rs.' }} {{ number_format($product->price, 2) }}
                            </div>
                        </div>

                <div id="stockDisplay" class="mb-6">
                    @if($product->stock > 0)
                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">In Stock</span>
                        <span class="text-xs text-gray-500 ml-2">({{ $product->stock }} available)</span>
                    @else
                        <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">Out of Stock</span>
                    @endif
                </div>

                <h3 class="text-lg font-semibold text-[#5b2c2c] mb-2">Description</h3>
                <p class="text-gray-700 leading-relaxed mb-6 text-sm">{!! nl2br(e($product->description)) !!}</p>

                @if ($hasVariants)
                    <div class="mt-5 p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
                        <label class="block text-sm font-bold text-gray-700 mb-3">
                            Select Size / Option: <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-3 flex-wrap" id="variantContainer">
                           <div class="flex gap-3 flex-wrap" id="variantContainer">
                                    @foreach ($product->variants as $variant)
                                        <button
                                            type="button"
                                            class="variantOption px-4 py-2 border border-gray-300 rounded-md text-sm font-medium transition-all
                                                hover:border-[#5b2c2c] hover:bg-gray-50
                                                {{ $variant->stock == 0 ? 'opacity-50 cursor-not-allowed bg-gray-100' : 'bg-white' }}"
                                            {{ $variant->stock == 0 ? 'disabled' : '' }}
                                            data-id="{{ $variant->id }}"
                                            data-price="{{ $variant->price }}" 
                                            data-stock="{{ $variant->stock }}"
                                            data-label="{{ $variant->unit_label }}">
                                            
                                            {{ $variant->unit_label }}
                                            
                                            @if($variant->stock == 0) 
                                                <span class="text-xs text-red-500">(Sold Out)</span> 
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                        <input type="hidden" id="selectedVariantId" value="">
                    </div>
                @endif

                <div class="mt-6">
                    <label class="block text-sm font-medium mb-2">Quantity</label>
                    <div class="inline-flex items-center border rounded-md bg-white shadow-sm">
                        <button id="decreaseQty" class="px-4 py-2 text-gray-600 hover:bg-gray-100 border-r transition">-</button>
                        <input id="productQty" type="text" value="1" class="w-16 text-center focus:outline-none py-2 font-semibold text-gray-700" readonly>
                        <button id="increaseQty" class="px-4 py-2 text-gray-600 hover:bg-gray-100 border-l transition">+</button>
                    </div>
                </div>

                <div class="mt-8 flex flex-col sm:flex-row gap-4">
                    <button id="addToCartBtn" data-id="{{ $product->id }}" 
                            class="flex-1 h-12 bg-[#5b2c2c] hover:bg-[#462020] text-white rounded-lg flex items-center justify-center gap-2 shadow-lg transition transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                    <button id="buyNowBtn" data-id="{{ $product->id }}"
                            class="flex-1 h-12 bg-[#d97706] hover:bg-[#b86504] text-white rounded-lg flex items-center justify-center gap-2 shadow-lg transition transform hover:-translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-bolt"></i> Buy Now
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="cartToast" class="hidden fixed bottom-6 right-6 bg-green-600 text-white px-6 py-4 rounded-lg shadow-xl z-50 flex items-center gap-3 animate-bounce">
    <i class="fas fa-check-circle text-xl"></i>
    <div>
        <h4 class="font-bold text-sm">Success</h4>
        <p id="toastMessage" class="text-sm">Added to cart!</p>
    </div>
</div>

{{-- ================= REVIEWS SECTION ================= --}}
<section id="reviews-section" class="mt-20">
    <div class="max-w-6xl mx-auto px-6">


@php
    $totalReviews = $product->reviews->count();
    $avgRating = $totalReviews ? round($product->reviews->avg('rating'), 1) : 0;

    $ratingCounts = [
        5 => $product->reviews->where('rating', 5)->count(),
        4 => $product->reviews->where('rating', 4)->count(),
        3 => $product->reviews->where('rating', 3)->count(),
        2 => $product->reviews->where('rating', 2)->count(),
        1 => $product->reviews->where('rating', 1)->count(),
    ];
@endphp

<div class="bg-white border rounded-xl p-6 mb-10">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">

        {{-- LEFT: AVERAGE --}}
        <div>
            <div class="text-4xl font-bold text-gray-800">
                {{ $avgRating }}<span class="text-lg text-gray-500">/5</span>
            </div>

            <div class="flex text-yellow-400 text-lg mt-2">
                @for($i=1;$i<=5;$i++)
                    <i class="{{ $i <= round($avgRating) ? 'fas' : 'far' }} fa-star"></i>
                @endfor
            </div>

            <p class="text-sm text-gray-500 mt-1">
                {{ $totalReviews }} Ratings
            </p>
        </div>

        {{-- RIGHT: DISTRIBUTION --}}
        <div class="space-y-2">
            @foreach([5,4,3,2,1] as $star)
                @php
                    $percent = $totalReviews
                        ? ($ratingCounts[$star] / $totalReviews) * 100
                        : 0;
                @endphp

                <div class="flex items-center gap-3 text-sm">
                    <span class="w-8 text-gray-600">{{ $star }}★</span>

                    {{-- FIXED BAR WIDTH --}}
                    <div class="flex-1 h-3 bg-gray-200 rounded overflow-hidden">
                <div class="h-3 bg-yellow-400" style="width: {{ $percent }}%"></div>
            </div>
                    <span class="text-gray-500 w-6">
                        {{ $ratingCounts[$star] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="bg-white border rounded-xl p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-6">
        Product Reviews
    </h3>

    @forelse($product->reviews as $review)
        <div class="border-b last:border-b-0 pb-6 mb-6 last:mb-0">

            <div class="flex justify-between items-center mb-2">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-sm text-gray-800">
                        {{ $review->is_anonymous ? 'Anonymous' : $review->user->name }}
                    </span>

                    <span class="flex items-center gap-1 text-green-600 text-xs">
                        <i class="fas fa-check-circle"></i>
                        Verified Purchase
                    </span>
                </div>

                <span class="text-xs text-gray-400">
                    {{ $review->created_at->format('d M Y') }}
                </span>
            </div>

            <div class="flex text-yellow-400 text-sm mb-2">
                @for($i=1;$i<=5;$i++)
                    <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                @endfor
            </div>

            <p class="text-gray-700 text-sm mb-2">
                {{ $review->comment }}
            </p>

            @if($review->image)
                <img src="{{ asset('storage/' . $review->image) }}"
                     class="mt-2 w-24 h-24 object-cover rounded-lg border">
            @endif
        </div>
    @empty
        <p class="text-gray-400 italic">
            No reviews yet.
        </p>
    @endforelse
</div>
    </div>
</section>



<script>
    // 1. LIGHTBOX LOGIC
    const lightbox = document.getElementById('imageLightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const mainImage = document.getElementById('mainImage');

    if(mainImage) {
        mainImage.addEventListener('click', function() {
            lightboxImg.src = this.src;
            lightbox.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    }

    window.closeLightbox = function() {
        lightbox.classList.add('hidden');
        document.body.style.overflow = 'auto';
    };

    // 2. MAIN PRODUCT LOGIC
    document.addEventListener('DOMContentLoaded', function () {
        const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
        const hasVariants = {{ $hasVariants ? 'true' : 'false' }};
        const currencySymbol = "{{ session('currency') == 'USD' ? '$' : 'Rs.' }}"; 
        
        const thumbs = document.querySelectorAll('.gallery-thumb');
        const variantBtns = document.querySelectorAll('.variantOption');
        const priceDisplay = document.getElementById('displayPrice');
        const stockDisplay = document.getElementById('stockDisplay');
        const hiddenVariantInput = document.getElementById('selectedVariantId');
        const qtyInput = document.getElementById('productQty');
        const addToCartBtn = document.getElementById('addToCartBtn');
        const buyNowBtn = document.getElementById('buyNowBtn');
        
        let currentStock = {{ $product->stock }}; 

        // --- TradLanka Attractive Theme Settings ---
        const tradLankaTheme = {
            background: '#fdf6e3',      // Butter Cream
            color: '#5b2c2c',           // Maroon Text
            confirmButtonColor: '#5b2c2c',
            iconColor: '#5b2c2c',
            // Tailwind classes for bigger text
            customClass: {
                title: 'text-3xl font-bold', 
                htmlContainer: 'text-xl',
                confirmButton: 'px-8 py-3 text-lg rounded-lg'
            }
        };

        // Thumbnail Switcher
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                mainImage.src = this.dataset.src;
                thumbs.forEach(t => t.classList.replace('border-[#5b2c2c]', 'border-transparent'));
                this.classList.replace('border-transparent', 'border-[#5b2c2c]');
            });
        });

        // Variant Selection
        variantBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                variantBtns.forEach(b => b.classList.remove('bg-[#5b2c2c]', 'text-white', 'border-[#5b2c2c]', 'ring-2'));
                this.classList.add('bg-[#5b2c2c]', 'text-white', 'border-[#5b2c2c]', 'ring-2');
                
                let price = parseFloat(this.dataset.price);
        const stock = parseInt(this.dataset.stock);

        // ✅ FIX: Apply exchange rate if currency is USD
        if (currencySymbol === '$') {
            const exchangeRate = 0.0032; // Must match FrontendController
            price = price * exchangeRate;
        }

        priceDisplay.innerText = currencySymbol + ' ' + price.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        
            hiddenVariantInput.value = this.dataset.id;
            currentStock = stock;
        });
    });

        // Quantity Controls
        document.getElementById('increaseQty').onclick = () => {
            let val = parseInt(qtyInput.value);
            if(val < currentStock) qtyInput.value = val + 1;
            else {
                Swal.fire({
                    ...tradLankaTheme,
                    title: 'Stock Limit',
                    text: 'Only ' + currentStock + ' items available.',
                    icon: 'warning'
                });
            }
        };

        document.getElementById('decreaseQty').onclick = () => {
            let val = parseInt(qtyInput.value);
            if(val > 1) qtyInput.value = val - 1;
        };

        function handleAddToCart(btn, isBuyNow) {
            // Check for Variant Selection
            if(hasVariants && !hiddenVariantInput.value) {
                Swal.fire({
                    ...tradLankaTheme,
                    title: 'Pick a Size!',
                    text: 'Please select an option before adding to cart.',
                    icon: 'info'
                });
                document.getElementById('variantContainer').scrollIntoView({behavior: 'smooth'});
                return;
            }

            // Check Login
            if(!isLoggedIn) {
                Swal.fire({
                    ...tradLankaTheme,
                    title: 'Login Required',
                    text: 'Please login to start shopping.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Login Now'
                }).then((result) => {
                    if (result.isConfirmed) window.location.href = "/login";
                });
                return;
            }

            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            btn.disabled = true;

            fetch("{{ route('cart.add') }}", {
                method: "POST",
                headers: { 
                    "X-CSRF-TOKEN": "{{ csrf_token() }}", 
                    "Content-Type": "application/json", 
                    "Accept": "application/json" 
                },
                body: JSON.stringify({ 
                    product_id: btn.dataset.id, 
                    product_qty: qtyInput.value, 
                    product_variant_id: hasVariants ? (hiddenVariantInput.value || null) : null 
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = originalText; 
                btn.disabled = false;

                if(data.status === 'success' || data.status === 'exists') {
                    if(isBuyNow) { 
                        window.location.href = "{{ route('cart.show') }}"; 
                    } else {
                        Swal.fire({
                            ...tradLankaTheme,
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            timer: 2500,
                            timerProgressBar: true
                        });
                    }
                } else { 
                    Swal.fire({
                        ...tradLankaTheme,
                        title: 'Oops!',
                        text: data.message || 'Something went wrong',
                        icon: 'error'
                    });
                }
            })
            .catch(err => { 
                btn.innerHTML = originalText; 
                btn.disabled = false; 
                Swal.fire({
                    ...tradLankaTheme,
                    title: 'Server Error',
                    text: 'Could not connect to the server.',
                    icon: 'error'
                });
            });
        }

        if(addToCartBtn) addToCartBtn.onclick = () => handleAddToCart(addToCartBtn, false);
        if(buyNowBtn) buyNowBtn.onclick = () => handleAddToCart(buyNowBtn, true);
    });
</script>
@endsection