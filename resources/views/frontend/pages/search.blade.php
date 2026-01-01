@extends('layouts.frontend')

@section('content')
{{-- Include Header --}}
@include('frontend.partials.header')

<div class="bg-[#f9f6f3] min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ✅ BREADCRUMB SECTION --}}
        <nav aria-label="breadcrumb" class="mb-6 text-sm text-gray-600 w-full">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ url('/') }}" class="hover:text-[#5b2c2c] transition flex items-center gap-1">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li><span class="text-gray-400">/</span></li>
                <li class="font-bold text-[#5b2c2c] capitalize">
                    {{ str_replace('+', ' ', $query) }}
                </li>
            </ol>
        </nav>
        
        {{-- Page Title --}}
        <div class="mb-8 border-b border-gray-200 pb-4">
            <h1 class="text-3xl font-bold text-[#5b2c2c]">
                @if($query == 'best sellers' || $query == 'new arrivals')
                    <span class="capitalize">{{ str_replace('+', ' ', $query) }}</span>
                @else
                    Search Results for <span class="text-[#d97706] italic">"{{ $query }}"</span>
                @endif
            </h1>
        </div>

        @if($products->count() > 0)
        
            {{-- ✅ Product Grid --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    @foreach($products as $product)
        <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition duration-300 overflow-hidden group border border-gray-100 flex flex-col h-full">

            {{-- Product Image Wrapper --}}
            <div class="h-60 overflow-hidden relative bg-gray-100">
                
                            {{-- ✅ 1. BEST SELLER BADGE (GOLD) --}}
                @if(request('query') == 'best sellers')
                    <div class="absolute top-2 left-2 z-10 pointer-events-none">
                        <span class="bg-[#d97706] text-white text-[9px] font-bold px-2 py-1 rounded shadow-sm flex items-center gap-1">
                            <i class="fas fa-crown"></i> BEST SELLER
                        </span>
                    </div>
                @endif

                 {{-- ✅ 2. NEW ARRIVAL BADGE (GREEN) --}}
                @if(request('query') == 'new arrivals' || $product->created_at->diffInDays(now()) < 14)
                    <div class="absolute top-2 {{ request('query') == 'best sellers' ? 'right-2' : 'left-2' }} z-10 pointer-events-none">
                        <span class="bg-[#198754] text-white text-[9px] font-bold px-2 py-1 rounded shadow-sm uppercase">
                            New Arrival
                        </span>
                    </div>
                @endif
                                        
                <a href="{{ route('product.show', $product->slug) }}" class="block h-full w-full">
                    <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/placeholder.jpg') }}" 
                         alt="{{ $product->name }}" 
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                </a>
                
                {{-- Low Stock Badge --}}
                @if($product->stock < 5 && $product->stock > 0)
                    <span class="absolute bottom-2 right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded">
                        Low Stock
                    </span>
                @endif
            </div>
            
            {{-- Product Details --}}
            <div class="p-4 flex flex-col flex-grow">
                <p class="text-xs text-gray-500 mb-1">{{ $product->category->name ?? 'Product' }}</p>

                <h3 class="text-base font-bold text-gray-800 mb-2 line-clamp-2" title="{{ $product->name }}">
                    <a href="{{ route('product.show', $product->slug) }}" class="hover:text-[#5b2c2c] transition">
                        {{ $product->name }}
                    </a>
                </h3>

                <div class="mt-auto">
                    {{-- ✅ FIXED: Use the Accessor for consistent currency (Prevents $ vs Rs mistake) --}}
                    <div class="flex items-center justify-between mt-3 mb-4">
                        <span class="text-xl font-bold text-[#e95b2c]">
                            {{ $product->display_price }}
                        </span>
                    </div>

                    <a href="{{ route('product.show', $product->slug) }}" 
                       class="block w-full py-2 bg-[#5b2c2c] text-white text-center rounded-lg hover:bg-[#462020] transition shadow-sm font-semibold">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</div>
            {{-- ✅ Pagination Logic with Safety Check --}}
<div class="mt-10 flex justify-center">
    @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator)
        {{ $products->appends(['query' => $query])->links() }}
    @else
        <p class="text-gray-400 text-sm">Viewing all available New Arrivals</p>
    @endif
</div>
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-20 bg-white rounded-xl shadow-sm border border-gray-100 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-400">
                    <i class="fas fa-search text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-700 mb-2">No products found</h2>
                <p class="text-gray-500 max-w-md mx-auto mb-6">
                    We couldn't find any products matching "{{ $query }}".
                </p>
                <a href="{{ route('home') }}" class="px-8 py-3 bg-[#5b2c2c] text-white rounded-lg hover:bg-[#462020] transition shadow">
                    Back to Home
                </a>
            </div>
        @endif
    </div>
</div>
@endsection