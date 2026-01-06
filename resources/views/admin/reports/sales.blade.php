@extends('layouts.admin')

@section('content')
<style>
    .stat-card { border: none; border-radius: 15px; color: white; min-height: 180px; transition: 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.1); position: relative; overflow: hidden; }
    .stat-card:hover { transform: translateY(-5px); }
    .bg-lkr { background: linear-gradient(135deg, #2dce89 0%, #2dcecc 100%); }
    .bg-usd { background: linear-gradient(135deg, #11cdef 0%, #1171ef 100%); }
    .bg-payments { background: linear-gradient(135deg, #8965e0 0%, #bc65e0 100%); }
    .bg-failed { background: linear-gradient(135deg, #f5365c 0%, #f56036 100%); }
    .card-icon { position: absolute; right: -10px; bottom: -10px; font-size: 5rem; opacity: 0.2; transform: rotate(-15deg); }
    .divider-white { border-top: 1px solid rgba(255,255,255,0.3); margin: 12px 0; }
    .count-badge { position: absolute; top: 15px; right: 15px; background: white; color: #333; padding: 2px 10px; border-radius: 8px; font-weight: bold; font-size: 11px; z-index: 5; }
</style>

<div class="container-fluid px-4 py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
    
        <h2 class="h3 fw-bold text-dark mb-1">Platform Sales Analysis</h2>
        
        <div class="d-flex gap-3 align-items-center">
            <a href="{{ route('admin.reports.sales.pdf', ['filter_date' => request('filter_date')]) }}" class="btn btn-danger shadow-sm d-flex align-items-center px-3">
                <i class="bi bi-file-earmark-pdf me-2"></i> Download PDF
            </a>

            <form action="{{ route('admin.reports.sales') }}" method="GET" id="filterForm">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-event text-primary"></i></span>
                    <input type="date" name="filter_date" class="form-control border-start-0" 
                           value="{{ request('filter_date') }}" onchange="handleDateChange(this)">
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="card stat-card p-4 bg-lkr">
                <i class="bi bi-currency-exchange card-icon"></i>
                <div class="count-badge shadow-sm">{{ $successLKRCount }} Total</div>
                <small class="fw-bold opacity-75">SUCCESSFUL REVENUE (LKR)</small>
                <h4 class="fw-bold mb-0">Rs. {{ number_format($successLKR, 2) }}</h4>
                <div class="divider-white"></div>
                <small class="fw-bold opacity-75">PENDING VALUE</small>
                <h5 class="fw-bold mb-0">Rs. {{ number_format($pendingLKR, 2) }}</h5>
                <small>{{ $pendingLKRCount }} Local Orders Awaiting</small>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card stat-card p-4 bg-usd">
                <i class="bi bi-globe card-icon"></i>
                <div class="count-badge shadow-sm">{{ $successUSDCount }} Total</div>
                <small class="fw-bold opacity-75">SUCCESSFUL REVENUE (USD)</small>
                <h4 class="fw-bold mb-0">$ {{ number_format($successUSD, 2) }}</h4>
                <div class="divider-white"></div>
                <small class="fw-bold opacity-75">PENDING VALUE</small>
                <h5 class="fw-bold mb-0">$ {{ number_format($pendingUSD, 2) }}</h5>
                <small>{{ $pendingUSDCount }} Intl. Orders Awaiting</small>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card stat-card p-4 bg-payments">
                <i class="bi bi-credit-card card-icon"></i>
                <small class="fw-bold opacity-75 text-uppercase">COD Orders</small>
                <h4 class="fw-bold mb-0">{{ $totalCOD }} Active</h4>
                <div class="divider-white"></div>
                <small class="fw-bold opacity-75 text-uppercase">Stripe Orders</small>
                <h4 class="fw-bold mb-0">{{ $totalStripe }} Active</h4>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card stat-card p-4 bg-failed">
                <i class="bi bi-shield-x card-icon"></i>
                <small class="fw-bold opacity-75 text-uppercase">Total Failed/Cancelled</small>
                <h2 class="fw-bold mb-1">{{ $failedCount }} Orders</h2>
                <div class="mt-2 fw-bold">Loss: Rs. {{ number_format($totalRefundValueLKR, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-4 border-0">
            <h6 class="mb-1 fw-bold">Transaction History (Outcomes Only)</h6>
        </div>
        <div class="table-responsive">
            <table class="table align-middle m-1 table-hover">
                <table class="table align-middle mb-0 table-hover">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tableOrders as $order)
                    <tr>
                        <td class="ps-4 text-muted small">{{ $order->created_at->format('d M, Y') }}</td>
                        <td class="fw-bold">#{{ $order->tracking_no }}</td>
                        <td>{{ $order->fname }}</td>
                        <td>
                            <span class="badge {{ $order->status == 5 ? 'bg-success' : 'bg-danger' }}">
                                {{ $order->status == 5 ? 'Delivered' : 'Cancelled' }}
                            </span>
                        </td>
                        <td class="fw-bold text-end pe-4">
                            {{ $order->currency == 'USD' ? '$' : 'Rs.' }} {{ number_format($order->total_price, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">No final transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function handleDateChange(input) {
        if (input.value === "") {
            window.location.href = "{{ route('admin.reports.sales') }}";
        } else {
            document.getElementById('filterForm').submit();
        }
    }
</script>
@endsection