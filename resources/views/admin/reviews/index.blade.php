@extends('layouts.admin')

@section('content')
<div class="container px-4 mx-auto mt-4 mb-4">
    <h2 class="page-title" style="font-size:32px; font-weight:800; color:#111827;">Customer Reviews</h2>
    <p class="page-subtitle" style="font-size:15px; color:#4b5563;">Monitor and manage all customer feedback across the platform</p>

    {{-- Centered Star Rating Filter --}}
    <div class="row mb-4 justify-content-center">
        <div class="col-md-8 text-center">
            <div class="btn-group shadow-sm">
                <a href="{{ route('admin.reviews') }}" 
                   class="btn {{ !request('rating') ? 'text-white' : '' }}" 
                   style="background-color: {{ !request('rating') ? '#5b2c2c' : '#fff' }}; border: 1px solid #5b2c2c; color: {{ !request('rating') ? '#fff' : '#5b2c2c' }};">
                   All
                </a>
                @for($i=5; $i>=1; $i--)
                    <a href="{{ route('admin.reviews', ['rating' => $i]) }}" 
                       class="btn {{ request('rating') == $i ? 'text-white' : '' }}" 
                       style="background-color: {{ request('rating') == $i ? '#5b2c2c' : '#fff' }}; border: 1px solid #5b2c2c; color: {{ request('rating') == $i ? '#fff' : '#5b2c2c' }};">
                        {{ $i }} <i class="bi bi-star-fill"></i>
                    </a>
                @endfor
            </div>
        </div>
    </div>

    {{-- Fixed Table Card --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <table class="w-full border-collapse" style="table-layout: auto; width: 100% !important;">
            <thead style="background:#5b2c2c; color:#fff; text-transform:uppercase; font-size:12px;">
                <tr>
                    <th class="px-5 py-4 text-left">Customer ID</th>
                    <th class="px-5 py-4 text-left">Product</th>
                    <th class="px-5 py-4 text-center">Rating</th>
                    <th class="px-5 py-4 text-left">Comment</th>
                    <th class="px-5 py-4 text-right">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                <tr class="border-b hover:bg-gray-50 transition-colors">
                    {{-- Customer ID --}}
                    <td class="px-5 py-4 text-sm text-gray-600">USR-{{ str_pad($review->user_id, 4, '0', STR_PAD_LEFT) }}</td>
                    
                    {{-- Clickable Product Link --}}
                    <td class="px-5 py-4 font-bold">
                        <a href="{{ url('product/' . $review->product->slug) }}" target="_blank" class="text-blue-600 hover:underline">
                            {{ $review->product->name }}
                        </a>
                    </td>

                    {{-- Centered Star Rating --}}
                    <td class="px-5 py-4 text-center text-nowrap">
                        @for($j=1; $j<=5; $j++)
                            <i class="bi bi-star-fill {{ $j <= $review->rating ? 'text-warning' : 'text-gray-300' }}" style="font-size: 14px;"></i>
                        @endfor
                    </td>

                    {{-- Comment --}}
                    <td class="px-5 py-4 italic text-gray-600">"{{ $review->comment }}"</td>
                    
                    {{-- Date aligned to the right --}}
                    <td class="px-5 py-4 text-sm text-right text-gray-500">{{ $review->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-gray-500 font-medium">No reviews found for this rating.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection