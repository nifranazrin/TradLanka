<div class="relative w-full max-w-xl mx-auto z-[100]"> 
    {{-- ^^^ CHANGED z-50 to z-[100] to make sure it shows ON TOP of everything --}}
    
    {{-- Search Input Container --}}
    <div class="relative">
        
        {{-- 1. The Input Field --}}
        <input type="text" 
               wire:model.live.debounce.300ms="query"
               wire:keydown.enter="search"
               placeholder="Search for authentic Sri Lankan products..." 
               class="w-full py-2 pl-5 pr-14 rounded-full text-gray-700 focus:outline-none shadow-inner bg-gray-100 focus:ring-2 focus:ring-[#5b2c2c] transition-all"
        >
        
        {{-- 2. Loading Spinner --}}
        <div wire:loading class="absolute right-12 top-1/2 transform -translate-y-1/2 mr-2">
            <svg class="animate-spin h-4 w-4 text-[#5b2c2c]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        {{-- 3. Camera Icon --}}
        <button class="absolute right-3 top-1/2 transform -translate-y-1/2 text-green-600 hover:text-green-800 transition-colors">
            <i class="fas fa-camera text-xl"></i>
        </button>
    </div>

    {{-- 4. Live Search Results Dropdown --}}
    @if(sizeof($results) > 0)
        <div class="absolute w-full bg-white shadow-xl rounded-lg mt-1 overflow-hidden border border-gray-100">
            @foreach($results as $product)
                <a href="{{ route('product.show', $product->slug) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-[#fff7f5] transition border-b border-gray-100 last:border-0 group">
                    {{-- Image with Fallback --}}
                    <img src="{{ $product->image_url ?? 'https://via.placeholder.com/50' }}" 
                         alt="{{ $product->name }}" 
                         class="w-10 h-10 object-cover rounded border border-gray-200">
                    
                    {{-- Text Details --}}
                    <div>
                        <p class="text-sm font-medium text-gray-800 group-hover:text-[#5b2c2c]">
                            {{ $product->name }}
                        </p>
                        <p class="text-xs text-[#e95b2c] font-semibold">
                            Rs {{ number_format($product->price, 2) }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    @elseif(strlen($query) >= 2)
        {{-- No Results Found --}}
        <div class="absolute w-full bg-white shadow-xl rounded-lg mt-1 p-4 text-center text-gray-500 text-sm border border-gray-100">
            No products found for "{{ $query }}"
        </div>
    @endif

</div>