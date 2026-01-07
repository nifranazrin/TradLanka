@extends('layouts.frontend')

@section('content')
<div class="py-10 min-h-screen"
     style="background: url('{{ asset('storage/images/background.jpg') }}') no-repeat center center fixed; background-size: cover;">
     
    <div class="max-w-7xl mx-auto px-4">

        {{-- Main Title --}}
        <h1 class="text-4xl font-extrabold text-[#5b2c2c] mb-8 drop-shadow-sm">
            Manage My Account
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

            {{-- ================= LEFT SIDEBAR ================= --}}
            <div class="lg:col-span-1">
                @include('user.profile.sidebar') {{-- This pulls in your new sidebar file --}}
            </div>

            {{-- ================= RIGHT CONTENT ================= --}}
            <section class="md:col-span-3 space-y-6">

                {{-- ================= PROFILE CARDS ================= --}}
                <div id="profile" class="grid grid-cols-1 md:grid-cols-3 gap-6 scroll-mt-28">

                    {{-- Personal Profile --}}
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg shadow-md p-6 hover:shadow-lg transition">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-lg text-[#5b2c2c]">Personal Profile</h3>
                            <a href="{{ route('user.profile.edit') }}"
                               class="text-xs font-bold text-white bg-[#5b2c2c] px-3 py-1 rounded hover:bg-[#4a2424] transition">
                                EDIT
                            </a>
                        </div>
                        <p class="font-bold text-gray-800 text-lg">{{ $user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>

                    {{-- Address Book --}}
                    <div id="address" class="bg-white/95 backdrop-blur-sm rounded-lg shadow-md p-6 hover:shadow-lg transition">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-lg text-[#5b2c2c]">Address Book</h3>
                            <a href="{{ route('user.profile.address') }}"
                               class="text-xs font-bold text-white bg-[#5b2c2c] px-3 py-1 rounded hover:bg-[#4a2424] transition">
                                EDIT
                            </a>
                        </div>
                        @if($user->address1)
                            <p class="font-bold text-gray-800">{{ $user->name }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $user->address1 }}<br>{{ $user->city }}</p>
                        @else
                            <p class="text-sm text-gray-400 italic">No address added yet.</p>
                        @endif
                    </div>

                    {{-- Review Summary Card --}}
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg shadow-md p-6 hover:shadow-lg transition">
                        <h3 class="font-bold text-lg text-[#5b2c2c] mb-4">Reviews</h3>
                        <div class="flex flex-col items-center justify-center h-20 border-2 border-dashed border-gray-100 rounded-lg">
                            <a href="{{ route('user.reviews.index') }}" class="text-center group">
                                <p class="text-2xl font-black text-[#5b2c2c] group-hover:scale-110 transition-transform">
                                    {{ \App\Models\Review::where('user_id', auth()->id())->count() }}
                                </p>
                                <p class="text-[10px] uppercase text-gray-400 font-bold">Total Reviews</p>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ================= RECENT ORDERS TABLE ================= --}}
                             
<div id="orders" class="bg-white/95 backdrop-blur-sm rounded-lg shadow-md p-6 scroll-mt-28">
    <h3 class="font-bold text-xl text-[#5b2c2c] mb-5 border-b pb-2">Recent Orders</h3>
    @if($orders->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="text-left px-4 py-3 font-bold">Product</th>
                        <th class="text-left px-4 py-3 font-bold">Order #</th>
                        <th class="text-left px-4 py-3 font-bold">Date</th>
                        <th class="text-right px-4 py-3 font-bold">Total</th>
                        <th class="text-right px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($orders as $order)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-4">
                                 @php
                            // Find the first item that actually has an associated product
                            $itemWithProduct = $order->orderItems->first(function($item) {
                                return $item->product !== null;
                            });

                            // Determine image path: Valid Product > Default Placeholder
                            $imagePath = ($itemWithProduct && $itemWithProduct->product->image) 
                                ? asset('storage/' . $itemWithProduct->product->image) 
                                : asset('images/default-placeholder.png'); 
                        @endphp
                                                    
                                <img src="{{ $imagePath }}" 
                                     alt="Order Product Image"
                                     class="w-12 h-12 object-cover rounded border border-gray-200">
                            </td>
                            <td class="px-4 py-4 font-medium text-gray-800">{{ $order->tracking_no }}</td>
                            <td class="px-4 py-4 text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                            
                            <td class="px-4 py-4 text-right font-bold text-[#5b2c2c]">
                                @if($order->currency === 'USD')
                                    ${{ number_format($order->total_price, 2) }}
                                @else
                                    Rs. {{ number_format($order->total_price, 2) }}
                                @endif
                            </td>

                            <td class="px-4 py-4 text-right">
                                <a href="{{ route('user.orders.show', $order->id) }}" class="text-blue-600 font-bold hover:text-blue-800 text-xs uppercase tracking-wider">Manage</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-8">
            <i class="fas fa-shopping-bag text-gray-300 text-4xl mb-3"></i>
            <p class="text-gray-500">No orders placed yet.</p>
            <a href="{{ route('home') }}" class="text-[#5b2c2c] font-bold hover:underline mt-2 inline-block">Start Shopping</a>
        </div>
    @endif
</div>
            </section>
        </div>
    </div>
</div>
@endsection