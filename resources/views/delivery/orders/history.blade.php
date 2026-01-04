@extends('layouts.delivery')

@section('content')

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

    /* ✅ UPDATED: 
       Removed the 'border-left' blue line. 
       Kept only the subtle background for scannability.
    */
    .international-highlight {
        background-color: #f0f7ff !important; 
    }

    .tracking-no {
        color: #0d6efd;
        font-weight: 700;
    }

    .status-badge {
        font-size: 0.75rem;
        padding: 8px 12px;
        font-weight: 600;
        border-radius: 8px;
    }
</style>

<div class="container-fluid px-4 py-5">
    {{-- HEADER WITH DOWNLOAD BUTTON --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark">Task History</h2>
            <p class="text-muted">Review your completed and failed delivery attempts.</p>
        </div>
        {{--  DOWNLOAD BUTTON --}}
        <a href="{{ route('delivery.report.download') }}" class="btn btn-primary d-flex align-items-center shadow-sm px-4 py-2" style="border-radius: 10px;">
            <i class="bi bi-file-earmark-pdf-fill me-2"></i> Download Performance Report
        </a>
    </div>

    {{-- SEARCH BAR --}}
    <div class="row mb-4">
        <div class="col-md-6 col-lg-5">
            <form action="{{ route('delivery.task-history') }}" method="GET">
                <div class="input-group shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <input type="text" name="search" class="form-control border-0 py-2 ps-3" 
                           placeholder="Search by ID, Name, or City..." 
                           value="{{ request('search') }}"
                           style="background: #f8f9fa; font-size: 0.95rem;">
                    
                    <button class="btn px-4" type="submit" style="background-color: #5b2c2c; color: white; border: none;">
                        <i class="bi bi-search"></i>
                    </button>
                    
                    @if(request('search'))
                        <a href="{{ route('delivery.task-history') }}" class="btn btn-light d-flex align-items-center border-start">
                            <i class="bi bi-x-lg text-danger"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- HISTORY TABLE --}}
    <div class="card delivery-table-card">
        <div class="table-responsive">
            <table class="table align-middle custom-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Order Info</th>
                        <th>Customer</th>
                        <th>Final Status</th>
                        <th>Payment Value</th>
                        <th class="text-center pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                       @php
                            //  FIX: Defining $total to prevent "Undefined variable" error
                            $total = $order->total_price;
                            $payMode = strtoupper($order->payment_mode);
                            $dbCurrency = strtoupper(trim($order->currency));

                            // Accurate Currency Logic
                            $isUSD = (str_contains($payMode, 'USD') || $dbCurrency === 'USD');
                            $symbol = $isUSD ? '$ ' : 'Rs. ';
                        @endphp

                        {{--  Removed the blue line class logic from the <tr> --}}
                        <tr class="border-bottom {{ $isUSD ? 'international-highlight' : '' }}">
                            <td class="ps-4 py-4">
                                <span class="tracking-no">#{{ $order->tracking_no }}</span>
                                {{--  Global Sign remains as requested --}}
                                @if($isUSD)
                                    <i class="bi bi-globe-americas text-primary ms-1" title="International Order"></i>
                                @endif
                                <div class="small text-muted mt-1">
                                    Done: {{ $order->updated_at->format('d M, Y') }}
                                </div>
                            </td>

                            <td>
                                <div class="fw-bold text-dark">{{ $order->fname }} {{ $order->lname }}</div>
                                <div class="small text-muted">{{ $order->city }}</div>
                            </td>

                            <td>
                                        @if($order->status == 5)
                                            <span class="badge bg-success bg-opacity-10 text-success status-badge">
                                                <i class="bi bi-check-circle me-1"></i> DELIVERED
                                            </span>
                                        @elseif($order->status == 8) {{-- ✅ NEW: Pending Approval State --}}
                                            <span class="badge bg-warning bg-opacity-10 text-warning status-badge">
                                                <i class="bi bi-hourglass-split me-1"></i> PENDING APPROVAL
                                            </span>
                                            <div class="small text-muted mt-1" style="font-size: 0.7rem;">Waiting for Admin review</div>
                                        @elseif($order->status == 6 || $order->status == 9)
                                            <span class="badge bg-danger bg-opacity-10 text-danger status-badge">
                                                <i class="bi bi-x-circle me-1"></i> FAILED
                                            </span>
                                            @if($order->cancel_reason)
                                                <div class="small text-danger mt-1 fw-bold" style="font-size: 0.7rem;">
                                                    Reason: {{ \Illuminate\Support\Str::limit($order->cancel_reason, 30) }}
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                            <td>
                                {{--  Corrected $total display --}}
                                <div class="fw-bold text-dark">{{ $symbol }}{{ number_format($total, 2) }}</div>
                                <span class="small text-muted text-uppercase" style="font-size: 0.7rem;">{{ $order->payment_mode }}</span>
                            </td>

                            <td class="pe-4 text-center">
                                <a href="{{ route('delivery.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-eye"></i> Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-5 text-muted">
                                <i class="bi bi-clock-history display-4 opacity-25"></i>
                                <p class="mt-3">No historical tasks found matching your criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($orders->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection