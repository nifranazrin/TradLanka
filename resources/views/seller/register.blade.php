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
</style>

<div class="container py-5">
    <div class="register-card">
        <img src="{{ asset('storage/images/tradlanka-logo.jpg') }}" alt="TradLanka Logo" class="register-logo">
        <h4 class="register-title mb-2">Join as a Seller</h4>
        <p class="text-muted mb-4">Submit your details for admin approval</p>

        {{-- ✅ START: VALIDATION ERROR BLOCK --}}
        @if ($errors->any())
            <div class="alert alert-danger text-start" role="alert">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {{-- ✅ END: VALIDATION ERROR BLOCK --}}

        {{-- ✅ Registration Form --}}
        <form id="sellerForm" action="{{ route('seller.register.submit') }}" method="POST" enctype="multipart/form-data">
        @csrf

            <div class="mb-3 text-start">
                <label class="form-label fw-bold">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter your full name" required value="{{ old('name') }}">
            </div>

            <div class="mb-3 text-start">
                <label class="form-label fw-bold">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required value="{{ old('email') }}">
            </div>

            <div class="mb-3 text-start">
                <label class="form-label fw-bold">Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="e.g. 07********" required value="{{ old('phone') }}">
            </div>

            <div class="mb-3 text-start">
                <label class="form-label fw-bold">NIC Number</label>
                <input type="text" name="nic_number" class="form-control" placeholder="Enter NIC number" required value="{{ old('nic_number') }}">
            </div>

            <div class="mb-3 text-start">
                <label class="form-label fw-bold">Preferred Name</label>
                <input type="text" name="preferred_name" class="form-control" placeholder="Preferred Name" required value="{{ old('preferred_name') }}">
            </div>

            <div class="mb-3 text-start">
                <label class="form-label fw-bold">Address</label>
                <textarea name="address" class="form-control" rows="2" placeholder="Enter your address">{{ old('address') }}</textarea>
            </div>

            <div class="mb-4 text-start">
                <label class="form-label fw-bold">Upload NIC Image</label>
                <input type="file" name="nic_image" class="form-control" accept="image/*" required>
            </div>

            {{-- ✅ Button triggers SweetAlert review --}}
            <button type="button" id="reviewBtn" class="btn btn-register w-100">Review & Submit</button>

            <p class="note mt-3">Your request will be reviewed by our admin before approval.</p>
        </form>
    </div>
</div>

{{-- ✅ SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById('reviewBtn').addEventListener('click', function (e) {
    e.preventDefault(); // stop accidental GET reload
    const form = document.getElementById('sellerForm');
    const formData = new FormData(form);

    // Basic client-side check if fields are filled (improves user experience)
    let missingField = false;
    const requiredFields = ['name', 'email', 'phone', 'nic_number', 'preferred_name', 'nic_image'];
    
    for (const fieldName of requiredFields) {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (field.type === 'file' && field.files.length === 0) {
            missingField = true;
        } else if (field.type !== 'file' && !field.value) {
            missingField = true;
        }
    }

    if (missingField) {
        Swal.fire({
            title: 'Missing Fields',
            text: 'Please fill in all required fields, including the NIC image.',
            icon: 'warning',
            confirmButtonColor: '#a81c1c',
        });
        return; // Stop before showing review
    }

    const summary = `
        <strong>Name:</strong> ${formData.get('name')}<br>
        <strong>Email:</strong> ${formData.get('email')}<br>
        <strong>Phone:</strong> ${formData.get('phone')}<br>
        <strong>NIC Number:</strong> ${formData.get('nic_number')}<br>
        <strong>Preferred Name:</strong> ${formData.get('preferred_name')}<br>
        <strong>Address:</strong> ${formData.get('address') || 'N/A'}
    `;

    Swal.fire({
        title: 'Review Your Details',
        html: summary,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Submit',
        cancelButtonText: 'Edit',
        confirmButtonColor: '#a81c1c',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // ✅ Force POST submit correctly
            form.method = 'POST';
            form.action = "{{ route('seller.register.submit') }}";
            form.submit();
        }
    });
});
</script>

@endsection