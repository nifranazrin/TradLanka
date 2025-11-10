@extends('layouts.frontend')

@section('content')
<div class="container mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-5">Your Shopping Cart</h2>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(count($cart) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="py-2 px-4 text-left">Image</th>
                        <th class="py-2 px-4 text-left">Product</th>
                        <th class="py-2 px-4 text-left">Price</th>
                        <th class="py-2 px-4 text-left">Qty</th>
                        <th class="py-2 px-4 text-left">Total</th>
                        <th class="py-2 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @foreach($cart as $id => $item)
                        @php $subtotal = $item['price'] * $item['quantity']; $total += $subtotal; @endphp
                        <tr class="border-t">
                            <td class="py-2 px-4">
                                <img src="{{ asset('storage/'.$item['image']) }}" class="w-16 h-16 object-cover rounded">
                            </td>
                            <td class="py-2 px-4">{{ $item['name'] }}</td>
                            <td class="py-2 px-4">Rs {{ number_format($item['price'], 2) }}</td>
                            <td class="py-2 px-4">{{ $item['quantity'] }}</td>
                            <td class="py-2 px-4">Rs {{ number_format($subtotal, 2) }}</td>
                            <td class="py-2 px-4 text-center">
                                <a href="{{ route('cart.remove', $id) }}" 
                                   class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">Remove</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center mt-5">
            <h3 class="text-xl font-semibold">Total: Rs {{ number_format($total, 2) }}</h3>
            <div class="flex space-x-3">
                <a href="{{ route('cart.clear') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">Clear Cart</a>
                <a href="{{ route('checkout') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Checkout</a>
            </div>
        </div>
    @else
        <p class="text-gray-600">Your cart is empty 😕</p>
    @endif
</div>
@endsection
