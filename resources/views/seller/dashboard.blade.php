
@extends('layouts.seller')

@section('content')
<div class="container-fluid p-4">
    
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            @php
                $seller = Auth::guard('seller')->user();
                $firstName = $seller ? explode(' ', $seller->name)[0] : 'Seller';
            @endphp
            <h2 class="fw-bold mb-1">Welcome back, {{ $firstName }}</h2>
            <h6 class="text-muted">Seller Dashboard Overview</h6>
        </div>
        <a href="{{ route('seller.products.index') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-lg me-1"></i> Add Product
        </a>
    </div>

    {{-- Dashboard Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 p-3 h-100" style="border-radius: 12px;">
                <h6 class="text-muted small text-uppercase">Total Products</h6>
                <h4 class="fw-bold text-dark mb-0" id="stat-total-products">{{ $totalProducts ?? '0' }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 p-3 h-100" style="border-radius: 12px;">
                <h6 class="text-muted small text-uppercase">Orders Today</h6>
                <h4 class="fw-bold text-primary mb-0" id="stat-orders-today">{{ $ordersToday ?? '0' }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 p-3 h-100" style="border-radius: 12px;">
                <h6 class="text-muted small text-uppercase">Pending Deliveries</h6>
                <h4 class="fw-bold text-danger mb-0" id="stat-pending">{{ $pendingDeliveries ?? '0' }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm border-0 p-3 h-100" style="border-radius: 12px;">
                <h6 class="text-muted small text-uppercase">Monthly Revenue</h6>
                <h4 class="fw-bold text-success mb-0" id="stat-revenue">Rs {{ number_format($monthlyRevenue ?? 0, 2) }}</h4>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-body text-center">
                    <h5 class="fw-bold mb-3">Orders by Status</h5>
                    <div style="height: 300px;"><canvas id="statusPieChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Daily Sale Chart</h5>
                    <div style="height: 300px;"><canvas id="salesLineChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Orders Table --}}
    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold">Recent Orders</h5>
                <a href="{{ route('seller.orders.index') }}" class="btn btn-sm btn-light text-primary fw-bold px-3">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tracking No</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="recent-orders-table">
                        @forelse($recentOrders ?? [] as $order)
                            <tr>
                                <td class="fw-bold">#{{ $order->tracking_no }}</td>
                                <td>{{ $order->fname }} {{ $order->lname }}</td>
                                <td>Rs {{ number_format($order->total_price, 2) }}</td>
                                <td>
                                    <span class="badge rounded-pill bg-opacity-10 
                                        @if($order->status == 0) bg-primary text-primary 
                                        @elseif($order->status == 4) bg-success text-success 
                                        @else bg-info text-info @endif">
                                        {{ $order->status_label ?? 'Processing' }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $order->created_at->format('d M, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No recent orders available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Lively Update Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let pieChart, lineChart;

    function initCharts(data) {
        const ctxPie = document.getElementById('statusPieChart').getContext('2d');
        pieChart = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: ['Processing', 'Shipped', 'Delivered'],
                datasets: [{
                    data: [data.pie.processing, data.pie.shipped, data.pie.delivered],
                    backgroundColor: ['#1424b5', '#11ab14', '#ab2811'],
                    borderWidth: 0
                }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        const ctxLine = document.getElementById('salesLineChart').getContext('2d');
        lineChart = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: data.line.labels,
                datasets: [{
                    label: 'Orders',
                    data: data.line.data,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5
                }]
            },
            options: { maintainAspectRatio: false, responsive: true, scales: { y: { beginAtZero: true } } }
        });
    }

    function refreshDashboard() {
        fetch("{{ route('seller.dashboard.chart-data') }}")
            .then(res => res.json())
            .then(data => {
                document.getElementById('stat-total-products').innerText = data.totalProducts || '0';
                document.getElementById('stat-orders-today').innerText = data.ordersToday || '0';
                document.getElementById('stat-pending').innerText = data.pendingDeliveries || '0';
                document.getElementById('stat-revenue').innerText = 'Rs ' + (data.monthlyRevenue || '0.00');

                if (!pieChart) {
                    initCharts(data);
                } else {
                    pieChart.data.datasets[0].data = [data.pie.processing, data.pie.shipped, data.pie.delivered];
                    pieChart.update();
                    lineChart.data.labels = data.line.labels;
                    lineChart.data.datasets[0].data = data.line.data;
                    lineChart.update();
                }
            })
            .catch(err => console.error("Update failed:", err));
    }

    document.addEventListener('DOMContentLoaded', () => {
        refreshDashboard();
        setInterval(refreshDashboard, 30000); 
    });
</script>
@endsection