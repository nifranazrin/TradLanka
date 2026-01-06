@extends('layouts.frontend')

@section('content')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.0.2/css/flag-icons.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/css/intlTelInput.css">
<style>

    /* Custom Styling */
    .checkout-page { font-size:16px; font-family: sans-serif; }
    .checkout-page label { font-weight: 600; color: #374151; font-size: 14px; margin-bottom: 4px; display: block; }
    .iti { width: 100%; }
    .fi { width: 1.5em !important; line-height: 1em !important; display: inline-block !important; margin-right: 10px !important; vertical-align: middle; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { display: flex !important; align-items: center !important; }
    .billing-field { height: 50px; font-size: 15px; border: 1px solid #d1d5db; border-radius: 8px; background-color: #f9fafb; transition: all 0.3s; }
    .billing-field:focus { border-color: #800000; box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1); background-color: #fff; outline: none; }
    .checkout-card { border: 1px solid #e5e7eb; background: white; height: 100%; }
    .select2-container--default .select2-selection--single { height: 50px !important; border: 1px solid #d1d5db !important; border-radius: 8px !important; background-color: #f9fafb !important; display: flex !important; align-items: center !important; }
    .custom-textarea-large { width: 100%; border: 1px solid #ced4da; border-radius: 8px; padding: 15px; font-size: 0.9rem; background-color: #f8f9fa; resize: vertical; min-height: 150px; transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out; }
    .custom-textarea-large:focus { border-color: #800000; background-color: #ffffff; box-shadow: 0 0 5px rgba(128, 0, 0, 0.2); outline: none; }

</style>


<div class="checkout-page min-h-screen py-10" style="background:url('{{ asset('storage/images/background.jpg') }}') no-repeat center center fixed; background-size:cover;">
    <div class="container mx-auto px-4 lg:px-12">
        <form id="checkoutForm" action="{{ route('checkout.placeorder') }}" method="POST">

            {{-- Add this above <form id="checkoutForm" ...> --}}

@if ($errors->any())
    <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
        <p class="font-bold">Please fix the following errors:</p>
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

              {{-- LEFT : BILLING DETAILS --}}
<div class="checkout-card p-8 rounded-xl shadow-lg">
    <h2 class="text-2xl font-bold text-[#800000] mb-6 border-b pb-4">
        <i class="fas fa-shipping-fast mr-2"></i> Billing Details
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label>First Name</label>
            <input class="billing-field w-full px-4" name="fname" required 
                value="{{ old('fname', Auth::user()->fname ?? Auth::user()->name) }}" placeholder="First Name">
        </div>
        <div>
            <label>Last Name <span class="text-gray-400 font-normal">(Optional)</span></label>
            <input class="billing-field w-full px-4" name="lname" 
                value="{{ old('lname', Auth::user()->lname ?? '') }}" placeholder="Last Name">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-5">
        <div>
            <label>Email</label>
            <input class="billing-field w-full px-4" name="email" required 
                value="{{ old('email', Auth::user()->email) }}" placeholder="Email Address">
        </div>
        <div>
            <label>Phone Number</label>
            <input type="tel" id="phoneInput" class="billing-field w-full px-4" name="phone" 
                value="{{ old('phone', Auth::user()->phone ?? '') }}">
            <input type="hidden" name="full_phone" id="fullPhone">
        </div>
    </div>

    <div class="mt-5">
        <label>Country</label>
        <select name="country" id="countrySelect" class="billing-field w-full px-4" required>
    <option value="">Select Country</option>
    @php $savedCountry = old('country', Auth::user()->country ?? ''); @endphp
    
    {{-- Primary --}}
    @if(session('currency', 'LKR') !== 'USD')
        <option value="Sri Lanka" data-flag="lk" {{ $savedCountry == 'Sri Lanka' ? 'selected' : '' }}>Sri Lanka</option>
    @endif

    {{-- Middle East --}}
    <option value="United Arab Emirates" data-flag="ae" {{ $savedCountry == 'United Arab Emirates' ? 'selected' : '' }}>United Arab Emirates</option>
    <option value="Saudi Arabia" data-flag="sa" {{ $savedCountry == 'Saudi Arabia' ? 'selected' : '' }}>Saudi Arabia</option>
    <option value="Qatar" data-flag="qa" {{ $savedCountry == 'Qatar' ? 'selected' : '' }}>Qatar</option>
    <option value="Oman" data-flag="om" {{ $savedCountry == 'Oman' ? 'selected' : '' }}>Oman</option>
    <option value="Kuwait" data-flag="kw" {{ $savedCountry == 'Kuwait' ? 'selected' : '' }}>Kuwait</option>

    {{-- Europe --}}
    <option value="United Kingdom" data-flag="gb" {{ $savedCountry == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
    <option value="France" data-flag="fr" {{ $savedCountry == 'France' ? 'selected' : '' }}>France</option>
    <option value="Germany" data-flag="de" {{ $savedCountry == 'Germany' ? 'selected' : '' }}>Germany</option>
    <option value="Italy" data-flag="it" {{ $savedCountry == 'Italy' ? 'selected' : '' }}>Italy</option>
    <option value="Netherlands" data-flag="nl" {{ $savedCountry == 'Netherlands' ? 'selected' : '' }}>Netherlands</option>

    {{-- North America --}}
    <option value="United States" data-flag="us" {{ $savedCountry == 'United States' ? 'selected' : '' }}>United States</option>
    <option value="Canada" data-flag="ca" {{ $savedCountry == 'Canada' ? 'selected' : '' }}>Canada</option>

    {{-- Oceania --}}
    <option value="Australia" data-flag="au" {{ $savedCountry == 'Australia' ? 'selected' : '' }}>Australia</option>
    <option value="New Zealand" data-flag="nz" {{ $savedCountry == 'New Zealand' ? 'selected' : '' }}>New Zealand</option>

    {{-- Asia --}}
    <option value="India" data-flag="in" {{ $savedCountry == 'India' ? 'selected' : '' }}>India</option>
    <option value="Singapore" data-flag="sg" {{ $savedCountry == 'Singapore' ? 'selected' : '' }}>Singapore</option>
    <option value="Malaysia" data-flag="my" {{ $savedCountry == 'Malaysia' ? 'selected' : '' }}>Malaysia</option>
    <option value="Japan" data-flag="jp" {{ $savedCountry == 'Japan' ? 'selected' : '' }}>Japan</option>
    <option value="South Korea" data-flag="kr" {{ $savedCountry == 'South Korea' ? 'selected' : '' }}>South Korea</option>
    <option value="Maldives" data-flag="mv" {{ $savedCountry == 'Maldives' ? 'selected' : '' }}>Maldives</option>
</select>
    </div>

    <div class="mt-5">
        <label>Address Line 1</label>
        <input class="billing-field w-full px-4" name="address1" required
            value="{{ old('address1', Auth::user()->address1 ?? '') }}" placeholder="House No, Street Name">
    </div>

    <div class="mt-5">
        <label>Address Line 2 <span class="text-gray-400 font-normal">(Optional)</span></label>
        <input class="billing-field w-full px-4" name="address2"
            value="{{ old('address2', Auth::user()->address2 ?? '') }}" placeholder="Apartment, Suite, Unit, etc.">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-5">
        <div>
            <label>City</label>
            <input class="billing-field w-full px-4" name="city" required 
                value="{{ old('city', Auth::user()->city ?? '') }}" placeholder="City">
        </div>
        <div>
            <label>State</label>
            <input class="billing-field w-full px-4" name="state" required 
                value="{{ old('state', Auth::user()->state ?? '') }}" placeholder="State">
        </div>
        <div>
            <label>Zip Code</label>
            <input class="billing-field w-full px-4" name="zipcode" required 
                value="{{ old('zipcode', Auth::user()->zipcode ?? '') }}" placeholder="Zip Code">
        </div>
    </div>
</div>

                {{-- RIGHT : ORDER SUMMARY --}}

                <div class="checkout-card p-8 rounded-xl shadow-lg">
                    <h2 class="text-2xl font-bold text-[#800000] mb-6 border-b pb-4">
                        <i class="fas fa-receipt mr-2"></i> Order Summary
                    </h2>
                    @php

                        $productTotal = 0;
                        $currency = session('currency', 'LKR');
                        $rate = 0.0032;

                        foreach($cartItems as $item) {

                            $p = $item->variant ? $item->variant->price : $item->product->price;
                            $productTotal += ($p * $item->product_qty);

                        }

                        $initialDelivery = ($currency === 'USD') ? 5000 : 500;
                        $grandTotal = $productTotal + $initialDelivery;
                    @endphp

                    <div class="space-y-4 mb-6">
                        @foreach($cartItems as $item)
                            @php
                                $price = $item->variant ? $item->variant->price : $item->product->price;
                                $name = $item->product->name . ($item->variant ? ' (' . $item->variant->unit_label . ')' : '');
                                $itemTotal = $price * $item->product_qty;
                            @endphp
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 bg-gray-100 rounded-md overflow-hidden border">
                                        <img src="{{ asset('storage/'.$item->product->image) }}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800 text-sm">{{ $name }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ $item->product_qty }} x {{ $currency == 'USD' ? '$' . number_format($price * $rate, 2) : 'Rs ' . number_format($price, 2) }}
                                        </p>
                                    </div>
                                </div>
                                <span class="font-bold text-gray-700">
                                    {{ $currency == 'USD' ? '$' . number_format($itemTotal * $rate, 2) : 'Rs ' . number_format($itemTotal, 2) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                        <div class="flex justify-between text-gray-600">
                            <span>Sub Total</span>
                            <span class="font-semibold">{{ $currency == 'USD' ? '$' . number_format($productTotal * $rate, 2) : 'Rs ' . number_format($productTotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Delivery Charge</span>
                            <span class="font-semibold" id="delivery-fee-text">
                                {{ $currency == 'USD' ? '$' . number_format($initialDelivery * $rate, 2) : 'Rs ' . number_format($initialDelivery, 2) }}
                            </span>
                        </div>
                        <div class="border-t border-gray-300 pt-3 flex justify-between items-center">
                            <span class="text-lg font-bold text-gray-800">Grand Total</span>
                            <span class="text-xl font-extrabold text-[#800000]" id="grand-total-text">
                                {{ $currency == 'USD' ? '$' . number_format($grandTotal * $rate, 2) : 'Rs ' . number_format($grandTotal, 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 mb-3">
                        <label class="font-bold text-sm">Order Notes (Optional)</label>
                        <textarea name="message" class="custom-textarea-large" placeholder="Special instructions for delivery..."></textarea>
                    </div>

                    <div class="mt-6 space-y-4">
                        @if(session('currency', 'LKR') !== 'USD')
                            <label class="flex items-center border border-[#800000] bg-[#fff5f5] p-4 rounded-lg cursor-pointer">
                                <input type="radio" name="payment_mode" value="COD" checked class="w-5 h-5 accent-[#800000]">
                                <span class="ml-3 font-bold text-[#800000]">Cash on Delivery (COD)</span>
                            </label>

                        @endif

                        <label class="flex items-center border border-[#004080] bg-[#f0f7ff] p-4 rounded-lg cursor-pointer">
                            <input type="radio" name="payment_mode" value="Stripe" {{ session('currency') === 'USD' ? 'checked' : '' }} class="w-5 h-5 accent-[#004080]">
                            <span class="ml-3 font-bold text-[#004080]">Online Payment (Stripe)</span>
                        </label>
                    </div>



                    <button type="button" id="placeOrderBtn" class="w-full mt-6 bg-[#800000] text-white py-4 rounded-lg font-bold text-lg shadow-lg">
                        Confirm & Place Order
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/intlTelInput.min.js"></script>



<script>
$(document).ready(function () {

    const baseProductTotal = {{ $productTotal }};
    const rate = 0.0032;
    const currency = "{{ session('currency', 'LKR') }}";
    let currentDelivery = (currency === 'USD') ? 5000 : 500;

    // ✅ GLOBAL country map (FIXED)
    const countryMap = { 
        "Sri Lanka": "lk",
        "United Arab Emirates": "ae",
        "Saudi Arabia": "sa",
        "Qatar": "qa",
        "Oman": "om",
        "Kuwait": "kw",
        "United Kingdom": "gb",
        "France": "fr",
        "Germany": "de",
        "Italy": "it",
        "Netherlands": "nl",
        "United States": "us",
        "Canada": "ca",
        "Australia": "au",
        "New Zealand": "nz",
        "India": "in",
        "Singapore": "sg",
        "Malaysia": "my",
        "Japan": "jp",
        "South Korea": "kr",
        "Maldives": "mv"
    };

    // ✅ Phone input
    const phoneInput = window.intlTelInput(
        document.querySelector("#phoneInput"), {
            initialCountry: (currency === 'USD') ? "gb" : "lk",
            separateDialCode: true,
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/utils.js",
        }
    );

    // ✅ Select2 flag rendering
    function formatState(state) {
        if (!state.id) return state.text;
        const flag = $(state.element).data('flag');
        return $('<span><span class="fi fi-' + flag + '"></span> ' + state.text + '</span>');
    }

    $('#countrySelect').select2({
        templateResult: formatState,
        templateSelection: formatState,
        width: '100%'
    });

    // ✅ Country change handler
    $('#countrySelect').on('change', function () {

        const country = $(this).val();

        currentDelivery = (country === "Sri Lanka") ? 500 : 5000;
        const newGrandTotalLKR = baseProductTotal + currentDelivery;

        if (currency === 'USD') {
            $('#delivery-fee-text').text('$' + (currentDelivery * rate).toFixed(2));
            $('#grand-total-text').text('$' + (newGrandTotalLKR * rate).toFixed(2));
        } else {
            $('#delivery-fee-text').text('Rs ' + currentDelivery.toLocaleString());
            $('#grand-total-text').text('Rs ' + newGrandTotalLKR.toLocaleString());
        }

        // ✅ Auto switch phone flag
        if (countryMap[country]) {
            phoneInput.setCountry(countryMap[country]);
        }
    });

    // ✅ Confirm & Place Order button
    $('#placeOrderBtn').on('click', function () {

        const form = document.getElementById('checkoutForm');

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const totalLKR = baseProductTotal + currentDelivery;
        const displayTotal = (currency === 'USD')
            ? '$' + (totalLKR * rate).toFixed(2)
            : 'Rs ' + totalLKR.toLocaleString();

        Swal.fire({
            title: 'Place Order?',
            text: "Total Amount: " + displayTotal,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#800000',
            confirmButtonText: 'Confirm'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#fullPhone').val(phoneInput.getNumber());
                form.submit();
            }
        });
    });

});
</script>

@endsection