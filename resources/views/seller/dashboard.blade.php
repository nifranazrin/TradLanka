@extends('layouts.seller')

@section('content')
<div class="container-fluid p-4">
    
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            @php
                $firstName = explode(' ', Auth::guard('seller')->user()->name)[0];
            @endphp
            <h1 class="fw-bold mb-1" style="font-size: 2.2rem;">Welcome back, {{ $firstName }}</h1>
            <h5 class="text-muted fw-normal">Seller Dashboard Overview</h5>
        </div>
       <div class="col-12 col-md-6 text-md-end">
            <span class="badge bg-white text-dark border p-2 rounded-pill px-3 fw-bold shadow-sm">
                <i class="bi bi-calendar3 text-primary me-2"></i> {{ now()->format('l, F d, Y') }}
            </span>
        </div>
    </div>

    {{-- Colorful Gradient Summary Cards --}}
    <div class="row g-4 mb-5">
        {{-- 1. Orders Today with Currency Split --}}
<div class="col-md-3">
    <div class="card border-0 shadow h-100" style="border-radius: 20px; background: linear-gradient(45deg, #1cc88a, #13855c);">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="text-white-50 small text-uppercase fw-extrabold mb-2" style="letter-spacing: 1px;">Orders Today</h6>
                    <h2 class="fw-bold text-white mb-0" id="stat-orders-today" style="font-size: 2.5rem;">{{ $ordersToday }}</h2>
                </div>
                {{-- FIXED ICON: Success Color on solid White Circle --}}
                <div class="bg-white rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px;">
                    <i class="bi bi-cart-check fs-2 text-success"></i>
                </div>
            </div>

            {{-- Today's Currency Split Footer --}}
            <div class="pt-3 border-top border-white border-opacity-25 d-flex justify-content-between">
                <div class="text-center w-50">
                    <h4 class="fw-bold mb-0" id="stat-orders-today-local" style="font-size: 1.4rem;">{{ $ordersTodayLocal ?? 0 }}</h4>
                    <div class="text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem;">LKR</div>
                </div>
                <div class="vr bg-white bg-opacity-25"></div>
                <div class="text-center w-50">
                    <h4 class="fw-bold mb-0" id="stat-orders-today-foreign" style="font-size: 1.4rem;">{{ $ordersTodayForeign ?? 0 }}</h4>
                    <div class="text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem;">USD</div>
                </div>
            </div>
        </div>
    </div>
</div>

        
        {{-- 2. Total Products with Approval Breakdown --}}
        <div class="col-md-3">
            <div class="card border-0 shadow h-100" style="border-radius: 20px; background: linear-gradient(45deg, #4e73df, #224abe);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="text-white-50 small text-uppercase fw-extrabold mb-2" style="letter-spacing: 1px;">Total Products</h6>
                            <h2 class="fw-bold text-white mb-0" id="stat-total-products" style="font-size: 2.5rem;">{{ $totalProducts }}</h2>
                        </div>
                        {{-- FIXED ICON: Primary Color on solid White Circle for maximum visibility --}}
                        <div class="bg-white rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px;">
                            <i class="bi bi-box-seam fs-2 text-primary"></i> 
                        </div>
                    </div>

                    {{-- Approval Breakdown Footer --}}
                    <div class="pt-3 border-top border-white border-opacity-25 d-flex justify-content-between">
                        <div class="text-center w-50">
                            <h4 class="fw-bold mb-0" id="stat-approved-products" style="font-size: 1.4rem;">{{ $approvedProducts ?? 0 }}</h4>
                            <div class="text-white-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Approved</div>
                        </div>
                        <div class="vr bg-white bg-opacity-25"></div>
                        <div class="text-center w-50">
                            <h4 class="fw-bold mb-0" id="stat-rejected-products" style="font-size: 1.4rem;">{{ $rejectedProducts ?? 0 }}</h4>
                            <div class="text-white-50 text-uppercase fw-bold" style="font-size: 0.7rem;">Rejected</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Pending Deliveries with Currency Split --}}
        <div class="col-md-3">
            <div class="card border-0 shadow h-100" style="border-radius: 20px; background: linear-gradient(45deg, #e74a3b, #be2617);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="text-white-50 small text-uppercase fw-extrabold mb-1" style="letter-spacing: 1px;">Pending</h6>
                            <h2 class="fw-bold text-white mb-0" id="stat-pending" style="font-size: 2.5rem;">{{ $pendingDeliveries }}</h2>
                        </div>
                        {{-- FIXED ICON: Danger Color on solid White Circle --}}
                        <div class="bg-white rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px;">
                            <i class="bi bi-clock-history fs-2 text-danger"></i>
                        </div>
                    </div>
                    <div class="pt-3 border-top border-white border-opacity-25 d-flex justify-content-between">
                        <div class="text-center w-50">
                            <h4 class="fw-bold mb-0" id="stat-pending-local" style="font-size: 1.4rem;">{{ $pendingLocal ?? 0 }}</h4>
                            <div class="text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem;">LKR</div>
                        </div>
                        <div class="vr bg-white bg-opacity-25"></div>
                        <div class="text-center w-50">
                            <h4 class="fw-bold mb-0" id="stat-pending-foreign" style="font-size: 1.4rem;">{{ $pendingForeign ?? 0 }}</h4>
                            <div class="text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem;">USD</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Total Orders with Currency Split --}}
        <div class="col-md-3">
            <div class="card border-0 shadow h-100" style="border-radius: 20px; background: linear-gradient(45deg, #f6c23e, #dda20a);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="text-white-50 small text-uppercase fw-extrabold mb-1" style="letter-spacing: 1px;">Total Orders</h6>
                            <h2 class="fw-bold text-white mb-0" id="stat-total-orders" style="font-size: 2.5rem;">{{ $totalOrders }}</h2>
                        </div>
                        {{-- FIXED ICON: Warning Color on solid White Circle --}}
                        <div class="bg-white rounded-circle p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px;">
                            <i class="bi bi-bag-check fs-2 text-warning"></i>
                        </div>
                    </div>
                    <div class="pt-3 border-top border-white border-opacity-25 d-flex justify-content-between">
                        <div class="text-center w-50">
                            <h4 class="fw-bold mb-0" id="stat-local-orders" style="font-size: 1.4rem;">{{ $localOrders ?? 0 }}</h4>
                            <div class="text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem;">LKR</div>
                        </div>
                        <div class="vr bg-white bg-opacity-25"></div>
                        <div class="text-center w-50">
                            <h4 class="fw-bold mb-0" id="stat-foreign-orders" style="font-size: 1.4rem;">{{ $foreignOrders ?? 0 }}</h4>
                            <div class="text-white-50 text-uppercase fw-bold" style="font-size: 0.75rem;">USD</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sale Performance Triple Chart & Top Selling Section --}}
    <div class="row mb-5 g-4">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Sale Performance</h5>
                        <div class="btn-group shadow-sm" role="group" id="chartToggle">
                            <button type="button" class="btn btn-primary btn-sm px-3 active" data-view="8days">Last 8 Days</button>
                            <button type="button" class="btn btn-outline-primary btn-sm px-3" data-view="monthly">Monthly</button>
                        </div>
                    </div>
                    <div style="height: 350px;"><canvas id="salesLineChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Top Selling Products</h5>
                    <div class="list-group list-group-flush">
                        @forelse($topProducts as $tp)
                            <div class="list-group-item px-0 border-0 d-flex align-items-center gap-3 mb-3">
                                <img src="{{ asset('storage/'.$tp->image) }}" class="rounded shadow-sm" width="55" height="55" style="object-fit: cover;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;">{{ Str::limit($tp->name, 25) }}</h6>
                                    <span class="text-muted small">{{ $tp->total_sold ?? 0 }} units sold</span>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">#{{ $loop->iteration }}</span>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">No sales data yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Orders Table Section --}}
<div class="row g-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0" style="border-radius: 20px;">
            <div class="card-body p-4">
                {{-- Card Header --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Recent Orders</h5>
                        <p class="text-muted small mb-0">Overview of the latest customer activity</p>
                    </div>
                    <a href="{{ route('seller.orders.index') }}" class="btn btn-light text-primary fw-bold px-4 py-2 rounded-pill shadow-sm border">
                        View All Orders <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                {{-- Table Section --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-secondary small fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                                <th class="border-0 py-3 ps-4">Tracking No</th>
                                <th class="border-0 py-3">Customer</th>
                                <th class="border-0 py-3">Amount</th>
                                <th class="border-0 py-3 text-center">Status</th>
                                <th class="border-0 py-3 pe-4 text-end">Date</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($recentOrders as $order)
                                <tr style="transition: all 0.2s ease;">
                                    <td class="py-3 ps-4">
                                        <span class="fw-bold text-primary">#{{ $order->tracking_no }}</span>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2 bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <span class="text-primary small fw-bold">{{ strtoupper(substr($order->fname, 0, 1)) }}</span>
                                            </div>
                                            <span class="fw-semibold text-dark">{{ $order->fname }} {{ $order->lname }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="text-muted small fw-bold">{{ $order->currency }}</span> 
                                        <span class="fw-bold text-dark">{{ number_format($order->total_price, 2) }}</span>
                                    </td>
                                    <td class="py-3 text-center">
                                        @if($order->status == 5)
                                            <span class="badge rounded-pill px-3 py-2 text-success border border-success border-opacity-25" style="background: rgba(28, 200, 138, 0.1); font-size: 0.75rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i> Delivered
                                            </span>
                                        @elseif($order->status == 6)
                                            <span class="badge rounded-pill px-3 py-2 text-danger border border-danger border-opacity-25" style="background: rgba(231, 74, 59, 0.1); font-size: 0.75rem;">
                                                <i class="bi bi-x-circle-fill me-1"></i> Canceled
                                            </span>
                                        @elseif($order->status == 0)
                                            <span class="badge rounded-pill px-3 py-2 text-primary border border-primary border-opacity-25" style="background: rgba(78, 115, 223, 0.1); font-size: 0.75rem;">
                                                <i class="bi bi-clock-history me-1"></i> Pending
                                            </span>
                                        @else
                                            <span class="badge rounded-pill px-3 py-2 text-info border border-info border-opacity-25" style="background: rgba(54, 185, 204, 0.1); font-size: 0.75rem;">
                                                <i class="bi bi-gear-fill me-1"></i> Processing
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 pe-4 text-end">
                                        <span class="text-muted small fw-medium">{{ $order->created_at->format('M d, Y') }}</span>
                                        <div class="text-muted" style="font-size: 0.65rem;">{{ $order->created_at->format('h:i A') }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                            No recent orders found.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let lineChart;
    let currentView = '8days';

    function initCharts(data) {
        const ctxLine = document.getElementById('salesLineChart').getContext('2d');
        lineChart = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: data.line.labels,
                datasets: [
                    { 
                        label: 'Total Orders', 
                        data: data.line.total, 
                        borderColor: '#4e73df', 
                        backgroundColor: 'rgba(78, 115, 223, 0.05)', 
                        fill: true, tension: 0.4, pointRadius: 4 
                    },
                    { 
                        label: 'Delivered', 
                        data: data.line.success, 
                        borderColor: '#1cc88a', 
                        backgroundColor: 'transparent', 
                        fill: false, tension: 0.4, pointRadius: 4 
                    },
                    { 
                        label: 'Canceled', 
                        data: data.line.canceled, 
                        borderColor: '#e74a3b', 
                        backgroundColor: 'transparent', 
                        fill: false, tension: 0.4, pointRadius: 4 
                    }
                ]
            },
            options: { 
                maintainAspectRatio: false, 
                plugins: { legend: { position: 'top', labels: { usePointStyle: true } } },
                scales: { 
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } } 
                }
            }
        });
    }

    function refreshDashboard() {
        fetch(`{{ route('seller.dashboard.chart-data') }}?view=${currentView}`)
            .then(res => res.json())
            .then(data => {
                // Update Product Stats
            document.getElementById('stat-orders-today').innerText = data.ordersToday;
            document.getElementById('stat-orders-today-local').innerText = data.ordersTodayLocal;
            document.getElementById('stat-orders-today-foreign').innerText = data.ordersTodayForeign;

                // Update Order Stats
                document.getElementById('stat-orders-today').innerText = data.ordersToday;
                document.getElementById('stat-pending').innerText = data.pendingDeliveries;
                document.getElementById('stat-pending-local').innerText = data.pendingLocal;
                document.getElementById('stat-pending-foreign').innerText = data.pendingForeign;
                document.getElementById('stat-total-orders').innerText = data.totalOrders;
                document.getElementById('stat-local-orders').innerText = data.localOrders;
                document.getElementById('stat-foreign-orders').innerText = data.foreignOrders;

                if (!lineChart) {
                    initCharts(data);
                } else {
                    lineChart.data.labels = data.line.labels;
                    lineChart.data.datasets[0].data = data.line.total;
                    lineChart.data.datasets[1].data = data.line.success;
                    lineChart.data.datasets[2].data = data.line.canceled;
                    lineChart.update();
                }
            });
    }

    // Toggle View Script
    document.querySelectorAll('#chartToggle button').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#chartToggle button').forEach(b => {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-primary');
            });
            this.classList.add('btn-primary', 'active');
            this.classList.remove('btn-outline-primary');
            currentView = this.dataset.view;
            refreshDashboard();
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        refreshDashboard();
        setInterval(refreshDashboard, 60000); 
    });
</script>

<style>
    .fw-extrabold { font-weight: 800; }
    .card { transition: transform 0.2s ease-in-out; border-radius: 15px; }
    .card:hover { transform: translateY(-5px); }
    .vr { width: 2px; }
    /* Icon alignment helper */
    .bi { display: inline-flex; align-items: center; justify-content: center; }

    .table tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.02);
        transform: scale(1.002);
    }
    .avatar-sm {
        flex-shrink: 0;
    }
</style>
@endsection