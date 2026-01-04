@extends('layouts.delivery')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root { --primary-dark: #5b2c2c; }
    .stat-card { border-radius: 18px; border: none; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
    
    /* Unified COD Header Card */
    .unified-cash-card { border-radius: 18px; overflow: hidden; border: none; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .cash-header { background: var(--primary-dark); color: #fff; padding: 12px 25px; }
    .cash-split { display: flex; text-align: center; }
    .cash-box { flex: 1; padding: 15px; color: #fff; }

    .chart-container { position: relative; height: 280px; width: 100%; }
    .table-box { border-radius: 20px; background: #fff; padding: 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
</style>

<div class="container-fluid py-4 px-md-5">
    {{-- Header Section --}}
    <div class="row align-items-center mb-4 g-3">
        <div class="col-12 col-md-6">
            <h2 class="fw-bold text-dark m-0">Performance Overview</h2>
            <p class="text-muted small m-0">Assigned deliveries and collection summary</p>
        </div>
        <div class="col-12 col-md-6 text-md-end">
            <span class="badge bg-white text-dark border p-2 rounded-pill px-3 fw-bold shadow-sm">
                <i class="bi bi-calendar3 text-primary me-2"></i> {{ now()->format('l, F d, Y') }}
            </span>
        </div>
    </div>

    {{-- Unified COD Box: Now at the top for better visibility --}}
    <div class="card unified-cash-card mb-4">
        <div class="cash-header d-flex justify-content-between align-items-center">
            <span class="small fw-bold text-uppercase">Financial Overview</span>
            <span class="fw-bold">Full COD Potential: Rs. {{ number_format($fullCodPotential, 2) }}</span>
        </div>
        <div class="cash-split">
            <div class="cash-box" style="background: #198754;">
                <div class="small fw-bold opacity-75 mb-1 text-uppercase">Delivered COD Total</div>
                <h4 class="fw-bold mb-0">Rs. {{ number_format($deliveredCodTotal, 2) }}</h4>
            </div>
            <div class="cash-box" style="background: #0d6efd;">
                <div class="small fw-bold opacity-75 mb-1 text-uppercase">Pending COD (Active)</div>
                <h4 class="fw-bold mb-0">Rs. {{ number_format($pendingCodTotal, 2) }}</h4>
            </div>
        </div>
    </div>

    {{-- Stats Row: Stacked nicely below the money --}}
    <div class="row g-4 mb-4 text-center">
        @php
            $stats = [
                ['title' => 'ACTIVE TASKS', 'today' => $pendingToday, 'total' => $pendingTotal, 'color' => '#ffc107', 'icon' => 'bi-hourglass-split'],
                ['title' => 'DELIVERED', 'today' => $deliveredToday, 'total' => $deliveredTotal, 'color' => '#198754', 'icon' => 'bi-check2-all'],
                ['title' => 'FAILED/CLOSED', 'today' => $failedToday, 'total' => $failedTotal, 'color' => '#dc3545', 'icon' => 'bi-x-circle']
            ];
        @endphp
        @foreach($stats as $s)
        <div class="col-12 col-md-4">
            <div class="card stat-card p-3">
                <i class="bi {{ $s['icon'] }} fs-3 mb-2" style="color: {{ $s['color'] }}"></i>
                <div class="fw-bold text-muted small text-uppercase mb-2">{{ $s['title'] }}</div>
                <div class="d-flex justify-content-around">
                    <div><small class="text-muted d-block">Today</small><h5 class="fw-bold m-0">{{ $s['today'] }}</h5></div>
                    <div style="width: 1px; background: #eee;"></div>
                    <div><small class="text-muted d-block">Total</small><h5 class="fw-bold m-0">{{ $s['total'] }}</h5></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Charts Section --}}
    <div class="row g-4 mb-5">
        <div class="col-12 col-lg-4">
            <div class="card stat-card p-4">
                <h6 class="fw-bold mb-4 text-center">Today's Distribution</h6>
                <div class="chart-container"><canvas id="pieChart"></canvas></div>
            </div>
        </div>
        <div class="col-12 col-lg-8">
            <div class="card stat-card p-4">
                <h6 class="fw-bold mb-4">Performance Trend (Since Jan 01)</h6>
                <div class="chart-container"><canvas id="lineChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Recent Orders Table --}}
    <div class="table-box mb-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold m-0">Recent Assigned Tasks</h5>
            <a href="{{ route('delivery.my-deliveries') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>Tracking</th><th>Customer</th><th>City</th><th class="text-center">Amount</th><th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeDeliveries as $order)
                    <tr>
                        <td class="fw-bold text-primary">#{{ $order->tracking_no }}</td>
                        <td class="fw-bold small">{{ $order->fname }}</td>
                        <td class="text-muted small">{{ $order->city }}</td>
                        <td class="text-center fw-bold small">Rs. {{ number_format($order->total_price, 2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('delivery.orders.show', $order->id) }}" class="btn btn-sm btn-dark rounded-pill px-3">Manage</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted small">No active assignments.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Doughnut
        new Chart(document.getElementById('pieChart'), {
            type: 'doughnut',
            data: {
                labels: ['Delivered', 'Active', 'Failed'],
                datasets: [{
                    data: [{{ $deliveredToday }}, {{ $pendingToday }}, {{ $failedToday }}],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom' } } }
        });

        // Wavy Performance
        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($dates) !!},
                datasets: [
                    { label: 'Success', data: {!! json_encode($deliveredSeries) !!}, borderColor: '#198754', tension: 0.4, fill: false },
                    { label: 'Failed', data: {!! json_encode($failedSeries) !!}, borderColor: '#dc3545', tension: 0.4, fill: false }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
    });
</script>
@endsection