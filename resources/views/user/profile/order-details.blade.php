@extends('layouts.frontend')

@section('content')

{{-- ✅ CLEAN CSS (Gold Background + Maroon Button + No Icon Glitches) --}}
<style>
    .swal-modal { background-color: #33100e !important; border: 3px solid #dfd04c; }
    .swal-title { color: #d1ab5a !important; font-size: 24px; margin-bottom: 10px; }
    .swal-text { color: #f1de89 !important; font-weight: 600; font-size: 16px; text-align: center; }
    .swal-footer { text-align: center !important; margin-top: 20px !important; }
    .swal-button { background-color: #eef088 !important; color: rgb(78, 27, 27) !important; border: none; padding: 10px 35px; font-weight: bold; border-radius: 5px; }
    .swal-button:hover { background-color: #e9b669 !important; }

    /* Tooltip for disabled button */
    .btn-disabled { cursor: not-allowed; opacity: 0.6; position: relative; }
</style>

<div class="py-10 min-h-screen" style="background: url('{{ asset('storage/images/background.jpg') }}') no-repeat center center fixed; background-size: cover;">
    <div class="max-w-6xl mx-auto px-4">

        {{-- ✅ BREADCRUMB --}}
        <nav class="flex text-sm font-bold text-[#5b2c2c] mb-6 bg-white/80 p-3 rounded-lg w-fit shadow-sm">
            <ol class="inline-flex">
                <li><a href="{{ route('user.profile.index') }}" class="hover:underline">My Profile</a><span class="mx-2">/</span></li>
                <li><a href="{{ route('user.orders.index') }}" class="hover:underline">My Orders</a><span class="mx-2">/</span></li>
                <li class="text-gray-600">Order #{{ $order->tracking_no }}</li>
            </ol>
        </nav>

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-4xl font-extrabold text-[#5b2c2c] drop-shadow-sm">Order Details</h1>
            <a href="{{ route('user.orders.index') }}" class="text-sm font-bold bg-white text-[#5b2c2c] px-4 py-2 rounded shadow hover:bg-gray-100 transition">
                &larr; Back to List
            </a>
        </div>

        <div class="bg-white/95 backdrop-blur-sm rounded-lg shadow-lg overflow-hidden border-t-4 border-[#5b2c2c]">
            
            {{-- ORDER HEADER --}}
            <div class="bg-gray-50 px-8 py-5 border-b flex justify-between items-center">
                <div>
                    <p class="font-extrabold text-xl text-gray-800">
                        <a href="{{ route('track.order', ['tracking_no' => $order->tracking_no]) }}" class="hover:underline text-[#5b2c2c]">
                            Order #{{ $order->tracking_no }}
                        </a>
                    </p>
                    <p class="text-sm text-gray-500 mt-1">Placed on {{ $order->created_at->format('d M Y h:i A') }}</p>
                </div>

                  {{-- ✅ DYNAMIC TRACKING STATUSES --}}
                            @php
                                $statusMap = [
                                    '0' => ['Order Placed', 'bg-blue-100 text-blue-800'],
                                    '1' => ['Order Received', 'bg-orange-100 text-orange-800'],
                                    '2' => ['Packed', 'bg-purple-100 text-purple-800'],
                                    '4' => ['At Head Office', 'bg-indigo-100 text-indigo-800'],
                                    '5' => ['Delivered', 'bg-green-100 text-green-800'],
                                    '6' => ['Cancelled', 'bg-red-100 text-red-800'],
                                    '7' => ['Cancellation Requested', 'bg-yellow-100 text-yellow-800'],
                                    '8' => ['Seller Approved', 'bg-green-100 text-green-800']
                                ];

                                $currentStatus = $statusMap[$order->status] ?? ['Processing', 'bg-gray-100 text-gray-800'];
                            @endphp
                <span class="px-4 py-2 rounded-full text-sm font-bold shadow-sm {{ $currentStatus[1] }}">
                    {{ $currentStatus[0] }}
                </span>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                    {{-- Delivery Address --}}
                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-100">
                        <h4 class="font-bold text-lg text-[#5b2c2c] mb-3 border-b pb-2">Delivery Address</h4>
                        <div class="text-sm text-gray-700 space-y-1">
                            <p class="font-bold text-base">{{ $order->fullname }}</p>
                            <p>{{ $order->address }}</p>
                            <p>{{ $order->city }}, {{ $order->pincode }}</p>
                            <p class="mt-2 font-medium flex items-center gap-2">
                                <i class="fas fa-phone-alt text-gray-400"></i> {{ $order->phone }}
                            </p>
                        </div>
                    </div>

                    {{-- Order Summary --}}
                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-100">
                        <h4 class="font-bold text-lg text-[#5b2c2c] mb-3 border-b pb-2">Order Summary</h4>
                        <div class="text-sm text-gray-700 space-y-2">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-500">Payment Mode:</span>
                                <span class="font-bold">{{ $order->payment_mode }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-500">Payment ID:</span>
                                <span class="font-mono bg-white px-2 rounded border">{{ $order->payment_id ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between text-xl font-extrabold text-[#5b2c2c] mt-4 border-t border-gray-300 pt-3">
                                <span>Grand Total:</span>
                                <span>{{ $order->currency ?? 'Rs.' }} {{ number_format($order->total_price, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

              {{-- Items Table --}}
<h4 class="font-bold text-xl text-gray-800 mb-4">Items Ordered</h4>
<div class="border rounded-lg overflow-hidden shadow-sm">
    <table class="w-full text-sm text-left">
        <thead class="bg-[#5b2c2c] text-white uppercase text-xs tracking-wider">
            <tr>
                <th class="px-6 py-4 font-bold">Product</th>
                <th class="px-6 py-4 text-center font-bold">Price</th>
                <th class="px-6 py-4 text-center font-bold">Qty</th>
                <th class="px-6 py-4 text-right font-bold">Total</th>
                {{-- Fixed width for the header to match buttons --}}
                <th class="px-6 py-4 text-right font-bold w-44">Action</th> 
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
            @foreach($order->orderItems as $item)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        @if($item->product)
                            <a href="{{ route('product.show', $item->product->slug) }}" class="flex items-center p-1 rounded transition group">
                                <div class="w-16 h-16 flex-shrink-0 mr-4 border rounded overflow-hidden shadow-sm bg-white">
                                    @if($item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                    @else
                                        <div class="w-full h-full bg-gray-100 flex items-center justify-center text-xs text-gray-400">No Img</div>
                                    @endif
                                </div>
                                <span class="font-bold text-gray-800 group-hover:text-[#5b2c2c] group-hover:underline text-base">{{ $item->product->name }}</span>
                            </a>
                        @else
                            <span class="text-red-500 italic font-medium">Product No Longer Available</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center text-gray-600 font-medium">
                        {{ $order->currency ?? 'Rs.' }} {{ number_format($item->price, 2) }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-gray-100 text-gray-800 py-1 px-3 rounded font-bold text-xs">x{{ $item->qty }}</span>
                    </td>
                    <td class="px-6 py-4 text-right font-bold text-[#5b2c2c]">
                        {{ $order->currency ?? 'Rs.' }} {{ number_format($item->price * $item->qty, 2) }}
                    </td>
                    
                    <td class="px-6 py-4 text-right">
                        {{-- Single clean flex container for buttons --}}
                        <div class="flex flex-col gap-2 items-end">
                            
                            {{-- 1. CANCEL BUTTON LOGIC --}}
                            @if($order->status == '0' || $order->status == '1' || $order->status == '2')
                                {{-- Stage where Customer can request cancel --}}
                                <form id="cancel-form-{{ $order->id }}" action="{{ route('user.orders.cancel', $order->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="button" onclick="confirmCancellation({{ $order->id }})" 
                                            class="w-32 bg-red-600 text-white px-4 py-2 rounded text-[10px] font-bold hover:bg-red-700 transition shadow">
                                        REQUEST CANCEL
                                    </button>
                                </form>
                            @elseif($order->status == '7')
                                {{-- Status 7: Waiting for Seller Approval --}}
                                <button disabled title="Waiting for Seller to approve cancellation" 
                                        class="w-32 bg-yellow-500 text-white px-4 py-2 rounded text-[10px] font-bold shadow cursor-not-allowed opacity-75">
                                    WAITING SELLER
                                </button>
                            @elseif($order->status == '4')
                                {{-- Status 4: Locked at Head Office --}}
                                <button disabled title="Cannot cancel: Order is already at Head Office" 
                                        class="w-32 bg-gray-400 text-white px-4 py-2 rounded text-[10px] font-bold shadow cursor-not-allowed opacity-75">
                                    CANCEL (LOCKED)
                                </button>
                            @endif

                            {{-- 2. BUY AGAIN BUTTON --}}
                            @if($item->product)
                                <form action="{{ route('cart.add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                    <input type="hidden" name="product_qty" value="1"> 
                                    <button type="submit" class="w-32 bg-[#5b2c2c] text-white px-4 py-2 rounded text-[10px] font-bold hover:bg-[#4a2424] transition shadow">
                                        BUY AGAIN
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if (session('status'))
            swal({ title: "Success!", text: "{{ session('status') }}", button: "OK" });
        @endif
        @if (session('error'))
            swal({ title: "Notice", text: "{{ session('error') }}", button: "OK" });
        @endif
    });

    function confirmCancellation(orderId) {
        swal({
            title: "Are you sure?",
            text: "Do you want to request a cancellation from the seller?",
            icon: "warning",
            buttons: {
                cancel: "No, Keep Order",
                confirm: {
                    text: "Yes, Request Cancel",
                    value: true,
                },
            },
            dangerMode: true,
        })
        .then((willCancel) => {
            if (willCancel) {
                document.getElementById('cancel-form-' + orderId).submit();
            }
        });
    }
</script>

<style>
    /* Professional Maroon and Red Button Styling for SweetAlert */
    .swal-button--confirm {
        background-color: #dc2626 !important; /* Red for danger action */
    }
    .swal-button--cancel {
        background-color: #5b2c2c !important; /* Maroon for keep order */
        color: white !important;
    }
</style>
@endsection