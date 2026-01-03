@extends('layouts.delivery')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Layout and Card Styling */
    .stat-card { transition: all 0.3s ease; border-radius: 16px; border: none; overflow: hidden; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    
    /* Icon Styling - Fixed to remove the "white box" issue */
    .icon-circle { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
    .bg-opacity-custom { background-color: rgba(255, 255, 255, 0.2); }
    
    .table-container { border-radius: 16px; background: #ffffff; border: 1px solid #f1f1f1; }
    .status-pill { padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    
    .chart-wrapper { position: relative; height: 300px; }
    
    /* Divider for Today/Total split */
    .card-divider { width: 1px; height: 35px; background: #eee; }
</style>

<div class="container-fluid py-4">
    {{-- WELCOME HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-6 text-center text-md-start">
            <h2 class="fw-bold text-dark mb-1">Welcome back, {{ Auth::guard('delivery')->user()->name }}!</h2>
            <p class="text-muted mb-0">Here's your delivery performance overview for today.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-center justify-content-md-end">
            <div class="btn bg-white border shadow-sm rounded-pill px-4">
                <i class="bi bi-calendar3 text-primary me-2"></i> {{ now()->format('M d, Y') }}
            </div>
        </div>
    </div>

    {{-- QUICK STATS CARDS (Updated with Today/Total split) --}}
    <div class="row g-4 mb-4 text-center">
        <div class="col-md-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center mb-3">
                        <div class="icon-circle bg-warning bg-opacity-10 text-warning mb-2">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                        <span class="text-secondary fw-semibold small">Pending Tasks</span>
                    </div>
                    <div class="d-flex justify-content-around align-items-center">
                        <div>
                            <div class="text-muted x-small" style="font-size: 0.7rem;">Today</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $pendingToday }}</h4>
                        </div>
                        <div class="card-divider"></div>
                        <div>
                            <div class="text-muted x-small" style="font-size: 0.7rem;">Total</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $pendingTotal }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center mb-3">
                        <div class="icon-circle bg-success bg-opacity-10 text-success mb-2">
                            <i class="bi bi-check2-all fs-4"></i>
                        </div>
                        <span class="text-secondary fw-semibold small">Delivered</span>
                    </div>
                    <div class="d-flex justify-content-around align-items-center">
                        <div>
                            <div class="text-muted x-small" style="font-size: 0.7rem;">Today</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $deliveredToday }}</h4>
                        </div>
                        <div class="card-divider"></div>
                        <div>
                            <div class="text-muted x-small" style="font-size: 0.7rem;">Total</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $deliveredTotal }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center mb-3">
                        <div class="icon-circle bg-danger bg-opacity-10 text-danger mb-2">
                            <i class="bi bi-dash-circle-dotted fs-4"></i>
                        </div>
                        <span class="text-secondary fw-semibold small">Failed Orders</span>
                    </div>
                    <div class="d-flex justify-content-around align-items-center">
                        <div>
                            <div class="text-muted x-small" style="font-size: 0.7rem;">Today</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $failedToday }}</h4>
                        </div>
                        <div class="card-divider"></div>
                        <div>
                            <div class="text-muted x-small" style="font-size: 0.7rem;">Total</div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $failedTotal }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card shadow-sm h-100 bg-primary bg-gradient text-white border-0">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="d-flex flex-column align-items-center mb-2">
                        <div class="icon-circle bg-opacity-custom text-white mb-2">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <span class="fw-semibold small">Cash to Collect</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-center">Rs. {{ number_format($cashToCollect, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- ANALYTICS SECTION --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px;">
                <h5 class="fw-bold mb-4">Today's Distribution</h5>
                <div class="chart-wrapper">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 16px;">
                <h5 class="fw-bold mb-4">30-Day Performance Analysis</h5>
                <div class="chart-wrapper">
                    <canvas id="monthlyAnalysisChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- RECENT DISPATCHES TABLE --}}
    <div class="table-container shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0 text-dark">Recent Active Dispatches</h5>
            <a href="{{ route('delivery.my-deliveries') }}" class="btn btn-sm btn-link text-primary p-0 fw-bold text-decoration-none">View All</a>
        </div>

        @if($activeDeliveries->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr class="text-muted" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                            <th class="border-0 ps-3">TRACKING NO</th>
                            <th class="border-0">CUSTOMER</th>
                            <th class="border-0">LOCATION</th>
                            <th class="border-0">AMOUNT</th>
                            <th class="border-0">STATUS</th>
                            <th class="border-0 text-end pe-3">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeDeliveries as $order)
                            <tr>
                                <td class="ps-3"><span class="fw-bold text-dark">#{{ $order->tracking_no }}</span></td>
                                <td><div class="fw-semibold text-dark">{{ $order->fname }} {{ $order->lname }}</div></td>
                                <td><div class="small text-muted"><i class="bi bi-geo-alt-fill me-1"></i>{{ $order->city }}</div></td>
                                <td class="fw-bold text-dark">Rs. {{ number_format($order->total_price, 2) }}</td>
                                <td>
                                    @if($order->status == 4)
                                        <span class="status-pill bg-primary bg-opacity-10 text-primary">IN TRANSIT</span>
                                    @elseif($order->status == 6)
                                        <span class="status-pill bg-danger bg-opacity-10 text-danger">NOT RECEIVED</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('delivery.orders.show', $order->id) }}" class="btn btn-sm btn-dark rounded-pill px-3 py-1 shadow-sm">
                                        Manage
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="opacity-25 mb-3">
                <p class="text-muted">Your dispatch list is empty today.</p>
            </div>
        @endif
    </div>
</div>

{{-- CHARTS LOGIC SCRIPT --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. TODAY'S DISTRIBUTION CHART (Doughnut)
        const ctx1 = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Delivered', 'Pending', 'Failed'],
                datasets: [{
                    // Using "Today" variables to match the chart title
                    data: [{{ $deliveredToday }}, {{ $pendingToday }}, {{ $failedToday }}],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                    hoverOffset: 12,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                }
            }
        });

        // 2. MONTHLY LIVE ANALYSIS CHART (Line Chart)
        const ctx2 = document.getElementById('monthlyAnalysisChart').getContext('2d');
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: {!! json_encode($dates) !!},
                datasets: [
                    {
                        label: 'Success Deliveries',
                        data: {!! json_encode($deliveredSeries) !!},
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#198754'
                    },
                    {
                        label: 'Failed Deliveries',
                        data: {!! json_encode($failedSeries) !!},
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#dc3545'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1, color: '#666' },
                        grid: { borderDash: [5, 5] }
                    },
                    x: { grid: { display: false }, ticks: { color: '#666' } }
                },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, padding: 15 } },
                    tooltip: { padding: 12, cornerRadius: 8 }
                }
            }
        });
    });
</script>
@endsection