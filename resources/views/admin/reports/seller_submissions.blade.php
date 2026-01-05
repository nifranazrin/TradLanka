@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="font-weight: 800; color: #111827; margin: 0;">Seller Analytics</h2>
            <p class="text-muted mb-0">Review and manage inventory reports submitted by sellers</p>
        </div>
        <div class="bg-white px-3 py-2 rounded shadow-sm border">
            <span class="text-muted small">Total Submissions:</span>
            <span class="fw-bold text-dark">{{ $reports->count() }}</span>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background: #5b2c2c; color: #fff; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="px-4 py-3">Seller Details</th>
                        <th class="px-4 py-3">Report Type</th>
                        <th class="px-4 py-3">Submission Date</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($reports as $report)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="fw-bold text-dark" style="font-size: 14px;">{{ $report->seller_name }}</div>
                            <small class="text-muted">ID: #SEL-{{ str_pad($report->seller_id, 3, '0', STR_PAD_LEFT) }}</small>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-dark" style="font-weight: 500;">{{ $report->report_name }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-dark small">{{ date('M d, Y', strtotime($report->submitted_at)) }}</div>
                            <div class="text-muted" style="font-size: 11px;">{{ date('h:i A', strtotime($report->submitted_at)) }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($report->status == 'pending')
                                <span class="badge rounded-pill bg-warning text-dark px-3" style="font-size: 10px;">NEW</span>
                            @else
                                <span class="badge rounded-pill bg-light text-success border border-success px-3" style="font-size: 10px;">VIEWED</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <a href="{{ route('admin.seller.analytics.show', $report->id) }}" target="_blank" class="btn btn-sm btn-dark px-3" style="border-radius: 6px; font-size: 12px;">
                                <i class="bi bi-file-earmark-pdf me-1"></i> View Report
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="mb-2"><i class="bi bi-inbox text-muted" style="font-size: 40px;"></i></div>
                            <p class="text-muted">No reports have been submitted by sellers yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection