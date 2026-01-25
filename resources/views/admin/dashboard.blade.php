@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- Header Section --}}
<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        @php
            $firstName = explode(' ', Auth::guard('admin')->user()->name)[0];
        @endphp
        <h1 class="fw-bold mb-1" style="font-size: 2.2rem;">Welcome back, {{ $firstName }}</h1>
        <h5 class="text-muted fw-normal">Admin Dashboard Overview</h5>
    </div>
    <div class="col-12 col-md-6 text-md-end">
        <span class="badge bg-white text-dark border p-2 rounded-pill px-3 fw-bold shadow-sm">
            <i class="bi bi-calendar3 text-primary me-2"></i> {{ now()->format('l, F d, Y') }}
        </span>
    </div>
</div>

<style>
    .dashboard-card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); background: white; transition: all 0.3s ease; color: white !important; }
    .dashboard-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1); }
    .metric-label { font-size: 0.75rem; color: #00050c; font-weight: 700; display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
    .metric-value { font-size: 1.45rem; font-weight: 800; color: #1e293b; line-height: 1.2; }
    .bg-lkr-solid { background: linear-gradient(135deg, #ff8a00 0%, #ffb347 100%); }
    .bg-usd-solid { background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); }
    .bg-orders-solid { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }
    .bg-inventory-solid { background: linear-gradient(135deg, #6f42c1 0%, #a855f7 100%); }
    .bg-reviews-solid { background: linear-gradient(135deg, #0dcaf0 0%, #22d3ee 100%); }
    .status-dot { height: 8px; width: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; }
    .status-badge { padding: 5px 12px; border-radius: 20px; font-weight: 700; font-size: 0.72rem; display: inline-flex; align-items: center; }
    .badge-delivered { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
    .badge-shipping { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
    .badge-pending { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
    .badge-processing { background: #fffbeb; color: #d97706; border: 1px solid #fef3c7; }
    .badge-cancelled { background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; }
    .divider-white { border-top: 1px solid rgba(255, 255, 255, 0.2); margin: 10px 0; }
    .badge-glass { background: rgba(255, 255, 255, 0.2); color: white; border: none; }
    .chart-center-text { position: absolute; top: 55%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none; width: 100%; }
    .btn-action { background: #ff8a00; color: white; border-radius: 8px; padding: 6px 16px; border: none; font-weight: 600; text-decoration: none; font-size: 0.8rem; }
    #adminMap { height: 400px; border-radius: 12px; z-index: 1; }
</style>

<div class="container-fluid px-4 py-4">
    {{-- Top Metrics Row --}}
    <div class="row g-3 mb-4 text-start">
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card p-3 bg-lkr-solid h-100">
                <div class="d-flex justify-content-between">
                    <span class="metric-label text-white">Successful Revenue (LKR)</span>
                    <span class="badge badge-glass rounded-pill" style="font-size: 10px;">{{ $successLKRCount }} Total</span>
                </div>
                <h3 class="metric-value text-white">Rs. {{ number_format($salesLkr, 2) }}</h3>
                <div class="divider-white"></div>
                <span class="metric-label text-white">Pending Value</span>
                <h5 class="fw-bold mb-0 text-white">Rs. {{ number_format($pendingLkr, 2) }}</h5>
                <small class="text-white-50" style="font-size: 10px;">{{ $pendingLKRCount }} Local Orders Awaiting</small>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-card p-3 bg-usd-solid h-100">
                <div class="d-flex justify-content-between">
                    <span class="metric-label text-white">Successful Revenue (USD)</span>
                    <span class="badge badge-glass rounded-pill" style="font-size: 10px;">{{ $successUSDCount }} Total</span>
                </div>
                <h3 class="metric-value text-white">$ {{ number_format($salesUsd, 2) }}</h3>
                <div class="divider-white"></div>
                <span class="metric-label text-white">Pending Value</span>
                <h5 class="fw-bold mb-0 text-white">$ {{ number_format($pendingUsd, 2) }}</h5>
                <small class="text-white-50" style="font-size: 10px;">{{ $pendingUSDCount }} Intl. Orders Awaiting</small>
            </div>
        </div>

        <div class="col-xl-2 col-md-4">
            <div class="card dashboard-card p-3 bg-orders-solid h-100">
                <span class="metric-label text-white">Today's Orders</span>
                <h3 class="metric-value text-white">{{ $todaysOrders }}</h3>
                <div class="divider-white"></div>
                <span class="metric-label text-white">Total Orders</span>
                <h5 class="fw-bold mb-0 text-white">{{ $totalOrdersAllTime }}</h5>
            </div>
        </div>

        <div class="col-xl-2 col-md-4">
            <div class="card dashboard-card p-3 bg-inventory-solid h-100">
                <span class="metric-label text-white">Inventory</span>
                <h3 class="metric-value text-white">{{ $totalProducts }}</h3>
                <div class="divider-white"></div>
                <h6 class="fw-bold mb-0 text-white">{{ $totalCategories }} Categories</h6>
            </div>
        </div>

        <div class="col-xl-2 col-md-4">
            <a href="{{ route('admin.reviews') }}" style="text-decoration: none;">
                <div class="card dashboard-card p-3 bg-reviews-solid h-100">
                    <span class="metric-label text-white">Reviews</span>
                    <h3 class="metric-value text-white">{{ $totalReviews }}</h3>
                    <div class="divider-white"></div>
                    <small class="text-white fw-bold">Feedback →</small>
                </div>
            </a>
        </div>
    </div>

    {{-- Growth & Categories Row --}}
    <div class="row g-4 mb-4 text-start">
        <div class="col-lg-8">
            <div class="card dashboard-card p-4" style="color: #000 !important;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0">Revenue Growth</h5>
                    <div class="btn-group shadow-sm">
                        <button type="button" class="btn btn-outline-primary btn-sm active" id="btnDays" onclick="updateChart('days')">8 Days</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnMonths" onclick="updateChart('months')">Monthly</button>
                    </div>
                </div>
                <div style="height: 350px;"><canvas id="revenueChart"></canvas></div>
            </div>
        </div>
        
     <div class="col-lg-4">
    <div class="card dashboard-card p-4 h-100 position-relative" style="background: #fff; color: #000 !important;">
        <h5 class="fw-bold text-dark mb-4">Category Market Share</h5>
        
        <div style="height: 250px; position: relative; overflow: hidden;">
            <canvas id="categoryRoundChart"></canvas>
            <div class="chart-center-text">
                <span class="text-muted small d-block">Platform Total</span>
                <h5 class="fw-bold text-dark mb-0">100%</h5>
            </div>
        </div>

        <div class="mt-4">
            @php 
                $legendColors = ['#f5a2a4', '#ebb1f2', '#aeb5f2', '#a7f2b4', '#fde68a']; 
            @endphp
            @foreach($topCategories as $index => $cat)
            <div class="d-flex justify-content-between align-items-center small mb-2">
                <span class="text-dark">
                    <i class="bi bi-circle-fill me-2" style="color: {{ $legendColors[$index % 5] }}"></i>
                    {{ $cat->name }}
                </span>
                {{-- Changed to Percentage --}}
                <strong class="text-primary">{{ number_format($cat->share_percentage, 1) }}%</strong>
            </div>
            @endforeach
        </div>
    </div>
</div>
       

    {{-- NEW: Live Map & City Revenue Row --}}
    <div class="row g-4 mb-4 text-start">
        <div class="col-lg-8">
            <div class="card dashboard-card p-4 h-100" style="background: #fff; color: #000 !important;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Live Delivery Map</h5>
                    <div class="btn-group btn-group-sm rounded-pill shadow-sm bg-light p-1">
                        <button class="btn btn-dark rounded-pill px-3 filter-btn active" onclick="updateDashboardView('local', event)">Local</button>
                        <button class="btn btn-outline-dark border-0 rounded-pill px-3 filter-btn" onclick="updateDashboardView('international', event)">International</button>
                    </div>
                </div>
                <div id="adminMap"></div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card dashboard-card p-4 h-100" style="background: #fff; color: #000 !important;">
                <h5 class="fw-bold text-dark mb-4"><i class="bi bi-cash-stack text-primary me-2"></i>Revenue by City</h5>
                <div class="chart-container" style="height: 250px;"><canvas id="cityRevenueChart"></canvas></div>
                <div id="topPerformerCard" class="mt-4 p-3 rounded-4 border bg-light text-center">
                    <small id="performerLabel" class="text-muted d-block mb-1">Top Local (LKR)</small>
                    <h4 id="performerCity" class="fw-bold text-dark mb-1">{{ $topLocalCity->city ?? 'N/A' }}</h4>
                    <span id="performerAmount" class="badge bg-primary rounded-pill px-3">
                        {{ number_format($topLocalCity->total_revenue ?? 0, 0) }} LKR
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Product Performance & Status Row --}}
    <div class="row g-4 mb-4 text-start">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100 p-4" style="border-radius: 15px; background: #fff;">
                <h5 class="fw-bold text-dark mb-4">Top Selling Products</h5>
                <div class="list-group list-group-flush">
                    @forelse($topProducts as $tp)
                    <div class="list-group-item px-0 border-0 d-flex align-items-center gap-3 mb-3">
                        <img src="{{ asset('storage/'.$tp->image) }}" class="rounded shadow-sm" width="55" height="55" style="object-fit: cover;">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold text-dark">{{ Str::limit($tp->name, 35) }}</h6>
                            <span class="text-muted small">{{ number_format($tp->total_sold) }} units sold</span>
                        </div>
                        <span class="badge rounded-pill px-3 py-2" style="background: #eef2ff; color: #4338ca; font-weight: 700;">#{{ $loop->iteration }}</span>
                    </div>
                    @empty
                    <p class="text-muted text-center py-5">No sales data found.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card dashboard-card p-4 h-100" style="color: #000 !important;">
                <h5 class="fw-bold text-dark mb-4">Order Breakdown</h5>
                <div style="height: 300px;"><canvas id="orderStatusBarChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Recent Store Activity Table --}}
    <div class="card dashboard-card p-4" style="color: #000 !important;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold text-dark mb-0">Recent Store Activity</h5>
            <a href="{{ route('admin.orders.review') }}" class="btn-action shadow-sm">View Full History</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr class="text-muted small"><th>Order ID</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($recentOrders as $order)
                    <tr>
                        <td class="fw-bold">#{{ $order->tracking_no }}</td>
                        <td>{{ $order->fname }} {{ $order->lname }}</td>
                        <td class="fw-bold">{{ $order->currency }} {{ number_format($order->total_price, 2) }}</td>
                        <td>
                            @php
                                $statusMap = [
                                    '0' => ['Pending', 'badge-pending', '#dc2626'],
                                    '1' => ['Processing', 'badge-processing', '#d97706'],
                                    '4' => ['Shipping', 'badge-shipping', '#ea580c'],
                                    '5' => ['Delivered', 'badge-delivered', '#16a34a'],
                                    '6' => ['Cancelled', 'badge-cancelled', '#991b1b'] 
                                ];
                                $current = $statusMap[(string)$order->status] ?? ['Pending', 'badge-pending', '#dc2626'];
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

<script>
let revenueChart;
let cityRevenueChart; 

// Data sourced from AdminDashController
const dailyLabels = @json($days);
const dailySalesData = @json($salesData);
const dailyRevenueData = @json($revenueData);

const monthlyLabels = @json($months ?? []); 
const monthlySalesData = @json($monthlySalesData ?? []); 
const monthlyRevenueData = @json($monthlyRevenue ?? []); 

document.addEventListener('DOMContentLoaded', function () {
    // 1. Live Map Initialization with TradLanka Attribution
const map = L.map('adminMap').setView([7.8731, 80.7718], 7.5);
map.attributionControl.setPrefix('Leaflet | © TradLanka');

L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png', {
    attribution: '| CARTO'
}).addTo(map);

const markerGroup = L.layerGroup().addTo(map);

// Define Blue Icon with standard Leaflet sizing
const blueIcon = L.icon({ 
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png', 
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

// --- UPDATED GLOBAL MAP/CITY TOGGLE LOGIC ---
window.updateDashboardView = function(type, event) {
    markerGroup.clearLayers();
    let bounds = [];

    const data = (type === 'local') ? {
        labels: {!! json_encode(collect($cityRevenue ?? [])->pluck('city')) !!},
        values: {!! json_encode(collect($cityRevenue ?? [])->pluck('total_revenue')) !!},
        hotspots: {!! json_encode(collect($deliveryHotspots ?? [])->where('currency', 'LKR')->values()) !!},
        topCity: "{{ $topLocalCity->city ?? 'N/A' }}",
        topAmount: "{{ number_format($topLocalCity->total_revenue ?? 0, 0) }} LKR",
        center: [7.8731, 80.7718], 
        zoom: 7.5
    } : {
        labels: {!! json_encode(collect($intlRevenue ?? [])->pluck('city')) !!},
        values: {!! json_encode(collect($intlRevenue ?? [])->pluck('total_revenue')) !!},
        hotspots: {!! json_encode(collect($deliveryHotspots ?? [])->where('currency', 'USD')->values()) !!},
        topCity: "{{ $topInternationalCity->city ?? 'N/A' }}",
        topAmount: "$ {{ number_format($topInternationalCity->total_revenue ?? 0, 2) }} USD",
        center: [20, 0], 
        zoom: 2
    };

    // UI Updates
    if (cityRevenueChart) {
        cityRevenueChart.data.labels = data.labels;
        cityRevenueChart.data.datasets[0].data = data.values;
        cityRevenueChart.update();
    }
    document.getElementById('performerCity').innerText = data.topCity;
    document.getElementById('performerAmount').innerText = data.topAmount;
    document.getElementById('performerLabel').innerText = (type === 'local') ? 'Top Local (LKR)' : 'Top International (USD)';

    // 1. PLOT ALL PURCHASES AS RED CIRCLES
    data.hotspots.forEach(spot => {
        if (spot.lat && spot.lng) {
            // Draw all cities as red circles
            L.circleMarker([spot.lat, spot.lng], { 
                radius: Math.min(spot.count * 8, 30), 
                fillColor: "#dc3545", 
                color: "#fff", 
                weight: 1, 
                fillOpacity: 0.6 
            }).addTo(markerGroup).bindPopup(`<b>${spot.city}</b>: ${spot.count} Deliveries`);
            bounds.push([spot.lat, spot.lng]);

            // 2. ONLY SHOW BLUE PIN FOR THE TOP CITY
            // We check if this specific red dot city matches the topCity name
            if (spot.city === data.topCity) {
                L.marker([spot.lat, spot.lng], { icon: blueIcon })
                    .bindPopup(`<b>🏆 Top Performer: ${spot.city}</b>`)
                    .addTo(markerGroup);
            }
        }
    });

    // View Control
    if (bounds.length > 0 && type === 'local') {
        map.fitBounds(bounds, { padding: [50, 50], maxZoom: 12 });
    } else {
        map.setView(data.center, data.zoom);
    }

    // Button Styling
    if (event) {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('btn-dark');
            btn.classList.add('btn-outline-dark');
        });
        event.target.classList.remove('btn-outline-dark');
        event.target.classList.add('btn-dark');
    }
};

    // 2. City Revenue Donut
    const revenueCtx = document.getElementById('cityRevenueChart').getContext('2d');
    cityRevenueChart = new Chart(revenueCtx, {
        type: 'doughnut',
        data: { labels: [], datasets: [{ data: [], backgroundColor: ['#1ce626', '#0f44d6', '#d44a58', '#d6c633', '#fd14e2'], borderWidth: 0 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } } } }
    });

    // 3. Original Revenue Growth Chart (Preserving your logic/gradients)
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    const gradSales = revCtx.createLinearGradient(0, 0, 0, 400);
    gradSales.addColorStop(0, 'rgba(59, 130, 246, 0.4)'); gradSales.addColorStop(1, 'rgba(59, 130, 246, 0)');
    const gradRev = revCtx.createLinearGradient(0, 0, 0, 400);
    gradRev.addColorStop(0, 'rgba(255, 138, 0, 0.4)'); gradRev.addColorStop(1, 'rgba(255, 138, 0, 0)');

    revenueChart = new Chart(revCtx, {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [
                { label: 'Total Sales', data: dailySalesData, borderColor: '#3b82f6', backgroundColor: gradSales, fill: true, tension: 0.4, pointRadius: 4, pointBackgroundColor: '#fff', pointBorderColor: '#3b82f6', pointBorderWidth: 2 },
                { label: 'Total Revenue', data: dailyRevenueData, borderColor: '#ff8a00', backgroundColor: gradRev, fill: true, tension: 0.4, pointRadius: 4, pointBackgroundColor: '#fff', pointBorderColor: '#ff8a00', pointBorderWidth: 2 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { usePointStyle: true, font: { weight: 'bold' } } } } }
    });

   // 4. Category Market Share Chart
new Chart(document.getElementById('categoryRoundChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($topCategories->pluck('name')->toArray()) !!},
        datasets: [{
            data: {!! json_encode($topCategories->pluck('share_percentage')->toArray()) !!},
            backgroundColor: ['#f5a2a4', '#ebb1f2', '#aeb5f2', '#a7f2b4', '#fde68a'],
            borderWidth: 0,
            cutout: '80%'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                enabled: true,
                callbacks: {
                    label: function(context) {
                        return ' ' + context.label + ': ' + context.raw.toFixed(1) + '%';
                    }
                }
            }
        }
    }
});

    // 5. Original Order Status Bar
    new Chart(document.getElementById('orderStatusBarChart'), {
        type: 'bar',
        data: { labels: @json(array_keys($statusCounts)), datasets: [{ data: @json(array_values($statusCounts)), backgroundColor: ['#f5e216', '#3b82f6', '#16a395', '#f07d41', '#1bd173', '#dc2626', '#585d61'], borderRadius: 5 }] },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    setTimeout(() => { map.invalidateSize(); updateDashboardView('local'); }, 400);
});

function updateChart(type) {
    if (type === 'days') {
        revenueChart.data.labels = dailyLabels; revenueChart.data.datasets[0].data = dailySalesData; revenueChart.data.datasets[1].data = dailyRevenueData;
        document.getElementById('btnDays').classList.add('active'); document.getElementById('btnMonths').classList.remove('active');
    } else if (monthlyLabels.length > 0) {
        revenueChart.data.labels = monthlyLabels; revenueChart.data.datasets[0].data = monthlySalesData; revenueChart.data.datasets[1].data = monthlyRevenueData;
        document.getElementById('btnMonths').classList.add('active'); document.getElementById('btnDays').classList.remove('active');
    }
    revenueChart.update();
}
</script>
@endsection