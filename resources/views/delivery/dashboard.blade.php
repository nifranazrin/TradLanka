@extends('layouts.delivery')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    :root {
        --primary-dark: #5b2c2c;
        --success-color: #198754;
        --pending-color: #0d6efd;
        --bg-light: #f8f9fa;
        --glass-white: rgba(255, 255, 255, 0.95);
    }

    .dashboard-wrapper {
        background-color: var(--bg-light);
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
        padding-bottom: 2rem;
    }

    .fin-card {
        background: #fff;
        border-radius: 20px;
        transition: transform 0.3s ease;
        padding: 1.5rem;
    }
    .fin-card:hover { transform: translateY(-5px); }

    .icon-box {
        width: 50px;
        height: 50px;
        border-radius: 15px;
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
        <div class="row align-items-center mb-5">
            <div class="col-12 col-md-6">
                @php
                    $firstName = explode(' ', Auth::guard('delivery')->user()->name)[0];
                @endphp
                <h1 class="fw-bold mb-1" style="font-size: 2.2rem;">Welcome back, {{ $firstName }}</h1>
                <h5 class="text-muted fw-normal">Delivery Dashboard Overview</h5>
            </div>
            <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
                <span class="badge bg-white text-dark border p-2 rounded-pill px-3 fw-bold shadow-sm">
                    <i class="bi bi-calendar3 text-primary me-2"></i> {{ now()->format('l, F d, Y') }}
                </span>
            </div>
        </div>

        {{-- Financial Overview Card --}}
        <div class="card border-0 shadow-sm mb-4 p-4" style="border-radius: 24px; background: #ffffff;">
            <div class="mb-4 px-2">
                <h5 class="fw-bold m-0 text-premium">Financial Overview</h5>
                <small class="text-muted">Real-time COD tracking by status</small>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="fin-card border-0 shadow-lg h-100 position-relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #198754 0%, #146c43 100%); border-radius: 24px;">
                        <div class="d-flex align-items-center p-3 position-relative">
                            <div class="icon-box bg-white text-success me-3 shadow">
                                <i class="bi bi-cash-coin fs-3"></i>
                            </div>
                            <div>
                                <div class="px-2 py-0 rounded-pill bg-white bg-opacity-10 d-inline-block mb-1">
                                    <span class="text-white small text-uppercase fw-bold">Delivered COD Total</span>
                                </div>
                                <h4 class="fw-bold m-0 text-white mt-1">Rs. {{ number_format($deliveredCodTotal ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="fin-card border-0 shadow-lg h-100 position-relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border-radius: 24px;">
                        <div class="d-flex align-items-center p-3 position-relative">
                            <div class="icon-box bg-white text-primary me-3 shadow">
                                <i class="bi bi-clock-history fs-3"></i>
                            </div>
                            <div>
                                <div class="px-2 py-0 rounded-pill bg-white bg-opacity-10 d-inline-block mb-1">
                                    <span class="text-white small text-uppercase fw-bold">Pending COD (Active)</span>
                                </div>
                                <h4 class="fw-bold m-0 text-white mt-1">Rs. {{ number_format($pendingCodTotal ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="fin-card border-0 shadow-lg h-100 position-relative overflow-hidden" 
                         style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); border-radius: 24px;">
                        <div class="d-flex align-items-center p-3 position-relative">
                            <div class="icon-box bg-white text-danger me-3 shadow">
                                <i class="bi bi-x-circle fs-3"></i>
                            </div>
                            <div>
                                <div class="px-2 py-0 rounded-pill bg-white bg-opacity-10 d-inline-block mb-1">
                                    <span class="text-white small text-uppercase fw-bold">Cancelled COD Total</span>
                                </div>
                                <h4 class="fw-bold m-0 text-white mt-1">Rs. {{ number_format($cancelledCodTotal ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Geographic Performance --}}
        <div class="row g-4 mb-5">
            <div class="col-12 col-lg-8">
                <div class="table-box p-0 overflow-hidden" style="border-radius: 24px; position: relative; background: #fff;">
                    <div class="p-4 bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold m-0 text-premium">
                            <i class="bi bi-geo-fill text-danger me-2"></i>Delivery Hotspots
                        </h6>
                        {{-- Toggle Filter --}}
                        <div class="btn-group btn-group-sm p-1 bg-light rounded-pill" role="group">
                            <button type="button" class="btn btn-dark rounded-pill px-3 filter-btn active" onclick="updateDashboardView('local', event)">Local</button>
                            <button type="button" class="btn btn-outline-dark border-0 rounded-pill px-3 filter-btn" onclick="updateDashboardView('international', event)">International</button>
                        </div>
                    </div>
                    <div id="riderMap" style="height: 400px; width: 100%; background: #f8f9fa;"></div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="table-box h-100 p-4" style="background: #fff; border-radius: 24px;">
                    <h6 class="fw-bold mb-4 text-premium">
                        <i class="bi bi-cash-stack text-primary me-2"></i>Revenue by City
                    </h6>
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="cityRevenueChart"></canvas>
                    </div>

                    {{-- Dynamic Top Performer Card --}}
                    <div id="topPerformerCard" class="mt-4 p-3 rounded-4 border bg-light">
                        <div class="text-center">
                            <small id="performerLabel" class="text-muted d-block mb-1">Top Local (LKR)</small>
                            <h4 id="performerCity" class="fw-bold text-dark mb-1">{{ $topLocalCity->city ?? 'N/A' }}</h4>
                            <span id="performerAmount" class="badge bg-primary rounded-pill px-3">
                                {{ number_format($topLocalCity->total_amount ?? 0, 0) }} LKR
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Row --}}
        <div class="row g-4 mb-4 text-center">
            @php
                $statsConfig = [
                    ['title' => 'ACTIVE TASKS', 'today' => $pendingToday ?? 0, 'total' => $pendingTotal ?? 0, 'color' => '#ffc107', 'icon' => 'bi-hourglass-split'],
                    ['title' => 'DELIVERED', 'today' => $deliveredToday ?? 0, 'total' => $deliveredTotal ?? 0, 'color' => '#198754', 'icon' => 'bi-check2-all'],
                    ['title' => 'FAILED/CLOSED', 'today' => $failedToday ?? 0, 'total' => $failedTotal ?? 0, 'color' => '#dc3545', 'icon' => 'bi-x-circle']
                ];
            @endphp
            @foreach($statsConfig as $s)
            <div class="col-12 col-md-4">
                <div class="card stat-card border-0 shadow-sm p-3">
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

        {{-- Main Charts --}}
        <div class="row g-4 mb-5">
            <div class="col-12 col-lg-4">
                <div class="table-box h-100">
                    <h6 class="fw-bold mb-4 text-center text-premium">Today's Distribution</h6>
                    <div class="chart-container">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-8">
                <div class="table-box h-100">
                    <h6 class="fw-bold mb-4 text-premium">Performance Trend (Monthly)</h6>
                    <div class="chart-container">
                        <canvas id="lineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tasks Table --}}
        <div class="table-box mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0 text-premium">Recent Assigned Tasks</h5>
                <a href="{{ route('delivery.my-deliveries') }}" class="btn btn-sm btn-dark rounded-pill px-4 shadow-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr class="text-muted small text-uppercase">
                            <th class="ps-3">Tracking</th>
                            <th>Customer</th>
                            <th>City</th>
                            <th class="text-center">Amount</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeDeliveries ?? [] as $order)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">#{{ $order->tracking_no }}</td>
                            <td class="fw-bold">{{ $order->fname }} {{ $order->lname }}</td>
                            <td>{{ $order->city }}</td>
                            <td class="text-center fw-bold">{{ $order->currency }} {{ number_format($order->total_price, 2) }}</td>
                            <td class="text-end pe-3">
                                <a href="{{ route('delivery.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">Manage</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted small">No active assignments found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- 1. DATA PREPARATION ---
        const localData = {
            chartLabels: {!! json_encode(collect($topCitiesRevenue)->where('currency', 'LKR')->pluck('city')) !!},
            chartValues: {!! json_encode(collect($topCitiesRevenue)->where('currency', 'LKR')->pluck('total_amount')) !!},
            markers: {!! json_encode(collect($activeDeliveries)->where('currency', 'LKR')->values()) !!},
            hotspots: {!! json_encode(collect($deliveryHotspots)->where('currency', 'LKR')->values() ?? []) !!},
            topCity: "{{ $topLocalCity->city ?? 'N/A' }}",
            topAmount: "{{ number_format($topLocalCity->total_amount ?? 0, 0) }} LKR",
            center: [7.8731, 80.7718],
            zoom: 7.5
        };

        const internationalData = {
            chartLabels: {!! json_encode(collect($topCitiesRevenue)->where('currency', 'USD')->pluck('city')) !!},
            chartValues: {!! json_encode(collect($topCitiesRevenue)->where('currency', 'USD')->pluck('total_amount')) !!},
            markers: {!! json_encode(collect($activeDeliveries)->where('currency', 'USD')->values() ?? []) !!},
            hotspots: {!! json_encode(collect($deliveryHotspots)->where('currency', 'USD')->values() ?? []) !!},
            topCity: "{{ $topInternationalCity->city ?? 'N/A' }}",
            topAmount: "${{ number_format($topInternationalCity->total_amount ?? 0, 2) }} USD",
            center: [20, 0],
            zoom: 2
        };

        // --- 2. INITIALIZE CHARTS ---
        // Distribution Chart
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Delivered', 'Active', 'Failed'],
                datasets: [{
                    data: [{{ $deliveredToday }}, {{ $pendingToday }}, {{ $failedToday }}],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '80%' }
        });

        // Trend Chart
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dates ?? []) !!},
                datasets: [
                    { label: 'Success', data: {!! json_encode($deliveredSeries ?? []) !!}, borderColor: '#198754', tension: 0.4 },
                    { label: 'Failed', data: {!! json_encode($failedSeries ?? []) !!}, borderColor: '#dc3545', tension: 0.4 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Dynamic Revenue Chart
        const revenueCtx = document.getElementById('cityRevenueChart').getContext('2d');
        let revenueChart = new Chart(revenueCtx, {
            type: 'doughnut',
            data: {
                labels: localData.chartLabels,
                datasets: [{
                    data: localData.chartValues,
                    backgroundColor: ['#1ce626', '#0f44d6', '#d44a58', '#d6c633', '#fd14e2'],
                    borderWidth: 0
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                cutout: '70%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }
            }
        });

        // --- 3. INITIALIZE MAP ---
        const map = L.map('riderMap').setView(localData.center, localData.zoom);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png', {
            attribution: '© TradLanka | CARTO',
            subdomains: 'abcd'
        }).addTo(map);

        const markerGroup = L.layerGroup().addTo(map);
        const smallBlueIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
            iconSize: [15, 25], iconAnchor: [7, 25], popupAnchor: [1, -25]
        });

        // --- 4. GLOBAL FILTER FUNCTION ---
       window.updateDashboardView = function(type, event) {
            const data = (type === 'local') ? localData : internationalData;

            // Update Chart
            revenueChart.data.labels = data.chartLabels;
            revenueChart.data.datasets[0].data = data.chartValues;
            revenueChart.update();

            // Clear and Redraw Map
            markerGroup.clearLayers();
            let bounds = [];

            // Draw Blue Pins for Active Tasks
            data.markers.forEach(order => {
                if (order.latitude && order.longitude) {
                    L.marker([order.latitude, order.longitude], { icon: smallBlueIcon })
                        .bindPopup(`<b>#${order.tracking_no}</b><br>${order.city}`)
                        .addTo(markerGroup);
                    bounds.push([order.latitude, order.longitude]);
                }
            });

            // Add Hotspot Circles (Red)
             data.hotspots.forEach(spot => {
                if (spot.lat && spot.lng) {
                    L.circleMarker([spot.lat, spot.lng], {
                        radius: Math.min(spot.count * 8, 30),
                        fillColor: "#dc3545", color: "#fff", weight: 1, fillOpacity: 0.6
                    }).addTo(markerGroup).bindPopup(`<b>${spot.city}</b>: ${spot.count} Deliveries`);
                    bounds.push([spot.lat, spot.lng]);
                }
            });

            // Auto-focus the map on available data
            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50], maxZoom: 12 });
            } else {
                map.setView(data.center, data.zoom);
            }

            // Update Revenue Card UI
            document.getElementById('performerCity').innerText = data.topCity;
            document.getElementById('performerAmount').innerText = data.topAmount;
            document.getElementById('performerLabel').innerText = type === 'local' ? 'Top Local (LKR)' : 'Top International (USD)';

            // Toggle Button Active Classes
            if (event) {
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('btn-dark');
                    btn.classList.add('btn-outline-dark');
                });
                event.target.classList.remove('btn-outline-dark');
                event.target.classList.add('btn-dark');
            }
        };

        // Initialize view on load
        setTimeout(function() { 
            map.invalidateSize();
            updateDashboardView('local');
        }, 400);
    });

</script>
@endsection