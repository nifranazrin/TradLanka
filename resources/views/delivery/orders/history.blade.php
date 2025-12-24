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

    /* Highlight for International/USD rows to stay consistent with Dashboard */
    .international-highlight {
        background-color: #f0f7ff !important; 
        border-left: 5px solid #0d6efd !important;
    }

    .tracking-no {
        color: #0d6efd;
        font-weight: 700;
    }
</style>

<div class="container-fluid px-4 py-5">
    <div class="mb-4">
        <h2 class="h3 fw-bold text-dark">Task History</h2>
        <p class="text-muted">Review your completed and failed delivery attempts.</p>
    </div>

    <div class="card delivery-table-card">
        <div class="table-responsive">
            <table class="table align-middle custom-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Order Info</th>
                        <th>Customer</th>
                        <th>Final Status</th>
                        <th>Payment Value</th>
                        <th class="text-center pe-4">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            /** ✅ SAFETY CURRENCY LOGIC 
                             * Identifies USD based on small values or payment strings
                             */
                            $dbCurrency = strtoupper(trim($order->currency));
                            $payMode = strtoupper($order->payment_mode);
                            $total = $order->total_price;

                            if ($dbCurrency === 'USD' || str_contains($payMode, '(USD)') || ($total < 500 && !str_contains($payMode, 'COD'))) {
                                $symbol = '$ ';
                                $isUSD = true;
                            } else {
                                $symbol = 'Rs. ';
                                $isUSD = false;
                            }
                        @endphp

                        <tr class="border-bottom {{ $isUSD ? 'international-highlight' : '' }}">
                            <td class="ps-4 py-4">
                                <span class="tracking-no">#{{ $order->tracking_no }}</span>
                                @if($isUSD)
                                    <i class="bi bi-globe-americas text-primary ms-1"></i>
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
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="bi bi-check-circle me-1"></i> DELIVERED
                                    </span>
                                @elseif($order->status == 6)
                                    <span class="badge bg-danger px-3 py-2" title="Reason: {{ $order->cancel_reason }}">
                                        <i class="bi bi-x-circle me-1"></i> FAILED
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="fw-bold text-dark">{{ $symbol }}{{ number_format($total, 2) }}</div>
                                <span class="small text-muted text-uppercase">{{ $order->payment_mode }}</span>
                            </td>

                            <td class="pe-4 text-center">
                                <a href="{{ route('delivery.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary px-3">
                                    <i class="bi bi-eye me-1"></i> View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-5 text-muted">
                                <i class="bi bi-clock-history display-4 opacity-25"></i>
                                <p class="mt-3">Your task history is currently empty.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination Links --}}
        @if($orders->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection