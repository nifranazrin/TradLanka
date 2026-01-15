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


    
{{-- Fully Corrected 5-Card Single Row with Larger Numbers --}}
<div class="row row-cols-1 row-cols-md-5 g-3 mb-5">
    
    {{-- 1. Orders Today --}}
    <div class="col">
        <div class="card border-0 shadow h-100" style="border-radius: 15px; background: linear-gradient(45deg, #1cc88a, #13855c);">
            <div class="card-body p-3 text-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="text-white-51 small text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Orders Today</h6>
                        {{-- Enlarged Main Number --}}
                        <h3 class="fw-bold text-white mb-0" id="stat-orders-today" style="font-size: 2.2rem;">{{ $ordersToday }}</h3>
                    </div>
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; flex-shrink: 0;">
                        <i class="bi bi-cart-check fs-4 text-success"></i>
                    </div>
                </div>
                <div class="pt-2 border-top border-white border-opacity-25 d-flex justify-content-between align-items-end">
                    <div class="text-center w-50">
                        {{-- Enlarged Currency Number --}}
                        <div class="fw-bold" id="stat-orders-today-local" style="font-size: 1.1rem;">{{ $ordersTodayLocal ?? 0 }}</div>
                        <div class="text-white-50 small" style="font-size: 0.85rem;">LKR</div>
                    </div>
                    <div class="vr bg-white bg-opacity-25" style="width: 1px; height: 25px;"></div>
                    <div class="text-center w-50">
                        <div class="fw-bold" id="stat-orders-today-foreign" style="font-size: 1.1rem;">{{ $ordersTodayForeign ?? 0 }}</div>
                        <div class="text-white-50 small" style="font-size: 0.85rem;">USD</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Total Products --}}
    <div class="col">
        <div class="card border-0 shadow h-100" style="border-radius: 15px; background: linear-gradient(45deg, #4e73df, #224abe);">
            <div class="card-body p-3 text-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="text-white-51 small text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Total Products</h6>
                        <h3 class="fw-bold text-white mb-0" id="stat-total-products" style="font-size: 2.2rem;">{{ $totalProducts }}</h3>
                    </div>
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; flex-shrink: 0;">
                        <i class="bi bi-box-seam fs-4 text-primary"></i>
                    </div>
                </div>
                <div class="pt-2 border-top border-white border-opacity-25 d-flex justify-content-between align-items-end">
                    <div class="text-center w-50">
                        <div class="fw-bold" id="stat-approved-products" style="font-size: 1.1rem;">{{ $approvedProducts ?? 0 }}</div>
                        <div class="text-white-50 small" style="font-size: 0.85rem;">Approved</div>
                    </div>
                    <div class="vr bg-white bg-opacity-25" style="width: 1px; height: 25px;"></div>
                    <div class="text-center w-50">
                        <div class="fw-bold" id="stat-rejected-products" style="font-size: 1.1rem;">{{ $rejectedProducts ?? 0 }}</div>
                        <div class="text-white-50 small" style="font-size: 0.85rem;">Reject</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Pending Deliveries --}}
    <div class="col">
        <div class="card border-0 shadow h-100" style="border-radius: 15px; background: linear-gradient(45deg, #e74a3b, #be2617);">
            <div class="card-body p-3 text-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="text-white-51 small text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Pending</h6>
                        <h3 class="fw-bold text-white mb-0" id="stat-pending" style="font-size: 2.2rem;">{{ $pendingDeliveries }}</h3>
                    </div>
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; flex-shrink: 0;">
                        <i class="bi bi-clock-history fs-4 text-danger"></i>
                    </div>
                </div>
                <div class="pt-2 border-top border-white border-opacity-25 d-flex justify-content-between align-items-end">
                    <div class="text-center w-50">
                        <div class="fw-bold" id="stat-pending-local" style="font-size: 1.1rem;">{{ $pendingLocal ?? 0 }}</div>
                        <div class="text-white-50 small" style="font-size: 0.85rem;">LKR</div>
                    </div>
                    <div class="vr bg-white bg-opacity-25" style="width: 1px; height: 25px;"></div>
                    <div class="text-center w-50">
                        <div class="fw-bold" id="stat-pending-foreign" style="font-size: 1.1rem;">{{ $pendingForeign ?? 0 }}</div>
                        <div class="text-white-50 small" style="font-size: 0.85rem;">USD</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Total Orders --}}
    <div class="col">
        <div class="card border-0 shadow h-100" style="border-radius: 15px; background: linear-gradient(45deg, #f6c23e, #dda20a);">
            <div class="card-body p-3 text-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="text-white-51 small text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Total Orders</h6>
                        <h3 class="fw-bold text-white mb-0" id="stat-total-orders" style="font-size: 2.2rem;">{{ $totalOrders }}</h3>
                    </div>
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; flex-shrink: 0;">
                        <i class="bi bi-bag-check fs-4 text-warning"></i>
                    </div>
                </div>
                <div class="pt-2 border-top border-white border-opacity-25 d-flex justify-content-between align-items-end">
                    <div class="text-center w-50">
                        <div class="fw-bold" id="stat-local-orders" style="font-size: 1.1rem;">{{ $localOrders ?? 0 }}</div>
                        <div class="text-white-50 small" style="font-size: 0.85rem;">LKR</div>
                    </div>
                    <div class="vr bg-white bg-opacity-25" style="width: 1px; height: 25px;"></div>
                    <div class="text-center w-50">
                        <div class="fw-bold" id="stat-foreign-orders" style="font-size: 1.1rem;">{{ $foreignOrders ?? 0 }}</div>
                        <div class="text-white-50 small" style="font-size: 0.85rem;">USD</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Financials Card --}}
<div class="col">
    <div class="card border-0 shadow h-100" style="border-radius: 15px; background: linear-gradient(45deg, #6e2727, #4a1a1a);">
        <div class="card-body p-3 text-white">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h6 class="text-white-51 small text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 0.5px;">Financials</h6>
                </div>
                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px; flex-shrink: 0;">
                    <i class="bi bi-cash-stack fs-6" style="color: #6e2727;"></i>
                </div>
            </div>

            <div class="pt-1">
                {{-- Success Section (Top Half) --}}
                <div class="pb-2">
                    <div class="text-white-50 fw-bold small text-uppercase mb-0" style="font-size: 0.55rem;">Success</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold" style="font-size: 1.1rem;">LKR <span id="stat-success-lkr">{{ number_format($successRevenueLKR, 2) }}</span></span>
                        <span class="fw-bold text-white-50 text-end" style="font-size: 0.9rem;">USD <span id="stat-success-usd">{{ number_format($successRevenueUSD, 2) }}</span></span>
                    </div>
                </div>

                {{-- Separating Line between Success and Canceled --}}
                <div class="pt-2 mt-2 border-top border-white border-opacity-10">
                    {{-- Canceled Section (Bottom Half) --}}
                    <div class="text-warning fw-bold small text-uppercase mb-0" style="font-size: 0.55rem; opacity: 0.8;">Canceled</div>
                    <div class="d-flex justify-content-between align-items-center text-warning">
                        <span class="fw-bold" style="font-size: 1.1rem;">LKR <span id="stat-canceled-lkr">{{ number_format($canceledRevenueLKR, 2) }}</span></span>
                        <span class="fw-bold opacity-75 text-end" style="font-size: 0.9rem;">USD <span id="stat-canceled-usd">{{ number_format($canceledRevenueUSD, 2) }}</span></span>
                    </div>
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
                const formatter = new Intl.NumberFormat('en-US');

                // Function to safely update text if element exists
                const safeUpdate = (id, value) => {
                    const el = document.getElementById(id);
                    if (el) el.innerText = value;
                };

                // --- 1. Standard Stats ---
                safeUpdate('stat-orders-today', data.ordersToday);
                safeUpdate('stat-orders-today-local', data.ordersTodayLocal);
                safeUpdate('stat-orders-today-foreign', data.ordersTodayForeign);

                safeUpdate('stat-total-products', data.totalProducts);
                safeUpdate('stat-approved-products', data.approvedProducts);
                safeUpdate('stat-rejected-products', data.rejectedProducts);

                safeUpdate('stat-pending', data.pendingDeliveries);
                safeUpdate('stat-pending-local', data.pendingLocal);
                safeUpdate('stat-pending-foreign', data.pendingForeign);

                safeUpdate('stat-total-orders', data.totalOrders);
                safeUpdate('stat-local-orders', data.localOrders);
                safeUpdate('stat-foreign-orders', data.foreignOrders);

                // --- 2. Revenue Card (Success vs Canceled) ---
                // Removed the "Estimated Total" update to prevent crashes
                
                safeUpdate('stat-success-lkr', formatter.format(data.successRevenueLKR));
                safeUpdate('stat-success-usd', parseFloat(data.successRevenueUSD).toFixed(2));
                safeUpdate('stat-canceled-lkr', formatter.format(data.canceledRevenueLKR));
                safeUpdate('stat-canceled-usd', parseFloat(data.canceledRevenueUSD).toFixed(2));

                // --- 3. Chart Updates (Sale Performance) ---
                // CRITICAL: This must be inside the .then block
                const canvas = document.getElementById('salesLineChart');
                if (canvas) {
                    if (!lineChart) {
                        initCharts(data); 
                    } else {
                        lineChart.data.labels = data.line.labels;
                        lineChart.data.datasets[0].data = data.line.total;
                        lineChart.data.datasets[1].data = data.line.success;
                        lineChart.data.datasets[2].data = data.line.canceled;
                        lineChart.update();
                    }
                }
            })
            .catch(error => console.error('Dashboard Refresh Error:', error));
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
    .vr { width: 1px; height: 100%; } /* Thinner vertical rule for better space */
    
    /* Perfect Icon Centering */
    .bi { display: inline-flex; align-items: center; justify-content: center; }

    /* Custom 5-Column Grid Fix */
    @media (min-width: 768px) {
        .row-cols-md-5 > * {
            flex: 0 0 auto;
            width: 20%;
        }
    }

    /* Table Hover Effects */
    .table tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.02);
        transform: scale(1.002);
    }
    
    .avatar-sm { flex-shrink: 0; }

    /* Card Text Scaling for 5-column layout */
    .row-cols-md-5 h3 { font-size: 1.6rem !important; }
    .row-cols-md-5 h6 { font-size: 0.6rem !important; }
    .row-cols-md-5 .small, .row-cols-md-5 small { font-size: 0.65rem !important; }


    .vr { 
    width: 1px; 
    height: 25px; 
    background-color: rgba(255,255,255,0.25); 
}
</style>
@endsection