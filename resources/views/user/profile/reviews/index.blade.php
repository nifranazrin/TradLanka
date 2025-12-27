@extends('layouts.frontend')

@section('content')
<div class="min-h-screen py-10"
     style="background: url('{{ asset('storage/images/background.jpg') }}') 
            no-repeat center center fixed; 
            background-size: cover;">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      {{-- ✅ BREADCRUMB NAVIGATION --}}
<nav class="flex text-sm font-bold text-[#5b2c2c] mb-6 bg-white/80 backdrop-blur-sm p-3 rounded-lg w-fit shadow-sm">
    <ol class="list-none p-0 inline-flex">
        <li class="flex items-center">
            <a href="{{ route('user.profile.index') }}"
               class="hover:underline hover:text-[#8a4a4a]">
                My Profile
            </a>
            <span class="mx-2 text-gray-500">/</span>
        </li>

        <li class="flex items-center text-gray-600">
            My Reviews
        </li>
    </ol>
</nav>


        <div class="grid grid-cols-1">
            {{-- Main Content --}}
            <div class="lg:col-span-3">
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden">

                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-2xl font-bold text-[#5b2c2c]">My Reviews</h2>
                    </div>

                    {{-- Tabs --}}
                    <div class="flex border-b border-gray-100 bg-gray-50/50">
                        <button id="btn-to-review"
    class="flex-1 py-4 text-center text-sm font-bold border-b-2 border-[#5b2c2c] text-[#5b2c2c] flex items-center justify-center gap-2">

    To Review

    @if($toReviewCount > 0)
        <span
            class="inline-flex items-center justify-center 
                   w-6 h-6 rounded-full 
                   bg-red-600 text-white 
                   text-xs font-extrabold">
            {{ $toReviewCount }}
        </span>
    @endif
</button>


                        <button id="btn-history"
                            class="flex-1 py-4 text-center text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-[#5b2c2c]">
                            History ({{ $history->count() }})
                        </button>
                    </div>

                    <div class="p-6">

                        {{-- TO REVIEW --}}
                        <div id="panel-to-review" class="space-y-4">
                            @forelse($toReview as $item)
                                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-[#fcfaf7]">
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 bg-white rounded-lg border overflow-hidden">
                                            <img src="{{ asset('storage/' . ($item->product->image ?? 'default.jpg')) }}"
                                                 class="w-full h-full object-contain p-1">
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-800 text-sm">
                                                {{ $item->product->name ?? 'Product' }}
                                            </h4>
                                            <p class="text-xs text-gray-500 italic">
                                                Delivered: {{ $item->order->updated_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    <a href="{{ route('user.reviews.create', $item->product_id) }}"
                                       class="bg-[#5b2c2c] text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-[#4a2424]">
                                        Review
                                    </a>
                                </div>
                            @empty
                                <div class="text-center py-10 text-gray-400 italic">
                                    No reviews pending.
                                </div>
                            @endforelse
                        </div>

                        {{-- HISTORY --}}
                        <div id="panel-history" class="space-y-6" style="display:none;">
                            @forelse($history as $review)
                                <div class="p-5 rounded-xl border border-gray-100 bg-white shadow-sm">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-lg border overflow-hidden bg-gray-50">
                                                <img src="{{ asset('storage/' . ($review->product->image ?? 'default.jpg')) }}"
                                                     class="w-full h-full object-contain p-0.5">
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800 text-sm">
                                                    @if($review->product)
                                                        <a href="{{ route('product.show', $review->product) }}"
                                                           class="text-[#5b2c2c] hover:underline">
                                                            {{ $review->product->name }}
                                                        </a>
                                                    @else
                                                        <span class="text-gray-400 italic">
                                                            Product not available
                                                        </span>
                                                    @endif
                                                </h4>

                                                <div class="flex text-yellow-400 text-[10px] mt-1">
                                                    @for($i=1; $i<=5; $i++)
                                                        <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-[10px] text-gray-400">
                                            {{ $review->created_at->format('M d, Y') }}
                                        </span>
                                    </div>

                                    <div class="text-sm text-gray-600 italic border-l-4 border-[#5b2c2c]/10 pl-4 py-1">
                                        "{{ $review->comment }}"
                                    </div>

                                    @if($review->image)
                                        <img src="{{ asset('storage/' . $review->image) }}"
                                             class="mt-3 w-24 h-24 object-cover rounded-lg border">
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-10 text-gray-400 italic">
                                    No history found.
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- TAB + SUCCESS ALERT SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnToReview = document.getElementById('btn-to-review');
    const btnHistory  = document.getElementById('btn-history');
    const panelToReview = document.getElementById('panel-to-review');
    const panelHistory  = document.getElementById('panel-history');

    function openTab(tab) {
        if (tab === 'history') {
            panelHistory.style.display = 'block';
            panelToReview.style.display = 'none';
            
            // Update Button Styles
            btnHistory.classList.add('border-[#5b2c2c]', 'text-[#5b2c2c]', 'font-bold');
            btnHistory.classList.remove('border-transparent', 'text-gray-500');
            
            btnToReview.classList.remove('border-[#5b2c2c]', 'text-[#5b2c2c]', 'font-bold');
            btnToReview.classList.add('border-transparent', 'text-gray-500');
        } else {
            panelToReview.style.display = 'block';
            panelHistory.style.display = 'none';
            
            // Update Button Styles
            btnToReview.classList.add('border-[#5b2c2c]', 'text-[#5b2c2c]', 'font-bold');
            btnToReview.classList.remove('border-transparent', 'text-gray-500');
            
            btnHistory.classList.remove('border-[#5b2c2c]', 'text-[#5b2c2c]', 'font-bold');
            btnHistory.classList.add('border-transparent', 'text-gray-500');
        }
    }

    btnToReview.addEventListener('click', () => openTab('to-review'));
    btnHistory.addEventListener('click', () => openTab('history'));

    // Handle initial state or redirect after success
    @if(session('success'))
        openTab('history');
        Swal.fire({
            icon: 'success',
            title: 'Review Submitted!',
            text: '{{ session('success') }}',
            background: '#4a1d1d',
            color: '#facc15',
            iconColor: '#facc15',
            confirmButtonColor: '#facc15',
            confirmButtonText: 'OK',
            timer: 3000,
            timerProgressBar: true
        });
    @endif
});
</script>
@endsection
