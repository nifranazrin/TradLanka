@extends('layouts.admin')

@section('content')

<style>
    /* Professional Maroon & Gold Theme for Reports */
    .report-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }
    .text-maroon { color: #5b2c2c !important; }
    .bg-maroon { background-color: #5b2c2c !important; color: white !important; }
    
    .table-custom thead th {
        background-color: #f8f9fa;
        color: #5b2c2c;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 15px;
        border-bottom: 2px solid #5b2c2c;
    }

    .stock-badge {
        font-size: 0.9rem;
        padding: 6px 12px;
        border-radius: 8px;
        min-width: 45px;
        display: inline-block;
    }

    .recovery-row {
        transition: background-color 0.2s;
    }
    .recovery-row:hover {
        background-color: #fff9f9;
    }

    @media print {
        .no-print { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    }
</style>

<div class="container-fluid px-4 py-5">
    
    {{-- HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-maroon mb-1">Inventory & Stock Recovery</h2>
            <p class="text-muted small">Live warehouse status and automated recovery logs from failed deliveries.</p>
        </div>
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-maroon px-4 fw-bold shadow-sm">
                <i class="bi bi-printer me-2"></i> Print Report
            </button>
        </div>
    </div>

    {{-- TOP ANALYTICS CARDS --}}
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card report-card bg-maroon p-4 text-center">
                <span class="text-uppercase small opacity-75">Total Products</span>
                <h2 class="fw-bold mb-0 mt-1">{{ $currentStock->count() }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card report-card bg-danger text-white p-4 text-center">
                <span class="text-uppercase small opacity-75">Low Stock Alerts</span>
                <h2 class="fw-bold mb-0 mt-1">{{ $currentStock->where('stock', '<', 10)->count() }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card report-card bg-success text-white p-4 text-center">
                <span class="text-uppercase small opacity-75">Total Stock Value</span>
                <h2 class="fw-bold mb-0 mt-1">
                    Rs. {{ number_format($currentStock->sum(fn($p) => $p->stock * $p->price), 2) }}
                </h2>
            </div>
        </div>
    </div>

    {{-- SECTION 1: LIVE WAREHOUSE STOCK --}}
    <div class="card report-card mb-5">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-bold text-maroon mb-0"><i class="bi bi-box-seam me-2"></i>Current Warehouse Inventory</h5>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-custom">
                    <tr>
                        <th class="ps-4">Product Name</th>
                        <th class="text-center">Current Stock</th>
                        <th>Unit Price</th>
                        <th>Total Inventory Value</th>
                        <th class="pe-4">Health Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($currentStock as $product)
                        <tr>
                            <td class="ps-4 py-3 fw-bold text-dark">{{ $product->name }}</td>
                            <td class="text-center">
                                <span class="stock-badge fw-bold {{ $product->stock < 10 ? 'bg-danger text-white' : 'bg-light text-dark border' }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td>Rs. {{ number_format($product->price, 2) }}</td>
                            <td class="fw-bold">Rs. {{ number_format($product->stock * $product->price, 2) }}</td>
                            <td class="pe-4">
                                @if($product->stock <= 0)
                                    <span class="badge bg-danger">OUT OF STOCK</span>
                                @elseif($product->stock < 10)
                                    <span class="badge bg-warning text-dark">CRITICAL LOW</span>
                                @else
                                    <span class="badge bg-success">HEALTHY</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center p-5">No products found in the database.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SECTION 2: RECOVERY LOG (STATUS 6) --}}
    <div class="card report-card border-0">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-arrow-counterclockwise me-2"></i>Stock Recovery Log (Failed/Cancelled)</h5>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-custom">
                    <tr>
                        <th class="ps-4">Recovery Date</th>
                        <th>Order ID</th>
                        <th>Recovered Items</th>
                        <th>Cancellation Reason</th>
                        <th class="pe-4 text-end">Action History</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recoveredOrders as $order)
                        <tr class="recovery-row">
                            <td class="ps-4 py-3">{{ $order->updated_at->format('d M, Y | h:i A') }}</td>
                            <td class="fw-bold text-primary">#{{ $order->tracking_no }}</td>
                            <td>
                                @foreach($order->items as $item)
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="badge bg-success me-2">+{{ $item->qty }}</span>
                                        <span class="small fw-bold">{{ $item->product->name }}</span>
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                <span class="text-danger small fw-bold">
                                    <i class="bi bi-exclamation-circle me-1"></i> {{ $order->cancel_reason ?? 'Manual Cancellation' }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <span class="badge bg-secondary">Stock Restored</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center p-5 text-muted">No stock recovery events recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{ $recoveredOrders->links() }}
        </div>
    </div>
</div>

@endsection