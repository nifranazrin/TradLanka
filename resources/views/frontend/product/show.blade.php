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

                <div class="mb-6">
    <button onclick="shareProduct()" class="flex items-center gap-2 text-sm font-bold text-[#5b2c2c] hover:text-[#e95b2c] transition-all bg-white px-5 py-2.5 rounded-xl shadow-sm border border-gray-200 hover:shadow-md active:scale-95">
        <i class="fas fa-share-alt"></i> Share this Product
    </button>
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

                 
                {{-- ✅ PRODUCT BUYING SECTION --}}
@if ($hasVariants)
    <div class="mt-8 p-6 bg-white rounded-2xl border border-gray-100 shadow-sm">
        <label class="flex items-center gap-2 text-sm font-bold text-gray-700 mb-4">
            <i class="fas fa-layer-group text-[#5b2c2c]"></i> 
            Select Size / Option: <span class="text-red-500">*</span>
        </label>
        
        <div class="flex gap-3 flex-wrap" id="variantContainer">
            @foreach ($product->variants as $variant)
                <button
                    type="button"
                    class="variantOption px-5 py-2.5 border-2 border-gray-100 rounded-xl text-sm font-bold transition-all duration-200
                        hover:border-[#5b2c2c] hover:bg-gray-50 flex flex-col items-center
                        {{ $variant->stock == 0 ? 'opacity-40 cursor-not-allowed bg-gray-50 border-dashed' : 'bg-white shadow-sm' }}"
                    {{ $variant->stock == 0 ? 'disabled' : '' }}
                    data-id="{{ $variant->id }}"
                    data-price="{{ $variant->price }}" 
                    data-stock="{{ $variant->stock }}"
                    data-label="{{ $variant->unit_label }}">
                    
                    <span>{{ $variant->unit_label }}</span>
                    
                    @if($variant->stock == 0) 
                        <span class="text-[10px] text-red-500 font-bold uppercase">Sold Out</span> 
                    @endif
                </button>
            @endforeach
        </div>
        <input type="hidden" id="selectedVariantId" value="">
    </div>
@endif

{{-- ✅ MODERN QUANTITY & BUTTON BAR --}}
<div class="mt-8 flex flex-col md:flex-row items-end gap-6">
    <div class="flex flex-col items-center md:items-start gap-2 w-full md:w-auto">
        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Quantity</label>
        <div class="inline-flex items-center border-2 border-gray-200 rounded-xl bg-white overflow-hidden shadow-sm">
            <button id="decreaseQty" class="w-12 h-12 flex items-center justify-center text-gray-400 hover:bg-gray-50 hover:text-[#5b2c2c] transition-colors border-r">-</button>
            <input id="productQty" type="text" value="1" class="w-14 text-center focus:outline-none h-12 font-bold text-gray-800" readonly>
            <button id="increaseQty" class="w-12 h-12 flex items-center justify-center text-gray-400 hover:bg-gray-50 hover:text-[#5b2c2c] transition-colors border-l">+</button>
        </div>
    </div>

    <div class="flex flex-1 gap-4 w-full h-14">
        {{-- ADD TO CART WITH SHOPPING CART ICON --}}
        <button id="addToCartBtn" data-id="{{ $product->id }}" 
                class="flex-1 h-full bg-white border-2 border-[#5b2c2c] text-[#5b2c2c] font-bold rounded-xl flex items-center justify-center gap-3 hover:bg-[#5b2c2c] hover:text-white transition-all duration-300 shadow-md transform active:scale-95">
            <i class="fas fa-shopping-cart"></i> Add to Cart
        </button>
        <button id="buyNowBtn" data-id="{{ $product->id }}"
                class="flex-1 h-full bg-[#d97706] text-white font-bold rounded-xl flex items-center justify-center gap-3 hover:bg-[#b86504] transition-all duration-300 shadow-md transform active:scale-95">
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
            // 1. Filter only approved reviews (status 1) to ensure data accuracy
            $approvedReviews = $product->reviews->where('status', 1);
            
            // 2. Calculate global totals and average
            $totalReviews = $approvedReviews->count();
            $avgRating = $totalReviews ? round($approvedReviews->avg('rating'), 1) : 0;

            // 3. Count distribution for the star bars
            $ratingCounts = [
                5 => $approvedReviews->where('rating', 5)->count(),
                4 => $approvedReviews->where('rating', 4)->count(),
                3 => $approvedReviews->where('rating', 3)->count(),
                2 => $approvedReviews->where('rating', 2)->count(),
                1 => $approvedReviews->where('rating', 1)->count(),
            ];
        @endphp

        {{-- SUMMARY CARD: AVERAGE & DISTRIBUTION --}}
        <div class="bg-white border rounded-xl p-8 mb-10 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">

                {{-- LEFT: TOTAL AVERAGE --}}
                <div class="text-center md:text-left">
                    <h4 class="text-gray-500 uppercase tracking-wider text-xs font-bold mb-2">Overall Rating</h4>
                    <div class="text-5xl font-extrabold text-gray-800">
                        {{ $avgRating }}<span class="text-xl text-gray-400">/5</span>
                    </div>

                    <div class="flex justify-center md:justify-start text-yellow-400 text-xl mt-3">
                        @for($i=1;$i<=5;$i++)
                            <i class="{{ $i <= round($avgRating) ? 'fas' : 'far' }} fa-star"></i>
                        @endfor
                    </div>

                    <p class="text-sm text-gray-500 mt-2 font-medium">
                        Based on {{ $totalReviews }} Verified Ratings
                    </p>
                </div>

                {{-- RIGHT: PROGRESS BARS --}}
                <div class="space-y-3">
                    @foreach([5,4,3,2,1] as $star)
                        @php
                            $percent = $totalReviews ? ($ratingCounts[$star] / $totalReviews) * 100 : 0;
                        @endphp

                        <div class="flex items-center gap-4 text-sm">
                            <span class="w-10 font-bold text-gray-600">{{ $star }} Star</span>
                            <div class="flex-1 h-2.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-yellow-400 transition-all duration-500" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="text-gray-400 w-8 text-right font-medium">
                                {{ $ratingCounts[$star] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- DETAILED REVIEWS LIST --}}
        <div class="bg-white border rounded-xl p-8 shadow-sm">
            <h3 class="text-xl font-bold text-gray-800 mb-8 flex items-center gap-2">
                <i class="fas fa-comments text-[#5b2c2c]"></i>
                Customer Feedback
            </h3>

            @forelse($approvedReviews->sortByDesc('created_at') as $review)
                <div class="border-b last:border-b-0 pb-8 mb-8 last:mb-0">
                    
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <span class="font-bold text-gray-900">
                                    {{ $review->is_anonymous ? 'Anonymous User' : $review->user->name }}
                                </span>
                                <span class="bg-green-50 text-green-700 text-[10px] uppercase font-extrabold px-2 py-0.5 rounded-md border border-green-100 flex items-center gap-1">
                                    <i class="fas fa-check-circle"></i> Verified
                                </span>
                            </div>
                            
                            {{-- STAR RATING --}}
                            <div class="flex text-yellow-400 text-xs">
                                @for($i=1;$i<=5;$i++)
                                    <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                @endfor
                            </div>
                        </div>

                        <span class="text-xs font-medium text-gray-400 bg-gray-50 px-2 py-1 rounded">
                            {{ $review->created_at->format('M d, Y') }}
                        </span>
                    </div>

                    {{-- REVIEW CONTENT --}}
                    <div class="pl-0 md:pl-2">
                        @if($review->comment)
                            <p class="text-gray-700 leading-relaxed text-sm italic">
                                "{{ $review->comment }}"
                            </p>
                        @else
                            {{-- Placeholder for star-only reviews so the UI stays balanced --}}
                            <p class="text-gray-400 text-xs italic">
                                User submitted a {{ $review->rating }}-star rating without a written comment.
                            </p>
                        @endif

                        {{-- REVIEW IMAGE --}}
                        @if($review->image)
                            <div class="mt-4 group relative inline-block">
                                <img src="{{ asset('storage/' . $review->image) }}"
                                     class="w-28 h-28 object-cover rounded-xl border-2 border-gray-100 shadow-sm cursor-zoom-in hover:opacity-90 transition-opacity">
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 pointer-events-none">
                                    <i class="fas fa-search-plus text-white text-lg"></i>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-12 text-center">
                    <i class="far fa-star-half-alt text-4xl text-gray-200 mb-3"></i>
                    <p class="text-gray-400 font-medium">No reviews yet. Be the first to share your thoughts!</p>
                </div>
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
        const hiddenVariantInput = document.getElementById('selectedVariantId');
        const qtyInput = document.getElementById('productQty');
        const addToCartBtn = document.getElementById('addToCartBtn');
        const buyNowBtn = document.getElementById('buyNowBtn');
        
        let currentStock = {{ $product->stock }}; 

        const tradLankaTheme = {
            background: '#fdf6e3',
            color: '#5b2c2c',
            confirmButtonColor: '#5b2c2c',
            iconColor: '#5b2c2c',
            customClass: {
                title: 'text-3xl font-bold', 
                htmlContainer: 'text-xl',
                confirmButton: 'px-8 py-3 text-lg rounded-lg',
                popup: 'cart-alert-popup'
            }
        };

        // ✅ HELPER: Update Header Cart Icon (Sync with Homepage ID)
        function updateCartIcon(count) {
            const badge = document.getElementById('cart-badge'); 
            if(badge) {
                badge.innerText = count;
                badge.classList.remove('hidden'); // Ensure it shows if it was hidden
                
                // Pop Animation
                badge.style.transition = "transform 0.3s ease";
                badge.style.transform = "scale(1.5)";
                setTimeout(() => { badge.style.transform = "scale(1)"; }, 300);
            }
        }

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
                variantBtns.forEach(b => b.classList.remove('active-variant', 'ring-2', 'ring-[#5b2c2c]'));
                this.classList.add('active-variant', 'ring-2', 'ring-[#5b2c2c]');
                
                let price = parseFloat(this.dataset.price);
                const stock = parseInt(this.dataset.stock);
                
                if (currencySymbol === '$') {
                    price = price * 0.0032; 
                }

                priceDisplay.innerText = currencySymbol + ' ' + price.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                
                hiddenVariantInput.value = this.dataset.id;
                currentStock = stock;
                qtyInput.value = 1;
            });
        });

        // Quantity Controls
        document.getElementById('increaseQty').onclick = () => {
            let val = parseInt(qtyInput.value);
            if(val < currentStock) qtyInput.value = val + 1;
            else {
                Swal.fire({ ...tradLankaTheme, title: 'Stock Limit', icon: 'warning' });
            }
        };

        document.getElementById('decreaseQty').onclick = () => {
            let val = parseInt(qtyInput.value);
            if(val > 1) qtyInput.value = val - 1;
        };


        // ✅ THE ACTION FUNCTION
        function handleAddToCart(btn, isBuyNow) {
            if(hasVariants && !hiddenVariantInput.value) {
                Swal.fire({ title: 'Pick a Size!', icon: 'info' });
                return;
            }

            if(!isLoggedIn) {
                window.location.href = "/login";
                return;
            }

            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
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
                    product_variant_id: hasVariants ? hiddenVariantInput.value : null 
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = originalHTML; 
                btn.disabled = false;

                if (data.status === 'success') {
                    // Update Header
                    if (data.cart_count !== undefined) {
                        updateCartIcon(data.cart_count); 
                    }

                    if (isBuyNow) { 
                        window.location.href = "{{ route('cart.show') }}"; 
                    } else {
                        Swal.fire({
                            ...tradLankaTheme,
                            title: 'Added to Cart',
                            text: data.message,
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-shopping-cart"></i> View Cart',
                            cancelButtonText: 'Continue Shopping'
                        }).then((result) => {
                            if (result.isConfirmed) window.location.href = "{{ route('cart.show') }}";
                        });
                    }
                } else if (data.status === 'guest') {
                    window.location.href = data.url;
                }
            })
            .catch(err => { 
                btn.innerHTML = originalHTML; 
                btn.disabled = false; 
                console.error("Cart Error:", err);
            });
        }

        if(addToCartBtn) addToCartBtn.onclick = () => handleAddToCart(addToCartBtn, false);
        if(buyNowBtn) buyNowBtn.onclick = () => handleAddToCart(buyNowBtn, true);
    });

function shareProduct() {
        const productTitle = "{{ $product->name }}";
        const productUrl = window.location.href;

        // Check if the browser supports native sharing (mostly Mobile)
        if (navigator.share) {
            navigator.share({
                title: productTitle,
                text: 'Check out this product: ' + productTitle,
                url: productUrl,
            })
            .catch((error) => console.log('Error sharing', error));
        } else {
            // Fallback for Desktop: Copy to Clipboard
            const tempInput = document.createElement('input');
            document.body.appendChild(tempInput);
            tempInput.value = productUrl;
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);

            // Notify User using your existing theme
            Swal.fire({
                background: '#fdf6e3',
                color: '#5b2c2c',
                icon: 'success',
                title: 'Link Copied!',
                text: 'The product link has been copied to your clipboard.',
                confirmButtonColor: '#5b2c2c',
                timer: 2000,
                showConfirmButton: false
            });
        }
    }

</script>

<style>
    .variantOption.active-variant {
        border-color: #5b2c2c !important;
        background-color: #fdf6e3 !important;
        color: #5b2c2c !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(91, 44, 44, 0.1);
    }
    #addToCartBtn:hover i { animation: trolley-shake 0.5s ease infinite; }
    @keyframes trolley-shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-2px); }
        50% { transform: translateX(2px); }
    }
</style>
@endsection