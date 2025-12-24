@extends('layouts.frontend')

@section('content')

{{-- ✅ MAIN CONTAINER WITH BACKGROUND IMAGE --}}
<div class="py-10 min-h-screen"
     style="background: url('{{ asset('storage/images/background.jpg') }}') no-repeat center center fixed; background-size: cover;">
     
    <div class="max-w-6xl mx-auto px-4">

        {{-- ✅ BREADCRUMB NAVIGATION --}}
        <nav class="flex text-sm font-bold text-[#5b2c2c] mb-6 bg-white/80 backdrop-blur-sm p-3 rounded-lg w-fit shadow-sm">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="{{ route('user.profile.index') }}" class="hover:underline hover:text-[#8a4a4a]">My Profile</a>
                    <span class="mx-2 text-gray-500">/</span>
                </li>
                <li class="flex items-center text-gray-600">
                    My Orders
                </li>
            </ol>
        </nav>

        {{-- HEADER SECTION --}}
        <div class="mb-8">
            <h1 class="text-4xl font-extrabold text-[#5b2c2c] drop-shadow-sm">
                My Orders
            </h1>
        </div>

        @if($orders->count())

            <div class="space-y-4">
                @foreach($orders as $order)
                    {{-- Order Card with Backdrop Blur --}}
                    <div class="bg-white/95 backdrop-blur-sm p-6 rounded-lg shadow-md hover:shadow-lg transition flex flex-col md:flex-row md:items-center md:justify-between border-l-4 border-[#5b2c2c]">

                        {{-- LEFT SIDE: Order Info --}}
                        <div class="mb-4 md:mb-0">
                            <p class="font-extrabold text-xl text-gray-800">
                                Order #{{ $order->tracking_no }}
                            </p>
                            <p class="text-sm text-gray-500 font-medium mt-1">
                                <i class="far fa-calendar-alt mr-1"></i> Placed on {{ $order->created_at->format('d M Y') }}
                            </p>
                            {{-- Optional: Show Status Badge --}}
                            <div class="mt-2">
                                <span class="px-3 py-1 rounded-full text-xs font-bold border
                                    {{ $order->status == '0' ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : 'bg-green-100 text-green-800 border-green-200' }}">
                                    {{ $order->status == '0' ? 'Pending' : 'Completed' }}
                                </span>
                            </div>
                        </div>

                        {{-- RIGHT SIDE: Price & Link --}}
                        <div class="text-left md:text-right">
                            <p class="font-extrabold text-2xl text-[#5b2c2c] mb-2">
                                Rs. {{ number_format($order->total_price, 2) }}
                            </p>

                            {{-- "View Details" Link --}}
                            <a href="{{ route('user.orders.show', $order->id) }}" 
                               class="inline-block bg-[#5b2c2c] text-white text-sm font-bold px-5 py-2 rounded hover:bg-[#4a2424] transition shadow-sm">
                                View Details
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>

        @else
            {{-- Empty State --}}
            <div class="bg-white/95 backdrop-blur-sm p-10 rounded-lg shadow-lg text-center">
                <i class="fas fa-box-open text-gray-300 text-6xl mb-4"></i>
                <p class="text-xl font-bold text-gray-600">
                    You haven’t placed any orders yet.
                </p>
                <a href="{{ route('home') }}" class="text-[#5b2c2c] font-bold hover:underline mt-4 inline-block">
                    Start Shopping
                </a>
            </div>
        @endif

    </div>
</div>
@endsection