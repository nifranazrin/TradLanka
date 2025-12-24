<div x-data="{ 
    showModal: false, 
    dragging: false,
    handleDrop(e) {
        this.dragging = false;
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            this.$refs.fileInput.files = files;
            this.submitForm();
        }
    },
    submitForm() {
        this.$refs.loading.classList.remove('hidden');
        this.$refs.loading.classList.add('flex');
        this.$refs.form.submit();
    }
}" class="relative w-full max-w-xl mx-auto z-[100]">

    {{-- SEARCH BAR CONTAINER --}}
    <div class="relative">
        {{-- Text Input --}}
        <input type="text" 
               wire:model.live.debounce.300ms="query"
               wire:keydown.enter="search"
               placeholder="Search for authentic Sri Lankan products..." 
               class="w-full py-2 pl-5 pr-14 rounded-full text-gray-700 focus:outline-none shadow-inner bg-gray-100 focus:ring-2 focus:ring-[#5b2c2c] transition-all">

        {{-- Loading Spinner for Livewire Text Search --}}
        <div wire:loading class="absolute right-12 top-1/2 transform -translate-y-1/2 mr-2">
            <svg class="animate-spin h-4 w-4 text-[#5b2c2c]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        {{-- Camera Button Trigger for Modal --}}
        <button type="button" @click="showModal = true"
            class="absolute right-2 top-1/2 transform -translate-y-1/2 p-2 text-gray-400 hover:text-[#5b2c2c] transition-colors"
            title="Search by Image">
            <i class="fas fa-camera text-xl"></i>
        </button>
    </div>

    {{-- LIVE SEARCH RESULTS DROPDOWN --}}
    @if(sizeof($results) > 0)
        <div class="absolute w-full bg-white shadow-xl rounded-lg mt-1 overflow-hidden border border-gray-100 z-[110]">
            @foreach($results as $product)
                <a href="{{ route('product.show', $product->slug) }}" 
                   class="flex items-center gap-3 px-4 py-3 hover:bg-[#fff7f5] transition border-b border-gray-100 last:border-0 group">
                    
                    {{-- Image with fallback logic --}}
                    <img src="{{ $product->image ? asset('storage/' . $product->image) : ($product->image_url ?? 'https://via.placeholder.com/50') }}" 
                         alt="{{ $product->name }}" 
                         class="w-10 h-10 object-cover rounded border border-gray-200">
                    
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
        <div class="absolute w-full bg-white shadow-xl rounded-lg mt-1 p-4 text-center text-gray-500 text-sm border border-gray-100 z-[110]">
            No products found for "{{ $query }}"
        </div>
    @endif

    {{-- VISUAL SEARCH MODAL (Teleported to Body for perfect layering) --}}
    <template x-teleport="body">
        <div x-show="showModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm" 
             style="display: none;">
            
            <div @click.outside="showModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
                <div class="flex justify-between items-center px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-[#5b2c2c]">Visual Search</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-8">
                    <form x-ref="form" action="{{ route('search.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div @dragover.prevent="dragging = true" 
                             @dragleave.prevent="dragging = false"
                             @drop.prevent="handleDrop($event)" 
                             @click="$refs.fileInput.click()"
                             :class="{ 'border-[#5b2c2c] bg-[#fff5f2]': dragging }"
                             class="relative border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 h-64 flex flex-col items-center justify-center cursor-pointer hover:border-[#5b2c2c] hover:bg-[#fffdfc] transition-all">
                            
                            <div class="text-center space-y-4 pointer-events-none">
                                <i class="fas fa-cloud-upload-alt text-4xl text-[#5b2c2c]"></i>
                                <p class="text-gray-700 font-medium">Drag & Drop or Click to Upload</p>
                                <p class="text-xs text-gray-400">Search using a product photo</p>
                            </div>
                            
                            {{-- Image Loading Overlay --}}
                            <div x-ref="loading" class="hidden absolute inset-0 bg-white/95 z-10 flex flex-col items-center justify-center rounded-xl">
                                <i class="fas fa-circle-notch fa-spin text-4xl text-[#5b2c2c] mb-3"></i>
                                <p class="text-[#5b2c2c] font-medium animate-pulse">Analyzing Image...</p>
                            </div>
                            
                            <input x-ref="fileInput" @change="submitForm()" type="file" name="search_image" class="hidden" accept="image/*">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>