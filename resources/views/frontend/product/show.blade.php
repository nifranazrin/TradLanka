@extends('layouts.frontend')

@section('content')
@include('frontend.partials.header')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    // logic kept exactly as you had it to ensure compatibility
    $images = $product->images ?? collect();
    $mainPathRaw = $product->image ?? optional($images->first())->path;
    $mainPath = $mainPathRaw ? preg_replace('/^public\//', '', $mainPathRaw) : null;

    if ($mainPath) {
        $mainUrl = Str::startsWith($mainPath, ['http://','https://']) ? $mainPath : Storage::url($mainPath);
    } else {
        $mainUrl = 'https://via.placeholder.com/900x700?text=No+Image';
    }

    $altMain = e($product->name ?: 'Product image');
@endphp

{{-- Breadcrumb + Share (top) --}}
<div class="max-w-6xl mx-auto px-6 lg:px-10 mt-6 flex items-center justify-between">
  <nav class="text-sm text-gray-600" aria-label="Breadcrumb">
    <ol class="list-reset flex items-center space-x-2">
      {{-- 1. Home Link --}}
      <li>
        <a href="{{ route('home') }}" class="text-gray-600 hover:text-[#5b2c2c]">Home</a>
      </li>

      {{-- 2. Category Link (FIXED: Added this section) --}}
      @if($product->category)
          <li><span class="mx-2 text-gray-400">/</span></li>
          <li>
              {{-- NOTE: Ensure 'categories.show' exists in your routes/web.php --}}
              <a href="{{ route('categories.show', $product->category->slug) }}" class="text-gray-600 hover:text-[#5b2c2c]">
                  {{ $product->category->name }}
              </a>
          </li>
      @endif

      {{-- 3. Product Name --}}
      <li><span class="mx-2 text-gray-400">/</span></li>
      <li class="text-gray-800 font-medium truncate max-w-xs">
          {{ \Illuminate\Support\Str::limit($product->name, 60) }}
      </li>
    </ol>
  </nav>

  <div class="flex items-center space-x-3">
    {{-- Copy Link --}}
    <button id="copyLinkBtn"
            data-url="{{ url()->current() }}"
            class="inline-flex items-center gap-2 px-3 py-1.5 border rounded text-sm hover:bg-gray-100">
      <i class="fas fa-link"></i> Copy Link
    </button>

    {{-- Mobile Share --}}
    <button id="nativeShareBtn"
            data-url="{{ url()->current() }}"
            class="inline-flex items-center gap-2 px-3 py-1.5 border rounded text-sm hover:bg-gray-100">
      <i class="fas fa-share-alt"></i> Share
    </button>
  </div>
</div>

<div class="bg-[#f8f4f1] min-h-screen pt-6 pb-20">

    <div class="max-w-6xl mx-auto px-6 lg:px-10 grid grid-cols-1 lg:grid-cols-2 gap-12 mt-6">

        {{-- LEFT: MAIN IMAGE + GALLERY --}}
        <div>
            {{-- Main Image --}}
            <div class="w-full h-[420px] rounded-xl overflow-hidden shadow">
                <img src="{{ $mainUrl }}"
                     id="mainImage"
                     class="w-full h-full object-cover"
                     alt="{{ $altMain }}">
            </div>

            {{-- Thumbnails --}}
            <div class="flex space-x-3 mt-4 overflow-x-auto">
                {{-- Always show main image first as a thumbnail --}}
                <img src="{{ $mainUrl }}"
                     onclick="changeImage(this)"
                     class="w-20 h-20 rounded-lg object-cover border-2 border-transparent hover:border-[#5b2c2c] cursor-pointer flex-shrink-0"
                     alt="{{ $altMain }}">

                {{-- Gallery images (if any) --}}
                @if($images->count())
                    @foreach($images->sortBy('sort_order')->values() as $gimg)
                        @php
                            $gpathRaw = $gimg->path ?? null;
                            $gpath = $gpathRaw ? preg_replace('/^public\//', '', $gpathRaw) : null;
                            $gurl = $gpath
                                ? (Str::startsWith($gpath, ['http://','https://']) ? $gpath : Storage::url($gpath))
                                : 'https://via.placeholder.com/150?text=No+Image';
                            $galt = e($product->name . ' gallery image');
                        @endphp

                        <img src="{{ $gurl }}"
                             onclick="changeImage(this)"
                             class="w-20 h-20 rounded-lg object-cover border-2 border-transparent hover:border-[#5b2c2c] cursor-pointer flex-shrink-0"
                             alt="{{ $galt }}">
                    @endforeach
                @endif
            </div>
        </div>

        {{-- RIGHT: Product Details --}}
        <div class="text-gray-800">

            {{-- Name --}}
            <h1 class="text-3xl font-extrabold text-[#5b2c2c] mb-3">
                {{ $product->name }}
            </h1>

            {{-- Price --}}
            <div class="text-3xl font-bold text-[#e95b2c] mb-4">
                Rs {{ number_format($product->price ?? 0, 2) }}
            </div>

            {{-- Description --}}
            <h3 class="text-lg font-semibold text-[#5b2c2c] mt-6 mb-2">Description</h3>
            <p class="text-gray-700">
                {!! nl2br(e($product->description ?? 'No description available.')) !!}
            </p>

            {{-- Size --}}
            <div class="mt-6">
                <label class="block text-sm font-medium mb-2">Size</label>
                {{-- WARNING: These sizes are hardcoded. Make sure this matches your products. --}}
                @php $sizes = ['100g','250g','500g','1kg']; @endphp

                <div class="flex gap-3">
                    @foreach($sizes as $s)
                        <button type="button"
                                class="sizeOption px-4 py-2 rounded-md border"
                                data-size="{{ $s }}">
                            {{ $s }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Quantity --}}
            <div class="mt-6">
                <label class="block text-sm font-medium mb-2">Quantity</label>
                <div class="inline-flex items-center border rounded-md overflow-hidden">
                    <button id="decreaseQty" class="px-3 py-2">-</button>
                    <input id="productQty" type="text" value="1"
                           class="w-14 text-center border-l border-r focus:outline-none">
                    <button id="increaseQty" class="px-3 py-2">+</button>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="mt-8 flex flex-col sm:flex-row gap-3">

                {{-- Add to Cart --}}
                <button id="addToCartBtn"
                        data-id="{{ $product->id }}"
                        class="w-40 h-12 bg-[#5b2c2c] hover:bg-[#462020] text-white rounded-lg flex items-center justify-center gap-2 shadow">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>

                {{-- Buy Now --}}
                <button id="buyNowBtn"
                        data-id="{{ $product->id }}"
                        class="w-40 h-12 bg-[#d97706] hover:bg-[#b86504] text-white rounded-lg flex items-center justify-center gap-2 shadow">
                    <i class="fas fa-bolt"></i> Buy Now
                </button>
            </div>
        </div>
    </div>

</div>

{{-- Toast --}}
<div id="cartToast"
     class="hidden fixed bottom-6 right-6 bg-green-600 text-white px-5 py-3 rounded-lg shadow-lg z-50">
     Added to cart!
</div>

{{-- JS --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Change main image when clicking thumbnail
    window.changeImage = function(el) {
        document.getElementById('mainImage').src = el.src;
    };

    // Size selector
    const sizeBtns = document.querySelectorAll('.sizeOption');
    let selectedSize = sizeBtns.length ? sizeBtns[0].dataset.size : null;
    if (sizeBtns.length) sizeBtns[0].classList.add('bg-[#fff7f5]','border-[#5b2c2c]');

    sizeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            sizeBtns.forEach(b => b.classList.remove('bg-[#fff7f5]','border-[#5b2c2c]'));
            btn.classList.add('bg-[#fff7f5]','border-[#5b2c2c]');
            selectedSize = btn.dataset.size;
        });
    });

    // Quantity
    const qtyInput = document.getElementById('productQty');
    document.getElementById('decreaseQty').onclick = () => {
        let v = parseInt(qtyInput.value) || 1;
        if (v > 1) qtyInput.value = v - 1;
    };
    document.getElementById('increaseQty').onclick = () => {
        let v = parseInt(qtyInput.value) || 1;
        qtyInput.value = v + 1;
    };

    // Payload builder
    function cartPayload(id) {
        return {
            product_id: id,
            qty: parseInt(qtyInput.value) || 1,
            size: selectedSize
        };
    }

    // Add to cart
    document.getElementById('addToCartBtn').onclick = function () {
        // NOTE: Ensure route 'cart.add' is defined in routes/web.php
        fetch("{{ route('cart.add') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(cartPayload(this.dataset.id))
        }).then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            const toast = document.getElementById('cartToast');
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 2000);
        }).catch(err => {
            console.error(err);
            alert('Could not add to cart. Try again.');
        });
    };

    // Buy now
    document.getElementById('buyNowBtn').onclick = function () {
        fetch("{{ route('cart.add') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(cartPayload(this.dataset.id))
        }).then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            // NOTE: Ensure route 'cart.show' is defined in routes/web.php
            window.location.href = "{{ route('cart.show') }}";
        }).catch(err => {
            console.error(err);
            alert('Could not proceed to cart.');
        });
    };

    // Copy link
    document.getElementById('copyLinkBtn')?.addEventListener('click', function () {
        const url = this.dataset.url;
        if (navigator.clipboard) navigator.clipboard.writeText(url);
        else {
            const ta = document.createElement('textarea');
            ta.value = url;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            ta.remove();
        }
        alert('Link copied to clipboard');
    });

    // Native share (mobile)
    document.getElementById('nativeShareBtn')?.addEventListener('click', function () {
        const url = this.dataset.url;
        if (navigator.share) {
            navigator.share({ title: document.title, url }).catch(()=>{});
        } else {
            alert('Sharing is not supported on this device.');
        }
    });
});
</script>

@endsection