@extends('layouts.app')

@section('content')
<style>
    body {
        background: url('{{ asset('storage/images/background.jpg') }}') no-repeat center center fixed;
        background-size: cover;
        font-family: 'Poppins', sans-serif;
    }

    .register-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        padding: 40px;
        max-width: 500px;
        margin: 60px auto;
        text-align: center;
    }

    .register-logo {
        width: 70px;
        height: 70px;
        margin-bottom: 15px;
        border-radius: 12px;
    }

    .register-title {
        font-weight: 700;
        font-size: 1.5rem;
        color: #a81c1c;
    }

    .btn-register {
        background: linear-gradient(90deg, #a81c1c, #f46b45);
        border: none;
        color: #fff;
        border-radius: 10px;
        padding: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-register:hover {
        background: linear-gradient(90deg, #f46b45, #a81c1c);
        transform: translateY(-1px);
    }

    .note {
        color: #888;
        font-size: 0.9rem;
    }

    .input-group-text {
        background-color: #f8f9fa;
        font-weight: bold;
        border-radius: 10px 0 0 10px;
    }
    .phone-input {
        border-radius: 0 10px 10px 0 !important;
    }
</style>

<div class="container py-5">
    <div class="register-card">
        <img src="{{ asset('storage/images/tradlanka-logo.jpg') }}" alt="TradLanka Logo" class="register-logo">
        <h4 class="register-title mb-2">Join Our Team</h4>
        <p class="text-muted mb-4">Submit your details for admin approval</p>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger text-start" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="staffForm" action="{{ route('seller.register.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Full Name --}}
            <div class="mb-3 text-start">
                <label class="form-label fw-bold">Full Name:</label>
                <input type="text" name="name" class="form-control" placeholder="Enter your full name" required value="{{ old('name') }}">
            </div>

            {{-- Email --}}
            <div class="mb-3 text-start">
                <label class="form-label fw-bold">Email:</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required value="{{ old('email') }}">
            </div>

            {{-- Phone Number with +94 Prefix --}}
            <div class="mb-3 text-start">
                <label class="form-label fw-bold">Phone Number:</label>
                <div class="input-group">
                    <span class="input-group-text">+94</span>
                    <input type="text" name="phone" class="form-control phone-input" placeholder="771234567" maxlength="9" required value="{{ old('phone') }}">
                </div>
                <small class="text-muted">Enter the 9 digits after +94</small>
            </div>

            {{-- NIC Number --}}
            <div class="mb-3 text-start">
                <label class="form-label fw-bold">NIC Number:</label>
                <input type="text" name="nic_number" class="form-control" placeholder="Enter NIC number" required value="{{ old('nic_number') }}">
            </div>

            {{-- Preferred Name (Previously Missing) --}}
            <div class="mb-3 text-start">
                <label class="form-label fw-bold">Preferred Name:</label>
                <input type="text" name="preferred_name" class="form-control" placeholder="How should we call you?" required value="{{ old('preferred_name') }}">
            </div>

            {{-- Position --}}
            <div class="mb-3 text-start">
                <label class="form-label fw-bold">Position you are applying for:</label>
                <select name="role" class="form-select" required>
                    <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select position</option>
                    <option value="seller" {{ old('role') == 'seller' ? 'selected' : '' }}>Seller</option>
                    <option value="delivery" {{ old('role') == 'delivery' ? 'selected' : '' }}>Delivery Person</option>
                </select>
            </div>

            {{-- Address (Previously Missing) --}}
            <div class="mb-3 text-start">
                <label class="form-label fw-bold">Address:</label>
                <textarea name="address" class="form-control" rows="2" placeholder="Enter your full address" required>{{ old('address') }}</textarea>
            </div>

            {{-- NIC Image --}}
            <div class="mb-4 text-start">
                <label class="form-label fw-bold">Upload NIC Image:</label>
                <input type="file" name="nic_image" class="form-control" accept="image/*" required>
            </div>

            <button type="button" id="reviewBtn" class="btn btn-register w-100">Review & Submit</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// 1. THANK YOU MESSAGE AFTER REDIRECT
@if(session('success'))
    Swal.fire({
        title: 'Thank You!',
        text: "{{ session('success') }}",
        icon: 'success',
        confirmButtonColor: '#a81c1c'
    });
@endif

// 2. REVIEW LOGIC
document.getElementById('reviewBtn').addEventListener('click', function () {
    const form = document.getElementById('staffForm');
    const formData = new FormData(form);
    
    // Check HTML5 validation first
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Capture the summary with all fields including Address and Preferred Name
    const summary = `
        <div style="text-align:left; font-size:14px; line-height: 1.6;">
            <p><strong>Name:</strong> ${formData.get('name')}</p>
            <p><strong>Preferred Name:</strong> ${formData.get('preferred_name')}</p>
            <p><strong>Email:</strong> ${formData.get('email')}</p>
            <p><strong>Phone:</strong> +94 ${formData.get('phone')}</p>
            <p><strong>NIC:</strong> ${formData.get('nic_number')}</p>
            <p><strong>Position:</strong> ${formData.get('role').toUpperCase()}</p>
            <p><strong>Address:</strong> ${formData.get('address')}</p>
            <hr>
            <p class="text-danger small">Please ensure your phone and email are correct so you can receive your login credentials.</p>
        </div>
    `;

    Swal.fire({
        title: 'Clarify Your Details',
        html: summary,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Submit Application',
        cancelButtonText: 'Edit',
        confirmButtonColor: '#a81c1c',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>
@endsection