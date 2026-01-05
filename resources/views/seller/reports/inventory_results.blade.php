@extends('layouts.seller')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .report-card-flush {
        padding: 0 !important;
        margin-top: 20px;
        overflow: hidden !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 8px;
    }

    .report-table-full {
        width: 100% !important;
        border-collapse: collapse !important;
        margin: 0 !important;
        table-layout: fixed !important; 
    }

    .report-table-full th, .report-table-full td {
        padding: 12px 20px !important;
        vertical-align: middle;
    }

    .product-img-thumb {
        width: 45px;
        height: 45px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #eee;
    }

    .btn-pdf {
        background: #5b2c2c;
        color: #fff;
        font-weight: 600;
        border: none;
        transition: 0.3s;
    }
    .btn-pdf:hover { background: #4a2424; color: #fff; }
</style>

<div class="container px-4 mx-auto mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 style="font-size:32px; font-weight:800; color:#111827;">Inventory Report</h2>
            <p class="mb-0">
                Category: <span class="badge" style="background:#5b2c2c; color:#fff;">{{ ucwords(str_replace('_', ' ', $reportType)) }}</span>
            </p>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            {{-- Download PDF --}}
            <a href="{{ route('seller.reports.pdf', ['report_type' => $reportType]) }}" class="btn btn-sm btn-pdf shadow-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </a>

            {{-- Submit to Admin Form --}}
            <form id="submitReportForm" action="{{ route('seller.reports.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="report_type" value="{{ $reportType }}">
                <button type="button" id="submitBtn" class="btn btn-sm btn-success shadow-sm">
                    <i class="bi bi-send-check me-1"></i> Submit to Admin
                </button>
            </form>

            {{-- Back to Filters --}}
            <a href="{{ route('seller.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="bg-white shadow-sm report-card-flush">
        <table class="report-table-full">
            <thead style="background:#5b2c2c; color:#fff; text-transform:uppercase; font-size:11px; letter-spacing: 0.5px;">
                <tr>
                    <th style="width: 12%;">ID</th>
                    <th style="width: 43%;">Product Details</th>
                    <th style="width: 15%; text-align: center;">Stock</th>
                    <th style="width: 15%; text-align: center;">Sales</th>
                    <th style="width: 15%; text-align: right;">Price</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td style="font-size: 13px; color: #6b7280;">PRD-{{ str_pad($product->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" class="product-img-thumb me-3">
                            @else
                                <div class="product-img-thumb me-3 bg-light d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image"></i>
                                </div>
                            @endif
                            <a href="{{ url('product/' . $product->slug) }}" target="_blank" class="text-decoration-none fw-bold" style="color: #2563eb;">
                                {{ $product->name }}
                            </a>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <span class="px-2 py-1 rounded bg-light border fw-bold">{{ $product->stock }}</span>
                    </td>
                    <td style="text-align: center;">
                        @if($reportType == 'top_selling')
                            <span class="text-success fw-bold">{{ $product->total_sold ?? 0 }} Sold</span>
                        @else
                            <span class="text-muted small">Standard</span>
                        @endif
                    </td>
                    <td style="text-align: right; fw-bold">Rs. {{ number_format($product->price, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">No products found for this criteria.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    // 1. CONFIRMATION ALERT (SweetAlert2)
    document.getElementById('submitBtn').addEventListener('click', function() {
        Swal.fire({
            title: 'Submit to Admin?',
            text: "This will send the current inventory data to TradLanka Admin.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754', // Green
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Submit Now',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('submitReportForm').submit();
            }
        });
    });

    // 2. SUCCESS ALERT (Triggered after page reload)
    @if(session('success'))
        Swal.fire({
            title: 'Success!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonColor: '#5b2c2c', // Maroon Brand Color
            timer: 4000
        });
    @endif
</script>
@endsection