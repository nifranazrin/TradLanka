@extends('layouts.frontend')

@section('content')
{{-- Include Header --}}
@include('frontend.partials.header')

<div class="bg-[#f9f6f3] min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Page Title --}}
        <div class="mb-8 border-b border-gray-200 pb-4">
            <h1 class="text-3xl font-bold text-[#5b2c2c]">
                Search Results for <span class="text-[#d97706] italic">"{{ $query }}"</span>
            </h1>
            <p class="text-gray-500 mt-1">
                Found {{ $products->total() }} result(s)
            </p>
        </div>

        {{-- Logic: Check if products exist --}}
        @if($products->count() > 0)
            
            {{-- Product Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition duration-300 overflow-hidden group border border-gray-100">
                        
                        {{-- Product Image --}}
                        <div class="h-60 overflow-hidden relative bg-gray-100">
                            <a href="{{ route('product.show', $product->slug) }}">
                                {{-- Uses your Model Accessor 'image_url' --}}
                                <img src="{{ $product->image_url ?? 'https://via.placeholder.com/300?text=No+Image' }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            </a>
                            
                            {{-- Optional Badge (Low Stock Logic) --}}
                            @if($product->stock < 5 && $product->stock > 0)
                                <span class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded">
                                    Low Stock
                                </span>
                            @endif
                        </div>
                        
                        {{-- Product Details --}}
                        <div class="p-4">
                            {{-- Category Name (Small) --}}
                            <p class="text-xs text-gray-500 mb-1">
                                {{ $product->category->name ?? 'Product' }}
                            </p>

                            {{-- Name --}}
                            <h3 class="text-lg font-bold text-gray-800 mb-2 truncate" title="{{ $product->name }}">
                                <a href="{{ route('product.show', $product->slug) }}" class="hover:text-[#5b2c2c] transition">
                                    {{ $product->name }}
                                </a>
                            </h3>

                            {{-- Price --}}
                            <div class="flex items-center justify-between mt-3">
                                <span class="text-xl font-bold text-[#e95b2c]">
                                    Rs {{ number_format($product->price, 2) }}
                                </span>
                            </div>

                            {{-- View Button --}}
                            <a href="{{ route('product.show', $product->slug) }}" 
                               class="mt-4 block w-full py-2 bg-[#5b2c2c] text-white text-center rounded-lg hover:bg-[#462020] transition shadow-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination Links --}}
            <div class="mt-10">
                {{-- Ensure pagination links maintain the search query when clicking Page 2 --}}
                {{ $products->appends(['query' => $query])->links() }}
            </div>

        @else
            {{-- Empty State (No Results) --}}
            <div class="flex flex-col items-center justify-center py-20 bg-white rounded-xl shadow-sm border border-gray-100 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-400">
                    <i class="fas fa-search text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-700 mb-2">No products found</h2>
                <p class="text-gray-500 max-w-md mx-auto mb-6">
                    We couldn't find any products matching "{{ $query }}". Try checking for typos or using different keywords.
                </p>
                <a href="{{ route('home') }}" 
                   class="px-8 py-3 bg-[#5b2c2c] text-white rounded-lg hover:bg-[#462020] transition shadow">
                    Back to Home
                </a>
            </div>
        @endif

    </div>
</div>
@endsection