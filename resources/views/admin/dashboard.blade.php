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
        color: white !important;
    }
    .dashboard-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1); }
    
    .metric-label { font-size: 0.75rem; color: #00050c; font-weight: 700; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
    .metric-value { font-size: 1.45rem; font-weight: 800; color: #1e293b; line-height: 1.2; }

    .bg-lkr-solid { background: linear-gradient(135deg, #ff8a00 0%, #ffb347 100%); }
    .bg-usd-solid { background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); }
    .bg-orders-solid { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }
    .bg-inventory-solid { background: linear-gradient(135deg, #6f42c1 0%, #a855f7 100%); }
    .bg-reviews-solid { background: linear-gradient(135deg, #0dcaf0 0%, #22d3ee 100%); }

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
    .badge-cancelled { background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; }


    .divider-white { border-top: 1px solid rgba(255, 255, 255, 0.2); margin: 10px 0; }
    .text-dark-custom {
        color: #1e293b !important;
    }
    .badge-glass { background: rgba(255, 255, 255, 0.2); color: white; border: none; }
    
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
    <div class="row g-3 mb-4 text-start">
        
        {{-- LKR Revenue Card - Solid Orange --}}
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card p-3 bg-lkr-solid h-100">
                <div class="d-flex justify-content-between">
                    <span class="metric-label">Successful Revenue (LKR)</span>
                    <span class="badge badge-glass rounded-pill" style="font-size: 10px;">{{ $successLKRCount }} Total</span>
                </div>
                <h3 class="metric-value">Rs. {{ number_format($salesLkr, 2) }}</h3>
                
                <div class="divider-white"></div>
                
                <span class="metric-label">Pending Value</span>
                <h5 class="fw-bold mb-0 text-white">Rs. {{ number_format($pendingLkr, 2) }}</h5>
                <small class="text-white-80" style="font-size: 10px;">{{ $pendingLKRCount }} Local Orders Awaiting</small>
            </div>
        </div>

        {{-- USD Revenue Card - Solid Blue --}}
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card p-3 bg-usd-solid h-100">
                <div class="d-flex justify-content-between">
                    <span class="metric-label">Successful Revenue (USD)</span>
                    <span class="badge badge-glass rounded-pill" style="font-size: 10px;">{{ $successUSDCount }} Total</span>
                </div>
                <h3 class="metric-value">$ {{ number_format($salesUsd, 2) }}</h3>
                
                <div class="divider-white"></div>
                
                <span class="metric-label">Pending Value</span>
                <h5 class="fw-bold mb-0 text-white">$ {{ number_format($pendingUsd, 2) }}</h5>
                <small class="text-white-80" style="font-size: 10px;">{{ $pendingUSDCount }} Intl. Orders Awaiting</small>
            </div>
        </div>

        {{-- Today's Orders Card - Solid Green --}}
        <div class="col-xl-2 col-md-4">
            <div class="card dashboard-card p-3 bg-orders-solid h-100">
                <span class="metric-label">Today's Orders</span>
                <h3 class="metric-value">{{ $todaysOrders }}</h3>
                
                <div class="divider-white"></div>
                
                <span class="metric-label">Total Orders</span>
                <h5 class="fw-bold mb-0 text-white">{{ $totalOrdersAllTime }}</h5>
                <small class="text-white-80" style="font-size: 10px;">Lifetime activity</small>
            </div>
        </div>

        {{-- Inventory Card - Solid Purple --}}
        <div class="col-xl-2 col-md-4">
            <div class="card dashboard-card p-3 bg-inventory-solid h-100">
                <span class="metric-label">Inventory</span>
                <h3 class="metric-value">{{ $totalProducts }}</h3>
                <small class="text-white-80 d-block">Active Products</small>
                <div class="divider-white"></div>
                <h6 class="fw-bold mb-0 text-white">{{ $totalCategories }}</h6>
                <small class="text-white-80" style="font-size: 10px;">Categories</small>
            </div>
        </div>
        
        {{-- Reviews Card - Solid Cyan --}}
        <div class="col-xl-2 col-md-4">
            <a href="{{ route('admin.reviews') }}" style="text-decoration: none; display: block;" class="h-100">
                <div class="card dashboard-card p-3 bg-reviews-solid h-100">
                    <span class="metric-label">Reviews</span>
                    <h3 class="metric-value text-white">{{ $totalReviews }}</h3>
                    <small class="text-white fw-bold">Customer Feedback</small>
                    <div class="divider-white"></div>
                    <small class="text-white fw-bold">View All Reviews →</small>
                </div>
            </a>
        </div>
    </div>
</div>
    {{-- Advanced Analytics Row --}}
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card dashboard-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark mb-0">Revenue Growth</h5>
                {{--  Toggle Buttons for Days/Months --}}
                <div class="btn-group shadow-sm" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm active" id="btnDays" onclick="updateChart('days')">Last 8 Days</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnMonths" onclick="updateChart('months')">Monthly</button>
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
            {{-- Force dark text for the heading --}}
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
            @php $legendColors = ['#f5a2a4', '#ebb1f2', '#aeb5f2', '#a7f2b4']; @endphp
            @foreach($topCategories as $index => $cat)
            <div class="d-flex justify-content-between align-items-center small mb-2">
                {{-- Force text-dark class here to make names visible --}}
                <span class="text-dark">
                    <i class="bi bi-circle-fill me-2" style="color: {{ $legendColors[$index % 4] }}"></i>
                    {{ $cat->name }}
                </span>
                <strong class="text-dark">Rs. {{ number_format($cat->total_revenue, 0) }}</strong>
            </div>
            @endforeach
        </div>
    </div>
</div>
{{-- Performance & Breakdown Row --}}
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="card dashboard-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-dark mb-0">Product Performance</h5>
                <a href="{{ url('admin/products') }}" class="text-muted small">Manage Stock</a>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0">
                    <tbody>
                        @php $barColors = ['#3b82f6', '#d63384', '#10b981', '#6f42c1', '#ff8a00']; @endphp
                        @foreach($topProducts as $index => $product)
                        <tr>
                            <td style="width: 45%"><span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $product->name }}</span></td>
                            <td class="text-center fw-bold text-muted" style="width: 20%;">{{ $product->total_sold ?? 0 }} sold</td>
                            <td class="text-end">
                                <div class="progress" style="height: 8px; border-radius: 10px; width: 100%;">
                                    {{--  Fixed percentage logic: max sold item is 100% --}}
                                    <div class="progress-bar" style="width: {{ ($product->total_sold / (max($topProducts->first()->total_sold, 1))) * 100 }}%; background: {{ $barColors[$index % 5] }};"></div>
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
        <div class="card dashboard-card p-4 h-100">
            <h5 class="fw-bold text-dark text-start mb-4">Order Breakdown</h5>
            <div style="height: 300px;">
                {{--  Bar chart is better for comparing many statuses --}}
                <canvas id="orderStatusBarChart"></canvas>
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
                                '5' => ['Delivered', 'badge-delivered', '#16a34a'],
                                '6' => ['Cancelled', 'badge-cancelled', '#991b1b'] // Added Cancelled logic
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let revenueChart;
// Ensure these variables are coming from the controller accurately
const dailyLabels = @json($days);
const dailyData = @json($revenueData);
const monthlyLabels = @json($months ?? []); 
const monthlyData = @json($monthlyRevenue ?? []); 

document.addEventListener('DOMContentLoaded', function () {
    // 1. Revenue Growth Chart
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    revenueChart = new Chart(revCtx, {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Successful Revenue',
                data: dailyData,
                borderColor: '#ff8a00',
                backgroundColor: 'rgba(255, 138, 0, 0.1)',
                fill: true, 
                tension: 0.4,
                pointRadius: 4, 
                pointBackgroundColor: '#fff', 
                pointBorderWidth: 2
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { 
                legend: { display: false },
                tooltip: { enabled: true } // Added tooltips for better UX
            } 
        }
    });

    // 2. Category Donut Chart
    new Chart(document.getElementById('categoryRoundChart'), {
        type: 'doughnut',
        data: {
            labels: @json($topCategories->pluck('name')),
            datasets: [{
                data: @json($topCategories->pluck('total_revenue')),
                backgroundColor: ['#f5a2a4', '#ebb1f2', '#aeb5f2', '#a7f2b4'],
                borderWidth: 0, 
                cutout: '80%'
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { 
                legend: { display: false },
                tooltip: { enabled: true } // This fixes the "names not showing" issue on hover
            } 
        }
    });

    // 3. Order Status Bar Chart (Horizontal)
    new Chart(document.getElementById('orderStatusBarChart'), {
        type: 'bar',
        data: {
            labels: @json(array_keys($statusCounts)),
            datasets: [{
                data: @json(array_values($statusCounts)),
                backgroundColor: ['#f5e216', '#3b82f6', '#16a395', '#f07d41', '#1bd173', '#dc2626', '#585d61'],
                borderRadius: 5
            }]
        },
        options: { 
            indexAxis: 'y', 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { 
                legend: { display: false } 
            },
            scales: {
                x: { beginAtZero: true } // Ensures bars start from 0
            }
        }
    });
});

function updateChart(type) {
    if (type === 'days') {
        revenueChart.data.labels = dailyLabels;
        revenueChart.data.datasets[0].data = dailyData;
        document.getElementById('btnDays').classList.add('active');
        document.getElementById('btnMonths').classList.remove('active');
    } else {
        // Only update if data exists
        if(monthlyLabels.length > 0) {
            revenueChart.data.labels = monthlyLabels;
            revenueChart.data.datasets[0].data = monthlyData;
            document.getElementById('btnMonths').classList.add('active');
            document.getElementById('btnDays').classList.remove('active');
        }
    }
    revenueChart.update();
}
</script>

@endsection