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
    {{-- HEADER WITH DYNAMIC DOWNLOAD BUTTON --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Task History</h2>
            <p class="text-muted mb-0">Review your completed and failed delivery attempts.</p>
        </div>

        {{-- ✅ Updated: The download link now includes current filters via request()->query() --}}
        <a href="{{ route('delivery.report.download', request()->query()) }}" 
           class="btn btn-primary d-flex align-items-center shadow-sm px-4 py-2" 
           style="border-radius: 10px;">
            <i class="bi bi-file-earmark-pdf-fill me-2"></i> Download Filtered Report
        </a>
    </div>

    {{-- ADVANCED FILTER BAR --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
        <div class="card-body p-3">
            <form action="{{ route('delivery.task-history') }}" method="GET" class="row g-3">
                
                {{-- 1. Search Box --}}
                <div class="col-12 col-lg-4">
                    <div class="input-group" style="border-radius: 10px; overflow: hidden; border: 1px solid #dee2e6;">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-0 py-2" 
                               placeholder="ID, Name, or City..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- 2. Status Filter --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="status" class="form-select border-light-subtle py-2" style="border-radius: 10px;">
                        <option value="all">All Status</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed / Pending</option>
                    </select>
                </div>

                {{-- 3. Currency Filter --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="currency" class="form-select border-light-subtle py-2" style="border-radius: 10px;">
                        <option value="all">All Currencies</option>
                        <option value="LKR" {{ request('currency') == 'LKR' ? 'selected' : '' }}>LKR (Local)</option>
                        <option value="USD" {{ request('currency') == 'USD' ? 'selected' : '' }}>USD (International)</option>
                    </select>
                </div>

                {{-- 4. Country Filter --}}
                <div class="col-8 col-md-4 col-lg-2">
                    <input type="text" name="country" class="form-control border-light-subtle py-2" 
                           placeholder="Country..." value="{{ request('country') }}" style="border-radius: 10px;">
                </div>

                {{-- 5. Action Buttons --}}
                <div class="col-4 col-md-2 col-lg-2 d-flex gap-2">
                    <button class="btn w-100 fw-bold" type="submit" style="background-color: #5b2c2c; color: white; border-radius: 10px;">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'currency', 'country']))
                        <a href="{{ route('delivery.task-history') }}" class="btn btn-light border" style="border-radius: 10px;">
                            <i class="bi bi-arrow-counterclockwise"></i>
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