@extends('layouts.admin')

@section('content')
{{-- Structured Dashboard Styling --}}
<style>
    .dashboard-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); 
        background: white;
        transition: all 0.3s ease;
    }
    .dashboard-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1); }
    
    .metric-label { font-size: 0.75rem; color: #64748b; font-weight: 700; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
    .metric-value { font-size: 1.45rem; font-weight: 800; color: #1e293b; line-height: 1.2; }

    /* Dynamic Colorful Borders */
    .accent-lkr { border-top: 5px solid #ff8a00; }
    .accent-usd { border-top: 5px solid #3b82f6; }
    .accent-orders { border-top: 5px solid #10b981; }
    .accent-products { border-top: 5px solid #6f42c1; }
    .accent-categories { border-top: 5px solid #d63384; }
    .accent-reviews { border-top: 5px solid #0dcaf0; }

    /* Status Badge Word Mapping */
    .status-dot { height: 8px; width: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; }
    .status-badge { padding: 5px 12px; border-radius: 20px; font-weight: 700; font-size: 0.72rem; display: inline-flex; align-items: center; }
    .badge-delivered { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
    .badge-shipping { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
    .badge-pending { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
    .badge-processing { background: #fffbeb; color: #d97706; border: 1px solid #fef3c7; }

    .chart-center-text {
        position: absolute;
        top: 55%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
        width: 100%;
    }

    .btn-action { background: #ff8a00; color: white; border-radius: 8px; padding: 6px 16px; border: none; font-weight: 600; text-decoration: none; font-size: 0.8rem; }
    .btn-action:hover { background: #e67e00; color: white; }
</style>

<div class="container-fluid px-4 py-4">
    {{-- Lively Top Row: Colorful Accent Borders --}}
    <div class="row g-3 mb-4 text-start">
        <div class="col-md-2">
            <div class="card dashboard-card p-3 accent-lkr h-100">
                <span class="metric-label text-warning">Sales (LKR)</span>
                <h3 class="metric-value">Rs. {{ number_format($salesLkr, 0) }}</h3>
                <small class="text-success fw-bold">+3.34% ↑</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card dashboard-card p-3 accent-usd h-100">
                <span class="metric-label text-primary">Sales (USD)</span>
                <h3 class="metric-value">$ {{ number_format($salesUsd, 2) }}</h3>
                <small class="text-muted">International</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card dashboard-card p-3 accent-orders h-100">
                <span class="metric-label text-success">Today's Orders</span>
                <h3 class="metric-value text-success">{{ $todaysOrders }}</h3>
                <small class="text-muted">New activity</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card dashboard-card p-3 accent-products h-100">
                <span class="metric-label" style="color: #6f42c1;">Products</span>
                <h3 class="metric-value">{{ $totalProducts }}</h3>
                <small class="text-muted">In Inventory</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card dashboard-card p-3 accent-categories h-100">
                <span class="metric-label" style="color: #d63384;">Categories</span>
                <h3 class="metric-value">{{ $totalCategories }}</h3>
                <small class="text-muted">Active groupings</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card dashboard-card p-3 accent-reviews h-100">
                <span class="metric-label text-info">Reviews</span>
                <h3 class="metric-value">{{ $totalReviews ?? 0 }}</h3>
                <small class="text-danger fw-bold">Total Views</small>
            </div>
        </div>
    </div>

    {{-- Advanced Analytics Row --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card dashboard-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0">Revenue Growth</h5>
                    <div class="badge border rounded-pill text-dark bg-light px-3 py-2">
                        <i class="bi bi-calendar3 me-1"></i> Last 8 Days
                    </div>
                </div>
                <div style="height: 350px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card dashboard-card p-4 h-100 position-relative">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0">Top Categories</h5>
                    <a href="{{ url('admin/categories') }}" class="text-muted small">See All</a>
                </div>
                <div style="height: 250px; position: relative;">
                    <canvas id="categoryRoundChart"></canvas>
                    <div class="chart-center-text">
                        <span class="text-muted small d-block">Total Sales</span>
                        <h5 class="fw-bold text-dark mb-0">Rs. {{ number_format($topCategories->sum('total_revenue'), 0) }}</h5>
                    </div>
                </div>
                <div class="mt-3">
                    @php $legendColors = ['#ff8a00', '#3b82f6', '#10b981', '#fef3c7']; @endphp
                    @foreach($topCategories as $index => $cat)
                    <div class="d-flex justify-content-between align-items-center small mb-2">
                        <span><i class="bi bi-circle-fill me-2" style="color: {{ $legendColors[$index % 4] }}"></i>{{ $cat->name }}</span>
                        <strong>Rs. {{ number_format($cat->total_revenue, 0) }}</strong>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Multi-Color Performance Row --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card dashboard-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0">Product Performance</h5>
                    <a href="{{ url('admin/review-orders') }}" class="text-muted small">Manage Stock</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <tbody>
                            @php 
                                // Specific Multi-Color Palette
                                $barColors = ['#3b82f6', '#d63384', '#10b981', '#6f42c1', '#ff8a00']; 
                            @endphp
                            @foreach($topProducts as $index => $product)
                            <tr>
                                <td style="width: 45%"><span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $product->name }}</span></td>
                                <td class="text-center fw-bold text-muted" style="width: 20%;">{{ $product->total_sold ?? 0 }} sold</td>
                                <td class="text-end">
                                    <div class="progress" style="height: 8px; border-radius: 10px; width: 100%;">
                                        <div class="progress-bar" style="width: {{ ($product->total_sold / ($topProducts->first()->total_sold ?: 1)) * 100 }}%; background: {{ $barColors[$index % 5] }};"></div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card dashboard-card p-4 h-100 text-center">
                <h5 class="fw-bold text-dark text-start mb-4">Order Breakdown</h5>
                <div style="height: 250px;">
                    <canvas id="statusPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Real Store Activity Table --}}
    <div class="card dashboard-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold text-dark mb-0">Recent Store Activity</h5>
            <a href="{{ url('admin/orders') }}" class="btn-action shadow-sm">View Full History</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light text-muted small">
                    <tr><th>Order ID</th><th>Customer</th><th>Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                    <tr>
                        <td class="fw-bold text-dark">#{{ $order->tracking_no }}</td>
                        <td>{{ $order->fname }} {{ $order->lname }}</td>
                        <td class="fw-bold text-dark">{{ $order->currency }} {{ number_format($order->total_price, 2) }}</td>
                        <td>
                            @php
                                $numStatus = (string)$order->status;
                                $statusMap = [
                                    '0' => ['Pending', 'badge-pending', '#dc2626'],
                                    '1' => ['Processing', 'badge-processing', '#d97706'],
                                    '4' => ['Shipping', 'badge-shipping', '#ea580c'],
                                    '5' => ['Delivered', 'badge-delivered', '#16a34a']
                                ];
                                $current = $statusMap[$numStatus] ?? ['Review', 'bg-secondary', '#6c757d'];
                            @endphp
                            <span class="status-badge {{ $current[1] }}">
                                <span class="status-dot" style="background: {{ $current[2] }}"></span>{{ $current[0] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Revenue Line Chart
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: @json($days),
            datasets: [{
                label: 'Revenue',
                data: @json($revenueData),
                borderColor: '#ff8a00',
                backgroundColor: 'rgba(255, 138, 0, 0.1)',
                fill: true, tension: 0.4,
                pointRadius: 6, pointBackgroundColor: '#fff', pointBorderColor: '#ff8a00', pointBorderWidth: 2
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 2. Round Category Chart
    new Chart(document.getElementById('categoryRoundChart'), {
        type: 'doughnut',
        data: {
            labels: @json($topCategories->pluck('name')),
            datasets: [{
                data: @json($topCategories->pluck('total_revenue')),
                backgroundColor: ['#ff8a00', '#3b82f6', '#10b981', '#fef3c7'],
                borderWidth: 0, cutout: '82%', hoverOffset: 12
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 3. Status Distribution Pie Chart
    new Chart(document.getElementById('statusPieChart'), {
        type: 'doughnut',
        data: {
            labels: @json(array_keys($statusCounts)),
            datasets: [{
                data: @json(array_values($statusCounts)),
                backgroundColor: ['#d97706', '#4bea0cff', '#16a34a', '#dc2626'],
                borderWidth: 0, cutout: '70%', hoverOffset: 10
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } } } }
    });
});
</script>
@endsection