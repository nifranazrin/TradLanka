@extends('layouts.frontend')

@section('content')

<div class="bg-gray-100 py-2 border-b border-gray-200">
    <div class="container mx-auto px-4">
        <nav class="text-sm text-gray-600">
            <a href="{{ url('/') }}" class="hover:text-[#8a4b2b]">Home</a> 
            <span class="mx-2">/</span>
            
            {{-- Show Parent Category if it exists --}}
            @if($category->parent)
                <a href="{{ url('category/'.$category->parent->slug) }}" class="hover:text-[#8a4b2b]">{{ $category->parent->name }}</a>
                <span class="mx-2">/</span>
            @endif
            
            <span class="font-semibold text-gray-800">{{ $category->name }}</span>
        </nav>
    </div>
</div>

@php
    // Logic: Use banner_image if available, otherwise fall back to main image, or a default
    $bannerUrl = $category->banner_image 
                 ? asset('storage/' . $category->banner_image) 
                 : ($category->image ? asset('storage/' . $category->image) : asset('images/default-banner.jpg')); 
@endphp

<div class="relative py-24 mb-8 border-b border-[#ead6d6] bg-cover bg-center"
     style="background-image: url('{{ $bannerUrl }}');">

    <div class="absolute inset-0 bg-black bg-opacity-50"></div>

    <div class="container mx-auto px-4 relative z-10 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4 drop-shadow-lg">
            {{ $category->name }}
        </h1>
        <p class="text-gray-100 text-lg max-w-3xl mx-auto drop-shadow-md leading-relaxed">
            {{ $category->description ?? 'Explore our exclusive collection of ' . $category->name }}
        </p>
    </div>
</div>

<div class="container mx-auto px-4 pb-12">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <aside class="w-full lg:w-1/4 flex-shrink-0">
            
            <div class="mb-8 pl-1">
                
                {{-- Sidebar Title (Maroon) --}}
                <h2 class="text-[#5b2c2c] font-bold text-lg mb-1">
                    {{ $sidebarTitle ?? 'Items' }}
                </h2>

                {{-- Mini Breadcrumb --}}
                <div class="text-xs text-gray-500 mb-4">
                    Home / {{ $sidebarTitle ?? 'Items' }}
                </div>

                {{-- "All Items" Bar (Maroon Background) --}}
                <div class="bg-[#5b2c2c] text-white px-4 py-2 rounded-md flex justify-between items-center font-medium shadow-sm mb-4">
                    <span>All Items</span>
                    <i class="fas fa-angle-double-right text-xs"></i>
                </div>

                {{-- Sidebar List --}}
                @if(isset($sidebarItems) && $sidebarItems->count() > 0)
                    <ul class="space-y-2">
                        @foreach($sidebarItems as $item)
                            <li>
                                {{-- LOGIC: Check if we are listing Categories or Products --}}
                                @if($sidebarType == 'category')
                                    <a href="{{ url('category/'.$item->slug) }}" 
                                       class="block text-sm text-gray-700 hover:text-[#5b2c2c] hover:font-bold transition pl-2
                                       {{ $category->id == $item->id ? 'font-bold text-[#5b2c2c]' : '' }}">
                                       {{ $item->name }}
                                    </a>
                                @else
                                    <a href="{{ route('product.show', $item->slug) }}" 
                                       class="block text-sm text-gray-700 hover:text-[#8a4b2b] hover:font-bold transition pl-2">
                                       {{ $item->name }}
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-400 pl-2">No items found.</p>
                @endif
            </div>

            <div class="bg-gray-50 rounded-lg shadow-sm border border-gray-200 p-5">
                <h3 class="text-[#5b2c2c] font-bold uppercase text-sm mb-4 border-b border-gray-200 pb-2">
                    Price Filter
                </h3>
                
                <form action="{{ url()->current() }}" method="GET">
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="sort" value="newest" onchange="this.form.submit()"
                                   class="w-4 h-4 text-[#5b2c2c] focus:ring-[#5b2c2c] accent-[#5b2c2c]"
                                   {{ request('sort') == 'newest' || !request('sort') ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700 group-hover:text-[#5b2c2c]">Best Sellers / Newest</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="sort" value="price_asc" onchange="this.form.submit()"
                                   class="w-4 h-4 text-[#5b2c2c] focus:ring-[#5b2c2c] accent-[#5b2c2c]"
                                   {{ request('sort') == 'price_asc' ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700 group-hover:text-[#5b2c2c]">Low to High</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="sort" value="price_desc" onchange="this.form.submit()"
                                   class="w-4 h-4 text-[#5b2c2c] focus:ring-[#5b2c2c] accent-[#5b2c2c]"
                                   {{ request('sort') == 'price_desc' ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700 group-hover:text-[#5b2c2c]">High to Low</span>
                        </label>
                    </div>
                </form>
            </div>

        </aside>

        <main class="w-full lg:w-3/4">
            @forelse($products as $product)
                @if ($loop->first) <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6"> @endif

                <div class="bg-white rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition group overflow-hidden h-full flex flex-col">
                    
                    <div class="relative h-48 overflow-hidden bg-gray-100">
                        <a href="{{ route('product.show', $product->slug) }}">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            @else
                                <img src="https://via.placeholder.com/300?text=No+Image" 
                                     class="w-full h-full object-cover">
                            @endif
                        </a>
                        @if(isset($product->sale_price) && $product->sale_price < $product->price)
                            <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">SALE</span>
                        @endif
                    </div>

                    <div class="p-4 flex flex-col flex-grow">
                        <h3 class="text-sm font-semibold text-gray-800 mb-1 group-hover:text-[#8a4b2b] truncate">
                            <a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a>
                        </h3>
                        
                        <div class="mt-auto flex items-center justify-between pt-3">
                            <div class="flex flex-col">
                                @if(isset($product->sale_price) && $product->sale_price)
                                    <span class="text-xs text-gray-400 line-through">Rs {{ number_format($product->price, 2) }}</span>
                                    <span class="text-md font-bold text-red-600">Rs {{ number_format($product->sale_price, 2) }}</span>
                                @else
                                    <span class="text-md font-bold text-gray-900">Rs {{ number_format($product->price, 2) }}</span>
                                @endif
                            </div>
                            <a href="{{ route('product.show', $product->slug) }}" class="text-[#8a4b2b] border border-[#8a4b2b] hover:bg-[#8a4b2b] hover:text-white px-3 py-1 rounded text-xs transition">
                                View
                            </a>
                        </div>
                    </div>
                </div>

                @if ($loop->last) </div> @endif

            @empty
                <div class="text-center py-12 bg-white rounded-lg border border-gray-200 w-full">
                    <div class="flex justify-center mb-3">
                        <i class="fas fa-box-open text-4xl text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No products found</h3>
                    <p class="text-gray-500">Try checking back later.</p>
                </div>
            @endforelse

            <div class="mt-8">
                {{ $products->appends(request()->query())->links() }}
            </div>

        </main>
    </div>
</div>

@endsection