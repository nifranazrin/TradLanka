@extends('layouts.seller')

@section('content')
<style>
    /* Tailwind-inspired card styling */
    .report-selection-card input:checked + .card {
        border-color: #5b2c2c !important; /* Maroon Brand Color */
        background-color: #fdf2f2 !important; /* Subtle maroon tint */
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .report-selection-card .card {
        transition: all 0.3s ease;
        border: 2px solid #e5e7eb;
        cursor: pointer;
    }

    .report-selection-card .card:hover {
        border-color: #5b2c2c;
        transform: translateY(-2px);
    }

    .icon-shape {
        width: 54px;
        height: 54px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    /* Professional Icon Backgrounds */
    .bg-top-selling { background-color: #e0f2fe; color: #0369a1; } /* Blue tint */
    .bg-slow-moving { background-color: #f3f4f6; color: #374151; } /* Gray tint */
    .bg-low-stock { background-color: #fee2e2; color: #b91c1c; }   /* Red tint */

    .cursor-pointer { cursor: pointer; }
</style>

<div class="container px-4 mx-auto mt-5">
    <div class="text-center mb-5">
        <h2 class="page-title" style="font-size:36px; font-weight:800; color:#111827; letter-spacing:-1px;">Product & Inventory Analytics</h2>
        <p class="page-subtitle" style="font-size:16px; font-weight:500; color:#6b7280; max-width: 600px; margin: 10px auto 0;">
            Gain deep insights into your inventory health. Choose a specialized report to optimize your stock and sales strategy.
        </p>
    </div>

    <form action="{{ route('seller.reports.inventory') }}" method="GET">
        <div class="row g-4 justify-content-center">
            
            <div class="col-md-4">
                <label class="report-selection-card h-100 w-100 cursor-pointer">
                    <input type="radio" name="report_type" value="top_selling" class="d-none" checked>
                    <div class="card p-4 h-100">
                        <div class="icon-shape bg-top-selling mb-4">
                            <i class="bi bi-graph-up-arrow fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Top-Selling Products</h5>
                        <p class="text-muted small mb-0">Identify your high-performance items generating the most revenue and customer interest.</p>
                    </div>
                </label>
            </div>

            <div class="col-md-4">
                <label class="report-selection-card h-100 w-100 cursor-pointer">
                    <input type="radio" name="report_type" value="slow_moving" class="d-none">
                    <div class="card p-4 h-100">
                        <div class="icon-shape bg-slow-moving mb-4">
                            <i class="bi bi-clock-history fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Slow-Moving Stock</h5>
                        <p class="text-muted small mb-0">Discover products with no sales activity in the last 60 days to plan clearance or promotions.</p>
                    </div>
                </label>
            </div>

            <div class="col-md-4">
                <label class="report-selection-card h-100 w-100 cursor-pointer">
                    <input type="radio" name="report_type" value="low_stock" class="d-none">
                    <div class="card p-4 h-100">
                        <div class="icon-shape bg-low-stock mb-4">
                            <i class="bi bi-exclamation-triangle fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Low Stock Alerts</h5>
                        <p class="text-muted small mb-0">Critical monitoring for items with less than 5 units remaining to prevent out-of-stock scenarios.</p>
                    </div>
                </label>
            </div>

        </div>

        <div class="text-center mt-5">
            <button type="submit" class="btn btn-lg px-5 py-3 text-white shadow" style="background:#5b2c2c; border-radius: 12px; font-weight: 700; font-size: 18px; transition: 0.3s;">
                <i class="bi bi-file-earmark-bar-graph-fill me-2"></i> Generate Analytics Report
            </button>
        </div>
    </form>
</div>
@endsection