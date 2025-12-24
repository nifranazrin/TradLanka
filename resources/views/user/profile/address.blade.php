@extends('layouts.frontend')

@section('content')

{{-- ✅ CUSTOM CSS FOR SWEETALERT --}}
<style>
    .swal-modal {
        background-color: #33100e !important; 
        border: 3px solid #dfd04c; 
    }
    .swal-title {
        color: #d1ab5a !important; 
    }
    .swal-text {
        color: #f1de89 !important; 
        font-weight: 600;
        text-align: center;
    }
    .swal-footer {
        text-align: center !important;
        margin-top: 20px !important;
    }
    .swal-button {
        background-color: #eef088 !important;
        color: rgb(78, 27, 27) !important;
        border: none;
        box-shadow: none !important;
        padding: 10px 35px;
        font-weight: bold;
        border-radius: 5px;
    }
    .swal-button:hover {
        background-color: #e9b669 !important;
    }
    .swal-button:focus {
        box-shadow: none !important;
    }
    /* Fix for icon lines */
    .swal-icon--success:before,
    .swal-icon--success:after,
    .swal-icon--success__hide,
    .swal-icon--success__fix, 
    .swal-icon--success__hide-corners {
        background-color: transparent !important;
    }
</style>

{{-- ✅ MAIN CONTAINER WITH BACKGROUND IMAGE --}}
<div class="py-10 min-h-screen"
     style="background: url('{{ asset('storage/images/background.jpg') }}') no-repeat center center fixed; background-size: cover;">
     
    <div class="max-w-3xl mx-auto px-4">

        {{-- ✅ BREADCRUMB NAVIGATION --}}
        <nav class="flex text-sm font-bold text-[#5b2c2c] mb-6 bg-white/80 backdrop-blur-sm p-3 rounded-lg w-fit shadow-sm">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="{{ route('user.profile.index') }}" class="hover:underline hover:text-[#8a4a4a]">My Profile</a>
                    <span class="mx-2 text-gray-500">/</span>
                </li>
                <li class="flex items-center text-gray-600">
                    Edit Address
                </li>
            </ol>
        </nav>

        {{-- Header Title --}}
        <h2 class="text-4xl font-extrabold text-[#5b2c2c] mb-8 drop-shadow-sm">
            Address Book
        </h2>

        {{-- EDIT FORM CARD --}}
        <form method="POST"
              action="{{ route('user.profile.address.update') }}"
              class="bg-white/95 backdrop-blur-sm p-8 rounded-lg shadow-lg space-y-6 border-t-4 border-[#5b2c2c]">

            @csrf
            @method('PUT')

            {{-- ADDRESS --}}
            <div>
                <label class="block text-base font-bold text-gray-700 mb-2">
                    Address
                </label>
                <textarea name="address1"
                          rows="3"
                          class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:border-[#5b2c2c] focus:ring-1 focus:ring-[#5b2c2c] outline-none transition text-gray-800 font-medium">{{ old('address1', $user->address1) }}</textarea>
                @error('address1')
                    <p class="text-sm text-red-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- CITY --}}
            <div>
                <label class="block text-base font-bold text-gray-700 mb-2">
                    City
                </label>
                <input type="text"
                       name="city"
                       value="{{ old('city', $user->city) }}"
                       class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:border-[#5b2c2c] focus:ring-1 focus:ring-[#5b2c2c] outline-none transition text-gray-800 font-medium">
                @error('city')
                    <p class="text-sm text-red-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- PHONE --}}
            <div>
                <label class="block text-base font-bold text-gray-700 mb-2">
                    Phone Number
                </label>
                <input type="text"
                       name="phone"
                       value="{{ old('phone', $user->phone) }}"
                       class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:border-[#5b2c2c] focus:ring-1 focus:ring-[#5b2c2c] outline-none transition text-gray-800 font-medium">
                @error('phone')
                    <p class="text-sm text-red-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- BUTTONS SECTION --}}
            <div class="pt-6 flex items-center gap-4">
                {{-- Save Button --}}
                <button type="submit"
                        class="bg-[#5b2c2c] text-white px-8 py-3 rounded-lg font-bold hover:bg-[#4a2424] transition shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    Save Address
                </button>

                {{-- ✅ Cancel Button (Styled) --}}
                <a href="{{ route('user.profile.index') }}"
                   class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-bold hover:bg-gray-300 transition shadow-sm hover:shadow-md transform hover:-translate-y-0.5 text-center">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>

{{-- ✅ SWEETALERT SCRIPT --}}
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if (session('success'))
            swal({
                title: "Success!",
                text: "{{ session('success') }}",
                icon: "success",
                button: "OK",
            });
        @endif
    });
</script>

@endsection