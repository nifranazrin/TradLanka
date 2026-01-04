@extends('layouts.delivery')

@section('content')

{{-- SweetAlert2 Library --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .delivery-table-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: none;
    }

    .custom-table thead th {
        background-color: #5b2c2c; 
        color: #ffffff;
        padding: 18px;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none;
    }

    .tracking-no {
        color: #0d6efd;
        font-weight: 700;
    }

    .btn-action {
        font-weight: 600;
        border-radius: 8px;
        padding: 6px 12px;
    }

    /* SweetAlert Custom Styling */
    .maroon-swal-popup {
        background-color: #f1f0de !important; 
        border-radius: 15px !important;
        border: 2px solid #570a0a !important;
    }
    .maroon-swal-title, .maroon-swal-content {
        color: #350b05 !important; 
    }
    .maroon-swal-confirm {
        background-color: #198754 !important; 
        color: white !important;
        margin: 10px;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: bold;
        border: none;
    }
    .maroon-swal-cancel {
        background-color: #6c757d !important;
        color: white !important;
        margin: 10px;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: bold;
        border: none;
    }
</style>

<div class="container-fluid px-4 py-5">
    <div class="mb-4">
        <h2 class="h3 fw-bold text-dark">Active Task</h2>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 col-lg-5">
            <form action="{{ route('delivery.my-deliveries') }}" method="GET">
                <div class="input-group shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <input type="text" name="search" class="form-control border-0 py-2 ps-3" 
                           placeholder="Search by ID, Name, or City..." 
                           value="{{ request('search') }}"
                           style="background: #f8f9fa; font-size: 0.95rem;">
                    
                    <button class="btn px-4" type="submit" style="background-color: #5b2c2c; color: white; border: none;">
                        <i class="bi bi-search"></i> Search
                    </button>
                    
                    @if(request('search'))
                        <a href="{{ route('delivery.my-deliveries') }}" class="btn btn-light d-flex align-items-center border-start">
                            <i class="bi bi-x-lg text-danger"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card delivery-table-card">
        <div class="table-responsive">
            <table class="table align-middle custom-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Order Info</th>
                        <th>Customer & Contact</th>
                        <th>Delivery Address</th>
                        <th>Payment</th>
                        <th class="text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $dbCurrency = strtoupper(trim($order->currency));
                            $payMode = strtoupper($order->payment_mode);
                            $isUSD = ($dbCurrency === 'USD' || str_contains($payMode, '(USD)'));
                            $symbol = $isUSD ? '$ ' : 'Rs. ';
                        @endphp
                        <tr>
                            <td class="ps-4 py-4">
                                <span class="tracking-no">#{{ $order->tracking_no }}</span>
                                @if($isUSD)
                                    <i class="bi bi-globe-americas text-primary ms-1" title="International"></i>
                                @endif
                                <div class="small text-muted mt-1">
                                    {{ $order->created_at->format('d M, Y') }}
                                </div>
                                
                                @if($order->status == 4)
                                    <span class="badge bg-info text-dark mt-1" style="font-size: 0.7rem;">OUT FOR DELIVERY</span>
                                @elseif($order->status == 10)
                                    <span class="badge bg-primary text-white mt-1" style="font-size: 0.7rem;">ARRIVED AT DESTINATION</span>
                                @elseif($order->status == 8 || $order->status == 9)
                                    <span class="badge bg-warning text-dark mt-1" style="font-size: 0.7rem;">REPORTED FAILED</span>
                                @endif
                            </td>

                            <td>
                                <div class="fw-bold text-dark">{{ $order->fname }} {{ $order->lname }}</div>
                                <div class="mt-1">
                                    <i class="bi bi-phone small text-muted"></i>
                                    <strong class="text-dark small">{{ $order->phone }}</strong>
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <a href="tel:{{ $order->phone }}" class="btn btn-sm btn-outline-primary rounded-circle shadow-sm">
                                        <i class="bi bi-telephone-fill"></i>
                                    </a>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->phone) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-circle shadow-sm">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                </div>
                            </td>

                            <td>
                                <div class="small text-secondary" style="max-width: 220px; line-height: 1.2;">
                                    {{ $order->address1 }}<br>
                                    @if($order->address2) <span class="text-muted">{{ $order->address2 }}</span><br> @endif
                                    <strong>{{ $order->city }}</strong>
                                </div>
                            </td>

                            <td>
                                <div class="fw-bold text-dark">{{ $symbol }}{{ number_format($order->total_price, 2) }}</div>
                                @if(str_contains(strtolower($order->payment_mode), 'cod'))
                                    <span class="badge bg-warning text-dark small">COLLECT CASH (COD)</span>
                                @else
                                    <span class="badge bg-success text-white small">PAID ONLINE</span>
                                @endif
                            </td>

                            <td class="pe-4 text-center">
                                <div class="d-flex flex-column gap-2">
                                    <a href="{{ route('delivery.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary btn-action">
                                        <i class="bi bi-eye"></i> View Items
                                    </a>
                                    
                                    @if($isUSD)
                                        {{-- ✅ INTERNATIONAL STRIPE WORKFLOW --}}
                                        @if($order->status == 4)
                                            <button type="button" class="btn btn-sm btn-primary btn-action" onclick="updateMilestone({{ $order->id }}, 10, 'Arrived at Destination Country')">
                                                <i class="bi bi-airplane me-1"></i> Arrived in Country
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger btn-action" onclick="confirmAction({{ $order->id }}, 'failed')">
                                                Not Received
                                            </button>
                                        @elseif($order->status == 10)
                                            <button type="button" class="btn btn-sm btn-success btn-action" onclick="confirmAction({{ $order->id }}, 'delivered')">
                                                <i class="bi bi-check-circle me-1"></i> Final Delivery
                                            </button>
                                        @endif
                                    @else
                                        {{-- ✅ LOCAL COD WORKFLOW --}}
                                        @if($order->status == 4)
                                            <button type="button" class="btn btn-sm btn-success btn-action" onclick="confirmAction({{ $order->id }}, 'delivered')">
                                                Complete
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger btn-action" onclick="confirmAction({{ $order->id }}, 'failed')">
                                                Not Received
                                            </button>
                                        @endif
                                    @endif

                                    @if($order->status == 8 || $order->status == 9)
                                        <button class="btn btn-sm btn-warning btn-action text-dark" disabled>
                                            <i class="bi bi-hourglass-split"></i> Awaiting Admin
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-5 text-muted">
                                <i class="bi bi-truck-flatbed display-4 opacity-25"></i>
                                <p class="mt-3">No active orders found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    
    // ✅ Corrected: Removed the extra '/delivery' from the URL path
function updateMilestone(orderId, nextStatus, statusName) {
    Swal.fire({
        title: 'Update Milestone?',
        text: `Confirm shipment has: "${statusName}"?`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Yes, Update',
        customClass: {
            popup: 'maroon-swal-popup',
            confirmButton: 'maroon-swal-confirm',
            cancelButton: 'maroon-swal-cancel'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            
            submitForm(`/delivery/update-milestone/${orderId}`, 'PUT', { status: nextStatus });
        }
    });
}
    // ✅ Handle Final Actions (Delivered / Failed)
    function confirmAction(orderId, type) {
        let config = type === 'delivered' 
            ? { title: 'Confirm Delivery?', text: 'Mark as successfully delivered?', icon: 'question', btn: 'Yes, Delivered!' }
            : { title: 'Report Delivery Failure?', text: 'This will be sent to Admin for final review. Provide a reason:', icon: 'warning', btn: 'Report to Admin' };

        Swal.fire({
            title: config.title,
            text: config.text,
            input: type === 'failed' ? 'text' : null,
            inputPlaceholder: 'e.g. Customer not at home...',
            showCancelButton: true,
            confirmButtonText: config.btn,
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'maroon-swal-popup',
                title: 'maroon-swal-title',
                confirmButton: 'maroon-swal-confirm',
                cancelButton: 'maroon-swal-cancel'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                let url = type === 'delivered' ? `/delivery/mark-delivered/${orderId}` : `/delivery/mark-failed/${orderId}`;
                let data = type === 'failed' ? { reason: result.value || 'No reason provided' } : {};
                submitForm(url, 'PUT', data);
            }
        });
    }

    // Helper to submit forms dynamically
    function submitForm(action, method, data) {
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        
        let csrf = document.createElement('input');
        csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        let methodInput = document.createElement('input');
        methodInput.type = 'hidden'; methodInput.name = '_method'; methodInput.value = method;
        form.appendChild(methodInput);
        
        for (let key in data) {
            let input = document.createElement('input');
            input.type = 'hidden'; input.name = key; input.value = data[key];
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }
</script>
@endsection