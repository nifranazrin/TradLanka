@extends('layouts.frontend')

@section('content')
<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white p-8 rounded-2xl shadow-sm border overflow-hidden">
            
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-[#5b2c2c]">Write a Review</h2>
                {{-- Returns user to the review dashboard --}}
                <a href="{{ route('user.reviews.index') }}" class="text-gray-400 hover:text-[#5b2c2c] transition">
                    <i class="fas fa-times text-xl"></i>
                </a>
            </div>
            
            {{-- Product Summary Card --}}
            <div class="flex items-center gap-4 mb-8 p-4 bg-[#fcfaf7] rounded-xl border border-orange-50">
                <div class="w-20 h-20 bg-white rounded-lg border border-gray-100 overflow-hidden flex-shrink-0">
                    <img src="{{ asset('storage/' . ($product->image ?? 'default.jpg')) }}" 
                         class="w-full h-full object-contain p-1" alt="{{ $product->name }}">
                </div>
                <div>
        
                    <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="font-bold text-gray-800 hover:text-[#5b2c2c] transition leading-snug block">
                        {{ $product->name }}
                        <i class="fas fa-external-link-alt text-[10px] ml-1 text-gray-400"></i>
                    </a>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">
                            Verified Purchase
                        </span>
                    </div>
                </div>
            </div>

            {{-- Form Submission: Points to user.reviews.store (web.php line 118) --}}
            <form action="{{ route('user.reviews.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                {{-- Interactive Star Rating --}}
                <div class="mb-8 text-center">
                    <label class="block font-bold text-gray-700 mb-3">Overall Rating</label>
                    <div class="flex items-center justify-center gap-2 text-3xl">
                        <input type="hidden" name="rating" id="rating_value" value="5" required>
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" onclick="setRating({{ $i }})" id="star-{{ $i }}" class="star-btn text-yellow-400 transition-transform hover:scale-110 focus:outline-none">
                                <i class="fas fa-star"></i>
                            </button>
                        @endfor
                    </div>
                </div>

                 <div class="mb-6">
                    <label class="block font-bold text-gray-700 mb-2">Review Content (Optional)</label>
                    <textarea name="comment" rows="4" 
                        class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-[#5b2c2c]/20 focus:border-[#5b2c2c] transition-all outline-none" 
                        placeholder="What did you think of the quality and appearance? (Optional)"></textarea>
                </div>
                {{-- Image Upload Input --}}
                <div class="mb-6">
                    <label class="block font-bold text-gray-700 mb-2">Upload Photo (Optional)</label>
                    <input type="file" name="image" accept="image/*" 
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-gray-100 file:text-[#5b2c2c] hover:file:bg-gray-200 cursor-pointer">
                </div>

                <div class="mb-8 p-4 bg-gray-50 rounded-xl flex items-center gap-3">
                    <input type="checkbox" name="is_anonymous" id="is_anon" value="1" 
                        class="w-5 h-5 rounded border-gray-300 text-[#5b2c2c] focus:ring-[#5b2c2c]">
                    <label for="is_anon" class="text-sm font-bold text-gray-700 cursor-pointer">Post anonymously</label>
                </div>

                <button type="submit" class="w-full bg-[#5b2c2c] text-white py-4 rounded-xl font-bold hover:bg-[#4a2424] transition-all transform active:scale-95 shadow-lg shadow-[#5b2c2c]/20">
                    Submit Review
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function setRating(val) {
        document.getElementById('rating_value').value = val;
        for (let i = 1; i <= 5; i++) {
            const star = document.getElementById('star-' + i);
            if (i <= val) {
                star.classList.add('text-yellow-400');
                star.classList.remove('text-gray-200');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-200');
            }
        }
    }
    // Set initial 5-star state
    setRating(5);
</script>
@endsection