<div x-data="{ 
    showModal: false, 
    dragging: false,
    previewUrl: null,

    init() {
        // Recover image from session storage on page load
        const saved = sessionStorage.getItem('search_image_preview');
        if (saved) {
            this.previewUrl = saved;
        }
    },
    
    handleDrop(e) {
        this.dragging = false;
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            this.$refs.fileInput.files = files;
            this.updatePreviewAndSubmit(); 
        }
    },

    updatePreviewAndSubmit() {
        const file = this.$refs.fileInput.files[0];
        if (file) {
            // Create persistent Base64 string
            const reader = new FileReader();
            reader.onload = (e) => {
                this.previewUrl = e.target.result;
                // Store locally so it survives the redirect
                sessionStorage.setItem('search_image_preview', this.previewUrl);
                this.submitForm();
            };
            reader.readAsDataURL(file);
        }
    },

    submitForm() {
        this.$refs.loading.classList.remove('hidden');
        this.$refs.loading.classList.add('flex');
        this.$refs.form.submit();
    },

    clearImage() {
        this.previewUrl = null;
        this.$refs.fileInput.value = '';
        sessionStorage.removeItem('search_image_preview');
        // Optional: Redirect to clear visual search results
        window.location.href = '{{ route('search.index') }}';
    }
}" class="relative w-full max-w-xl mx-auto z-[100]">

    {{-- SEARCH BAR CONTAINER --}}
    <div class="relative flex items-center bg-gray-100 rounded-full shadow-inner border border-transparent focus-within:border-[#5b2c2c] focus-within:bg-white transition-all">
        
        {{-- PREVIEW THUMBNAIL (The Marked Place) --}}
        <template x-if="previewUrl">
            <div class="flex items-center pl-4 shrink-0">
                <div class="relative group">
                    <img :src="previewUrl" class="h-8 w-8 object-cover rounded-md border border-gray-200 shadow-sm">
                    <button @click.stop="clearImage()" type="button" 
                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-[10px] hover:bg-red-600 shadow-sm">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </template>

        <input type="text" 
               wire:model.live.debounce.300ms="query"
               wire:keydown.enter="search"
               placeholder="Search for products..." 
               {{-- Adjust padding dynamically so text doesn't hit the image --}}
               :class="previewUrl ? 'pl-3' : 'pl-5'" 
               class="w-full py-2.5 pr-14 rounded-full text-gray-700 bg-transparent focus:outline-none transition-all">

        {{-- Loading Spinner for Livewire --}}
        <div wire:loading class="absolute right-12 top-1/2 transform -translate-y-1/2 mr-2">
            <svg class="animate-spin h-4 w-4 text-[#5b2c2c]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <button type="button" @click="showModal = true"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-[#5b2c2c] transition-colors">
            <i class="fas fa-camera text-xl"></i>
        </button>
    </div>

    {{-- Results Dropdown (Simplified for layout) --}}
    @if(sizeof($results) > 0)
        <div class="absolute w-full bg-white shadow-xl rounded-lg mt-1 overflow-hidden border border-gray-100 z-[110]">
            @foreach($results as $product)
                <a href="{{ route('product.show', $product->slug) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-[#fff7f5] transition border-b border-gray-100">
                    <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/50' }}" class="w-10 h-10 object-cover rounded">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $product->name }}</p>
                        <p class="text-xs text-[#e95b2c] font-semibold">Rs {{ number_format($product->price, 2) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    {{-- MODAL TEMPLATE (Keep as you have it, but ensure teleport works) --}}
    <template x-teleport="body">
        <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm">
            <div @click.outside="showModal = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4">
                <div class="p-8">
                    <form x-ref="form" action="{{ route('search.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div @click="$refs.fileInput.click()" class="relative border-2 border-dashed border-gray-300 rounded-xl h-64 flex flex-col items-center justify-center cursor-pointer hover:border-[#5b2c2c]">
                            <i class="fas fa-cloud-upload-alt text-4xl text-[#5b2c2c] mb-4"></i>
                            <p class="font-medium">Click to Upload for Visual Search</p>
                            
                            <div x-ref="loading" class="hidden absolute inset-0 bg-white/95 flex flex-col items-center justify-center rounded-xl">
                                <i class="fas fa-circle-notch fa-spin text-4xl text-[#5b2c2c] mb-3"></i>
                                <p class="text-[#5b2c2c] font-medium animate-pulse">Analyzing Product...</p>
                            </div>
                            
                            <input x-ref="fileInput" @change="updatePreviewAndSubmit()" type="file" name="search_image" class="hidden" accept="image/*">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>