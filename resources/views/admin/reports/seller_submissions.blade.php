@extends('layouts.admin')

@section('content')
<style>
    /* Professional Layout Adjustments */
    .glass-header {
        background: #fff;
        padding: 2rem 2rem; /* Medium padding */
        border-bottom: 1px solid #edf2f7;
        margin-bottom: 1.5rem;
    }
    .report-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border: none;
    }
    .table thead th {
        background-color: #800000; /* Maroon Header */
        color: #ffffff;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 0.05em;
        padding: 1rem 1.5rem;
    }
    .seller-avatar {
        width: 38px;
        height: 38px;
        background: #f8eeee;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #800000;
        margin-right: 12px;
        font-size: 0.9rem;
    }
    .btn-view {
        background: #800000;
        color: white;
        border-radius: 6px;
        padding: 0.4rem 1rem;
        font-size: 0.85rem;
        border: none;
    }
    .btn-view:hover {
        background: #a00000;
        color: #fff;
    }
</style>

<div class="container-fluid p-0">
    <div class="glass-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">Seller Analytics</h2>
            <p class="text-muted small mb-0">Review and audit inventory reports submitted by sellers.</p>
        </div>
        
        <div class="d-flex align-items-center bg-light px-3 py-2 rounded-3 border">
            <i class="bi bi-folder-check text-maroon me-2" style="color: #800000; font-size: 1.2rem;"></i>
            <div>
                <span class="text-muted small d-block" style="line-height: 1;">Submissions</span>
                <span class="fw-bold text-dark">{{ $reports->count() }}</span>
            </div>
        </div>
    </div>

    <div class="px-5">
        <div class="card report-card overflow-hidden shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead>
                        <tr>
                            <th class="ps-4">Seller</th>
                            <th>Report Name</th>
                            <th>Submitted At</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="seller-avatar border">
                                        {{ substr($report->seller_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $report->seller_name }}</div>
                                        <small class="text-muted">ID: #{{ $report->seller_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-dark">{{ $report->report_name }}</span>
                            </td>
                            <td>
                                <div class="text-dark small">{{ date('d M, Y', strtotime($report->submitted_at)) }}</div>
                                <div class="text-muted" style="font-size: 10px;">{{ date('h:i A', strtotime($report->submitted_at)) }}</div>
                            </td>
                            <td class="text-center">
                                @if($report->status == 'pending')
                                    <span class="badge bg-warning text-dark px-2 py-1" style="font-size: 10px;">NEW</span>
                                @else
                                    <span class="badge bg-light text-success border border-success px-2 py-1" style="font-size: 10px;">VIEWED</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.seller.analytics.show', $report->id) }}" target="_blank" class="btn btn-view shadow-sm">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No seller reports found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection