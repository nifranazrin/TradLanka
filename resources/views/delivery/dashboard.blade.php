@extends('layouts.delivery')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --primary-dark: #5b2c2c;
        --success-color: #198754;
        --pending-color: #0d6efd;
        --bg-light: #f8f9fa;
        --glass-white: rgba(255, 255, 255, 0.95);
    }

    /* 1. Main Dashboard Container */
    .dashboard-wrapper {
        background-color: var(--bg-light);
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
        padding-bottom: 2rem;
    }

    /* 2. Unified Card System */
    .fin-card {
        background: #fff;
        border-radius: 20px;
        transition: transform 0.3s ease;
        padding: 1.5rem;
    }
    .fin-card:hover { transform: translateY(-5px); }

    .icon-box {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-card {
        border-radius: 20px;
        border: none;
        background: #fff;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: transform 0.3s ease;
    }
    .stat-card:hover { transform: translateY(-3px); }

    /* 3. Table and Chart Containers */
    .table-box {
        border-radius: 24px;
        background: #fff;
        padding: 2rem;
        border: 1px solid rgba(0,0,0,0.04);
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
    }

    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }

    .text-premium { color: var(--primary-dark); }
</style>

<div class="dashboard-wrapper">
    <div class="container-fluid py-4 px-md-5">
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            @php
                $firstName = explode(' ', Auth::guard('delivery')->user()->name)[0];
            @endphp
            <h1 class="fw-bold mb-1" style="font-size: 2.2rem;">Welcome back, {{ $firstName }}</h1>
            <h5 class="text-muted fw-normal">Delivery Dashboard Overview</h5>
        </div>
       <div class="col-12 col-md-6 text-md-end">
            <span class="badge bg-white text-dark border p-2 rounded-pill px-3 fw-bold shadow-sm">
                <i class="bi bi-calendar3 text-primary me-2"></i> {{ now()->format('l, F d, Y') }}
            </span>
        </div>
    </div>


        {{-- Financial Overview Card --}}
        <div class="card border-0 shadow-sm mb-4 p-4" style="border-radius: 24px; background: #ffffff;">
            <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                <div>
                    <h5 class="fw-bold m-0 text-premium">Financial Overview</h5>
                    <small class="text-muted">Real-time COD tracking</small>
                </div>
                <div class="text-end">
                    <span class="text-muted small text-uppercase fw-bold d-block" style="letter-spacing: 1px;">Full COD Potential</span>
                    <h4 class="fw-bold m-0 text-premium">Rs. {{ number_format($fullCodPotential, 2) }}</h4>
                </div>
            </div>

            <div class="row g-3">
                {{-- Delivered Card --}}
                <div class="col-md-6">
                    <div class="fin-card border shadow-sm h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                                <i class="bi bi-cash-coin fs-4"></i>
                            </div>
                            <div>
                                <span class="text-muted small text-uppercase fw-bold" style="font-size: 0.65rem;">Delivered COD Total</span>
                                <h3 class="fw-bold m-0 text-dark">Rs. {{ number_format($deliveredCodTotal, 2) }}</h3>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 10px; background: #e9ecef;">
                            <div class="progress-bar bg-success" style="width: {{ $fullCodPotential > 0 ? ($deliveredCodTotal / $fullCodPotential) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Pending Card --}}
                <div class="col-md-6">
                    <div class="fin-card border shadow-sm h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                            <div>
                                <span class="text-muted small text-uppercase fw-bold" style="font-size: 0.65rem;">Pending COD (Active)</span>
                                <h3 class="fw-bold m-0 text-dark">Rs. {{ number_format($pendingCodTotal, 2) }}</h3>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 10px; background: #e9ecef;">
                            <div class="progress-bar bg-primary" style="width: {{ $fullCodPotential > 0 ? ($pendingCodTotal / $fullCodPotential) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Row --}}
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
                <div class="card stat-card">
                    <i class="bi {{ $s['icon'] }} fs-3 mb-2" style="color: {{ $s['color'] }}"></i>
                    <div class="fw-bold text-muted small text-uppercase mb-2">{{ $s['title'] }}</div>
                    <div class="d-flex justify-content-around border-top pt-3">
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
                <div class="table-box">
                    <h6 class="fw-bold mb-4 text-center">Today's Distribution</h6>
                    <div class="chart-container"><canvas id="pieChart"></canvas></div>
                </div>
            </div>
            <div class="col-12 col-lg-8">
                <div class="table-box">
                    <h6 class="fw-bold mb-4">Performance Trend (Since Jan 01)</h6>
                    <div class="chart-container"><canvas id="lineChart"></canvas></div>
                </div>
            </div>
        </div>

        {{-- Recent Orders Table --}}
        <div class="table-box mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0">Recent Assigned Tasks</h5>
                <a href="{{ route('delivery.my-deliveries') }}" class="btn btn-sm btn-dark rounded-pill px-4">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted small text-uppercase">
                            <th>Tracking</th><th>Customer</th><th>City</th><th class="text-center">Amount</th><th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeDeliveries as $order)
                        <tr>
                            <td class="fw-bold text-primary">#{{ $order->tracking_no }}</td>
                            <td class="fw-bold">{{ $order->fname }}</td>
                            <td>{{ $order->city }}</td>
                            <td class="text-center fw-bold">Rs. {{ number_format($order->total_price, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('delivery.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">Manage</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted small">No active assignments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Doughnut Chart
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
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                cutout: '80%', 
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } } 
            }
        });

        // Line Chart
        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($dates) !!},
                datasets: [
                    { 
                        label: 'Success', 
                        data: {!! json_encode($deliveredSeries) !!}, 
                        borderColor: '#198754', 
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        tension: 0.4, 
                        fill: true 
                    },
                    { 
                        label: 'Failed', 
                        data: {!! json_encode($failedSeries) !!}, 
                        borderColor: '#dc3545', 
                        tension: 0.4, 
                        fill: false 
                    }
                ]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { position: 'top', labels: { usePointStyle: true } } },
                scales: { 
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f1f1' } },
                    x: { grid: { display: false } }
                } 
            }
        });
    });
</script>
@endsection