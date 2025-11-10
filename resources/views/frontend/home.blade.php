@extends('layouts.frontend')

@section('content')

{{-- Sticky Header --}}
@include('frontend.partials.header')

{{--  Floating Toast Message (added, no design changed) --}}
<div id="cartToast" 
     class="hidden fixed bottom-6 right-6 bg-green-600 text-white px-5 py-3 rounded-lg shadow-lg z-50 transition-all duration-500 flex items-center space-x-2">
    <i class="fas fa-check-circle text-white text-lg"></i>
    <span id="toastMessage">Added to cart!</span>
</div>

{{-- Main Content --}}
<main class="w-full mt-0 pt-0">

    {{-- Hero Banner (Full Width) --}}
    <div class="w-full mt-0">
        @include('frontend.partials.banner')
    </div>

    {{-- Page Content with Padding --}}
    <div class="px-6 lg:px-10">

        {{-- SHOP BY CATEGORY (Dynamic) --}}
        <section class="mt-10 mb-12">
            <h2 class="text-2xl font-bold text-[#5b2c2c] mb-6 text-center">Shop by Category</h2>

            @if($categories->isEmpty())
                <p class="text-center text-gray-500">No categories available yet.</p>
            @else
                <div class="flex space-x-6 overflow-x-auto pb-4 justify-center">
                    @foreach ($categories as $category)
                        <div class="flex-shrink-0 flex flex-col items-center cursor-pointer group">
                            <div class="w-24 h-24 rounded-full overflow-hidden bg-[#f7f0ef] shadow-md hover:scale-105 transition-transform">
                                @if ($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-full h-full object-cover group-hover:opacity-90">
                                @else
                                    <img src="https://via.placeholder.com/100x100?text={{ urlencode($category->name) }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <p class="mt-2 text-sm font-medium text-gray-700 group-hover:text-[#5b2c2c] transition-colors">
                                {{ $category->name }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- BEST SELLERS SECTION --}}
        <section class="mt-12">
            <h2 class="text-2xl font-bold text-[#5b2c2c] mb-6 text-left">Best Sellers</h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-x-3 gap-y-6 w-full">
                @foreach (range(1, 10) as $i)
                    <div class="bg-white shadow-md rounded-lg overflow-hidden transform transition hover:-translate-y-1 hover:shadow-lg w-full">
                        <img src="https://via.placeholder.com/300x200?text=Product+{{ $i }}" alt="Product {{ $i }}" class="w-full h-56 md:h-60 object-cover">
                        <div class="p-4 text-center">
                            <h3 class="font-semibold text-gray-800 text-base md:text-lg">Product {{ $i }}</h3>
                            <p class="text-[#5b2c2c] font-bold mt-1 text-base">$ {{ 20 + $i }}.00</p>
                            <div class="flex justify-center items-center text-yellow-500 mt-1">
                                @for ($s = 0; $s < 5; $s++)
                                    <i class="fas fa-star text-sm"></i>
                                @endfor
                                <span class="text-xs text-gray-500 ml-2">(120)</span>
                            </div>
                            {{--  Add to Cart Button --}}
                            <button 
                                class="addToCartBtn mt-3 bg-[#5b2c2c] text-white px-4 py-1 rounded hover:bg-[#4a2424] text-sm flex items-center justify-center mx-auto"
                                data-id="{{ $i }}">
                                <i class="fas fa-cart-plus mr-2"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{--  NEW ARRIVALS SECTION (Dynamic Products from Sellers) --}}
        <section class="mt-16 mb-12">
            <h2 class="text-2xl font-bold text-[#5b2c2c] mb-6 text-left">New Arrivals</h2>

            @if ($products->isEmpty())
                <p class="text-center text-gray-500">No new products available.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-x-3 gap-y-6 w-full">
                    @foreach ($products as $product)
                        <div class="bg-white shadow-md rounded-lg overflow-hidden transform transition hover:-translate-y-1 hover:shadow-lg w-full">
                            <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/300x200?text=No+Image' }}"
                                 alt="{{ $product->name }}" class="w-full h-56 md:h-60 object-cover">
                            <div class="p-4 text-center">
                                <h3 class="font-semibold text-gray-800 text-base md:text-lg">{{ $product->name }}</h3>
                                <p class="text-[#5b2c2c] font-bold mt-1 text-base">Rs {{ number_format($product->price, 2) }}</p>
                                <div class="flex justify-center items-center text-yellow-500 mt-1">
                                    @for ($s = 0; $s < 5; $s++)
                                        <i class="fas fa-star text-sm"></i>
                                    @endfor
                                    <span class="text-xs text-gray-500 ml-2">(98)</span>
                                </div>
                                {{--  AJAX Add to Cart Button --}}
                                <button 
                                    class="addToCartBtn mt-3 bg-[#5b2c2c] text-white px-4 py-1 rounded hover:bg-[#4a2424] text-sm flex items-center justify-center mx-auto"
                                    data-id="{{ $product->id }}">
                                    <i class="fas fa-cart-plus mr-2"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- POPULAR CATEGORIES --}}
        <section class="mt-16 mb-12">
            <h2 class="text-2xl font-bold text-[#5b2c2c] mb-8 text-left">Popular Categories</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-4">
                <div class="flex items-center justify-between bg-green-50 rounded-2xl p-6 shadow-sm hover:shadow-lg transition">
                    <div>
                        <p class="text-green-700 font-medium">Ceylon’s Pride!</p>
                        <h3 class="text-2xl font-extrabold text-gray-800">Tea</h3>
                    </div>
                    <img src="https://via.placeholder.com/100x100?text=Tea" alt="Tea" class="w-24 h-24 object-contain">
                </div>

                <div class="flex items-center justify-between bg-orange-50 rounded-2xl p-6 shadow-sm hover:shadow-lg transition">
                    <div>
                        <p class="text-orange-600 font-medium">Taste of Tradition!</p>
                        <h3 class="text-2xl font-extrabold text-gray-800">Spices</h3>
                    </div>
                    <img src="https://via.placeholder.com/100x100?text=Spices" alt="Spices" class="w-24 h-24 object-contain">
                </div>

                <div class="flex items-center justify-between bg-yellow-50 rounded-2xl p-6 shadow-sm hover:shadow-lg transition">
                    <div>
                        <p class="text-yellow-600 font-medium">Crafted with Heritage!</p>
                        <h3 class="text-2xl font-extrabold text-gray-800">Handicrafts</h3>
                    </div>
                    <img src="https://via.placeholder.com/100x100?text=Handicraft" alt="Handicrafts" class="w-24 h-24 object-contain">
                </div>
            </div>
        </section>

        {{-- ADVERTISEMENT BANNER --}}
        <section class="mt-14 mb-14 relative overflow-hidden">
            <div class="relative bg-[url('https://via.placeholder.com/1200x400?text=TradLanka+Deals')] bg-cover bg-center h-64 md:h-[420px] flex items-center justify-center">
                <div class="absolute inset-0 bg-[#5b2c2c]/50"></div>
                <div class="relative text-center">
                    <h2 class="text-white text-2xl md:text-3xl font-extrabold mb-3">Special Festive Offers!</h2>
                    <a href="#" class="inline-block bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-6 py-2 rounded shadow">View</a>
                </div>
            </div>
        </section>

        {{-- RECOMMENDED FOR YOU --}}
        <section class="mt-10 mb-16">
            <h2 class="text-2xl font-bold text-[#5b2c2c] mb-6 text-left">Recommended for You</h2>

            <div class="overflow-hidden relative">
                <div id="recommendSlider" class="flex space-x-6 animate-scroll will-change-transform">
                    @foreach (range(1, 10) as $i)
                        <div class="min-w-[220px] bg-white rounded-lg shadow-md overflow-hidden transform transition hover:-translate-y-1 hover:shadow-lg">
                            <img src="https://via.placeholder.com/300x200?text=AI+Rec+{{ $i }}" alt="Recommended Product {{ $i }}" class="w-full h-36 object-cover">
                            <div class="p-4 text-center">
                                <h3 class="font-semibold text-gray-800 text-sm">Recommended Product {{ $i }}</h3>
                                <p class="text-[#5b2c2c] font-bold mt-1">Rs {{ 10 + $i }}.00</p>
                                {{--  AJAX Add to Cart Button --}}
                                <button 
                                    class="addToCartBtn mt-2 bg-[#5b2c2c] text-white px-3 py-1 rounded text-xs hover:bg-[#4a2424]"
                                    data-id="{{ $i }}">
                                    <i class="fas fa-cart-plus mr-1"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <style>
                @keyframes scroll {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(-50%); }
                }
                .animate-scroll {
                    display: flex;
                    animation: scroll 30s linear infinite;
                }
            </style>
        </section>

    </div>
</main>

{{-- Floating Action Buttons (Chatbot + WhatsApp) --}}
<div class="fixed bottom-6 right-6 flex flex-col items-center space-y-3 z-50">
    {{-- Chatbot --}}
    <a href="#" id="chatbotBtn" class="bg-[#5b2c2c] text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center hover:scale-110 transition-transform duration-300">
        <i class="fas fa-robot text-xl"></i>
    </a>

    {{-- WhatsApp --}}
    <a href="https://wa.me/94771234567" target="_blank" class="bg-[#25D366] text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center hover:scale-110 transition-transform duration-300">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>
</div>

{{--  Add to Cart AJAX Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.addToCartBtn');
    const toast = document.getElementById('cartToast');
    const toastMessage = document.getElementById('toastMessage');

    buttons.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;

            fetch("{{ route('cart.add') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(res => res.json())
            .then(data => {
                toastMessage.textContent = data.message;
                toast.classList.remove('hidden');
                setTimeout(() => toast.classList.add('hidden'), 2500);
            })
            .catch(() => {
                toastMessage.textContent = "Something went wrong!";
                toast.classList.remove('hidden');
                setTimeout(() => toast.classList.add('hidden'), 2500);
            });
        });
    });
});
</script>

@endsection
