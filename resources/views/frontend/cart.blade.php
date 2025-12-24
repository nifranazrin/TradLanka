@extends('layouts.frontend')

@section('content')
<style>
    /* Custom SweetAlert Styling */
    .swal-modal { background-color:#36170b!important; border:3px solid #d8b954; }
    .swal-title { color:#e9d374!important; font-size:24px; }
    .swal-text { color:#ece19f!important; font-weight:600; text-align:center; }
    .swal-footer { text-align:center!important; margin-top:15px!important; }
    .swal-button { background-color:#e0ccc4!important; color:#4e2525!important; border:none; box-shadow:none!important; padding:10px 25px; font-weight:bold; border-radius:5px; }
    .swal-button:hover { background-color:#e8ebc1!important; }
    .swal-button--cancel { background-color:#dabbbb!important; color:#5b2c2c!important; border:1px solid #e4da52!important; }
</style>

<div class="min-h-screen py-8 pb-32"
     style="background:url('{{ asset('storage/images/background.jpg') }}') no-repeat center center fixed; background-size:cover;">

    <div class="container mx-auto px-4 lg:px-20 max-w-5xl">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-[#5b2c2c]">
                My Cart (<span id="cartCount">{{ $cartItems->count() }}</span>)
            </h1>

            {{-- DELETE BUTTON --}}
            <button id="deleteSelectedBtn"
                    class="bg-white text-[#5b2c2c] px-4 py-2 rounded shadow hover:bg-red-50 hover:text-red-600 transition"
                    style="display: none;"> {{-- Hidden by default until items selected --}}
                <i class="fas fa-trash-alt fa-lg"></i> Delete Selected
            </button>
        </div>

        @if($cartItems->count() === 0)
            <div class="bg-white p-10 rounded-lg shadow text-center">
                <p class="text-lg text-gray-600">Your cart is empty 🛒</p>
                <a href="{{ url('/') }}" class="mt-4 inline-block text-[#5b2c2c] font-bold underline">Continue Shopping</a>
            </div>
        @else

        <div class="space-y-4" id="cartContainer">
            @foreach ($cartItems as $item)
                @if($item->product)
                    @php
                        // --- LOGIC TO HANDLE VARIANTS vs SIMPLE PRODUCTS ---
                        // If variant exists, use variant price & name. Else use product defaults.
                        $currentPrice = $item->variant ? $item->variant->price : $item->product->price;
                        $currentName  = $item->product->name;
                        
                        if($item->variant) {
                            $currentName .= ' (' . $item->variant->unit_label . ')';
                        }
                        
                        // Calculate row total for display
                        $rowTotal = $currentPrice * $item->product_qty;
                    @endphp

                    <div class="cart-item-row bg-white p-4 rounded-lg shadow-sm flex flex-col md:flex-row items-center gap-4"
                         id="cart-row-{{ $item->id }}">

                        {{-- CHECKBOX --}}
                        <input type="checkbox"
                               class="cart-checkbox w-5 h-5 cursor-pointer accent-[#5b2c2c]"
                               data-id="{{ $item->id }}"
                               data-price="{{ $currentPrice }}"
                               checked>

                        {{-- IMAGE --}}
                        <div class="w-24 h-24 flex-shrink-0">
                            @if($item->product->image)
                                <img src="{{ asset('storage/'.$item->product->image) }}" class="w-full h-full object-cover rounded border">
                            @else
                                <div class="w-full h-full bg-gray-200 rounded flex items-center justify-center text-xs">No Img</div>
                            @endif
                        </div>

                        {{-- DETAILS --}}
                        <div class="flex-1 text-center md:text-left">
                            <p class="font-semibold text-lg text-[#5b2c2c]">{{ $currentName }}</p>
                            <p class="text-sm text-gray-500">
                                Unit Price: Rs {{ number_format($currentPrice, 2) }}
                            </p>
                            @if($item->variant && $item->variant->stock < 5)
                                <p class="text-xs text-red-500 font-bold mt-1">Only {{ $item->variant->stock }} left!</p>
                            @endif
                        </div>

                        {{-- QUANTITY & TOTAL --}}
                        <div class="flex flex-col items-center md:items-end gap-2">
                            <div class="flex items-center border rounded overflow-hidden">
                                <button class="decrement-btn px-3 py-1 bg-gray-100 hover:bg-gray-200 transition"
                                        data-id="{{ $item->id }}">-</button>

                                <input type="text"
                                       id="qty-{{ $item->id }}"
                                       value="{{ $item->product_qty }}"
                                       readonly
                                       class="w-12 text-center border-x font-semibold">

                                <button class="increment-btn px-3 py-1 bg-gray-100 hover:bg-gray-200 transition"
                                        data-id="{{ $item->id }}">+</button>
                            </div>

                            <p class="font-bold text-[#5b2c2c] text-lg">
                                   <span id="currency-symbol-{{ $item->id }}">{{ session('currency') == 'USD' ? '$' : 'Rs' }}</span>
<span id="row-total-{{ $item->id }}">
    {{ session('currency') == 'USD' ? number_format($rowTotal * 0.0032, 2) : number_format($rowTotal, 2) }}
</span>
                            </p>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        @endif
    </div>

    {{-- BOTTOM BAR --}}
    <div class="fixed bottom-0 left-0 w-full bg-white border-t p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-50">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-500">Total (<span id="selectedCount">0</span> items)</p>
                <p class="text-2xl font-bold text-[#e95b2c]">
                    <span id="grandTotalSymbol">{{ session('currency') == 'USD' ? '$' : 'Rs' }}</span>
<span id="grandTotal">0.00</span>
                </p>
            </div>
            <button id="checkoutBtn"
                    class="bg-[#5b2c2c] text-white px-8 py-3 rounded-lg font-bold shadow-lg hover:bg-[#3e1e1e] transition disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                CHECKOUT
            </button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const checkboxes = document.querySelectorAll('.cart-checkbox');
    const grandTotalEl = document.getElementById('grandTotal');
    const selectedCountEl = document.getElementById('selectedCount');
    const checkoutBtn = document.getElementById('checkoutBtn');
    const deleteBtn = document.getElementById('deleteSelectedBtn');

  // --- 1. CALCULATE TOTALS (UPDATED FOR CURRENCY) ---
function calculateTotals() {
    let total = 0;
    let count = 0;

    // Get currency and rate from PHP Session
    const currency = "{{ session('currency', 'LKR') }}";
    const rate = 0.0032; // Matches your Product Model and Controller

    document.querySelectorAll('.cart-checkbox').forEach(box => {
        if (box.checked) {
            const id = box.dataset.id;
            const price = parseFloat(box.dataset.price);
            const qtyInput = document.getElementById('qty-' + id);
            
            if(qtyInput) {
                const qty = parseInt(qtyInput.value);
                const baseRowTotal = price * qty; // Raw LKR amount
                
                // Calculate converted display price if USD
                const displayRowTotal = (currency === 'USD') ? (baseRowTotal * rate) : baseRowTotal;
                
                // Update Row Total Text
                const rowTotalEl = document.getElementById('row-total-' + id);
                if(rowTotalEl) {
                    rowTotalEl.innerText = displayRowTotal.toLocaleString('en-US', { 
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2 
                    });
                }

                total += baseRowTotal; // Keep tracking the total in base LKR
                count++;
            }
        }
    });

    // Update the Grand Total in the bottom bar
    const displayGrandTotal = (currency === 'USD') ? (total * rate) : total;
    const symbol = (currency === 'USD') ? '$' : 'Rs';

    // Update the symbol span and the value
    const symbolEl = document.getElementById('grandTotalSymbol');
    if(symbolEl) symbolEl.innerText = symbol;

    grandTotalEl.innerText = displayGrandTotal.toLocaleString('en-US', { 
        minimumFractionDigits: 2,
        maximumFractionDigits: 2 
    });

    selectedCountEl.innerText = count;
    checkoutBtn.disabled = count === 0;

    if(deleteBtn) {
        deleteBtn.style.display = count > 0 ? 'block' : 'none';
    }
}

    // --- 2. UPDATE SERVER QUANTITY (AJAX) ---
    function updateServerQuantity(cartId, qty) {
        fetch("{{ route('cart.update') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ cart_id: cartId, qty: qty })
        })
        .then(res => res.json())
        .then(data => {
            if(data.status !== 'success') {
                swal("Error", "Could not update cart", "error");
            }
        })
        .catch(err => console.error(err));
    }

    // --- 3. INCREMENT / DECREMENT LISTENERS ---
    document.querySelectorAll('.increment-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const input = document.getElementById('qty-' + id);
            let newQty = parseInt(input.value) + 1;
            
            input.value = newQty;
            calculateTotals();
            updateServerQuantity(id, newQty); // Update DB
        });
    });

    document.querySelectorAll('.decrement-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const input = document.getElementById('qty-' + id);
            if (parseInt(input.value) > 1) {
                let newQty = parseInt(input.value) - 1;
                input.value = newQty;
                calculateTotals();
                updateServerQuantity(id, newQty); // Update DB
            }
        });
    });
// --- 4. CHECKOUT CLICK ---
checkoutBtn.addEventListener('click', () => {
    let ids = [];
    document.querySelectorAll('.cart-checkbox').forEach(box => {
        if (box.checked) ids.push(box.dataset.id);
    });

    // ✅ CORRECTED ROUTE
    fetch("{{ route('cart.checkout.store') }}", { 
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ ids: ids })
    }).then(res => {
        if(res.ok) {
            window.location.href = "{{ route('checkout.index') }}";
        } else {
            console.error("Server Error:", res); // Use console to debug
            alert("Something went wrong initializing checkout. Check console for details.");
        }
    });
});

    // --- 5. DELETE SELECTED ---
    if(deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            let ids = [];
            document.querySelectorAll('.cart-checkbox').forEach(box => {
                if (box.checked) ids.push(box.dataset.id);
            });

            if(ids.length === 0) return;

            swal({
                title: "Are you sure?",
                text: "These items will be removed from your cart.",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    fetch("{{ route('cart.delete') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({ ids: ids })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.status === 'success') {
                            location.reload(); // Reload to remove rows
                        } else {
                            swal("Error", "Failed to delete items", "error");
                        }
                    });
                }
            });
        });
    }

    // Initial Calculation on Load
    calculateTotals();
});
</script>
@endsection