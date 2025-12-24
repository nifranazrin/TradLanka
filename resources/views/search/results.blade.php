@extends('layouts.frontend')
@section('content')
    @include('frontend.partials.header')
    <div class="container mx-auto px-6 py-10 mt-20">
        <h2 class="text-3xl font-bold text-[#5b2c2c] mb-6">Visual Search Results</h2>
        @if($products->isEmpty())
            <div class="text-center py-10"><p class="text-gray-500">No matching products found.</p></div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition">
                        <a href="{{ route('product.show', $product->slug) }}">
                            <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-48 object-cover">
                            <div class="p-4 text-center">
                                <h3 class="font-bold text-gray-800 truncate">{{ $product->name }}</h3>
                                <p class="text-[#e95b2c] font-bold">Rs {{ number_format($product->price, 2) }}</p>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection