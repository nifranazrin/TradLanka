@extends('layouts.admin')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* Professional Maroon Theme */
    .maroon-header th { background-color: #3b0b0b !important; color: white !important; padding: 15px !important; }
    .text-maroon { color: #800000; }
    .custom-shadow-table { border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: none; }
    
    /* Filter Section Styling */
    .filter-card { border-radius: 15px; border: none; background: #ffffff; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .form-control-maroon:focus { border-color: #800000; box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.1); }
    
    /* Flag and Currency Badges in Table */
    .flag-img { width: 30px; border-radius: 3px; margin-right: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.2); vertical-align: middle; }
    .currency-lkr { color: #198754; font-weight: bold; } /* Green for Rs. */
    .currency-usd { color: #0d6efd; font-weight: bold; } /* Blue for $ */

    /* Perfect Select2 Alignment Fix */
    .select2-container--default .select2-selection--single {
        border-radius: 50px !important;
        height: 42px !important;
        border: 1px solid #ced4da !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal !important;
        display: flex !important;
        align-items: center !important;
        padding-left: 15px !important;
        height: 100% !important;
        color: #333 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        top: 1px !important;
    }

    /* Gold Badge Styling - Positioned in front of name */
    .gold-badge {
        background: linear-gradient(45deg, #ffd700, #ffae00);
        color: #3b0b0b;
        font-weight: 800;
        font-size: 0.65rem;
        padding: 2px 8px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        text-transform: uppercase;
        margin-right: 8px;
        display: inline-flex;
        align-items: center;
    }


    .btn-maroon {
    background-color: #3b0b0b !important; /* Your theme maroon */
    color: white !important;
    border: none;
    transition: all 0.3s ease;
}

.btn-maroon:hover {
    background-color: #5a1212 !important; /* Slightly lighter maroon on hover */
    color: #facc15 !important; /* Yellow text highlight on hover to match logo */
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0"><i class="bi bi-globe-americas text-maroon"></i> Customer Global Analysis</h4>

        <a href="{{ route('admin.customers.download', request()->query()) }}" class="btn btn-maroon rounded-pill px-4 shadow-sm">
    <i class="bi bi-file-earmark-pdf-fill me-2"></i> Download PDF Report
</a>
    </div>

    {{-- Advanced Filter & Search Bar --}}
    <div class="card filter-card mb-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.customers.index') }}" method="GET" id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="small fw-bold text-muted mb-1">Search Customer</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-maroon border-start-0" 
                               placeholder="Search ID, Name, or Tracking #" value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="small fw-bold text-muted mb-1">Market</label>
                    <select name="market" class="form-select form-control-maroon auto-filter">
                        <option value="">All Markets</option>
                        <option value="local" {{ request('market') == 'local' ? 'selected' : '' }}>Local (LKR)</option>
                        <option value="international" {{ request('market') == 'international' ? 'selected' : '' }}>International (USD)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="small fw-bold text-muted mb-1">Country</label>
                    <select name="country_name" id="countrySelect" class="form-select">
                        <option value="">Choose Country...</option>
                        @foreach($countriesList as $name => $code)
                            <option value="{{ $name }}" data-flag="{{ $code }}" {{ request('country_name') == $name ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1">
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary w-100 rounded-pill" title="Reset Filters">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Analysis Table --}}
    <div class="custom-shadow-table bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="maroon-header">
                    <tr>
                        <th class="ps-4">Customer Details</th>
                        <th>Region / Country</th>
                        <th class="text-center">Order Count</th>
                        <th class="text-end pe-5">Total Spend</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $index => $user)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                {{-- Gold Badge Logic: 10 or more orders --}}
                                @if($user->orders_count >= 10)
                                    <span class="gold-badge">
                                        <i class="bi bi-star-fill me-1"></i> GOLD
                                    </span>
                                @endif
                                
                                <span class="fw-bold text-dark">{{ $user->name ?? ($user->fname . ' ' . $user->lname) }}</span>
                            </div>
                            <div class="text-muted small mt-1">ID: #{{ $user->id }} | {{ $user->email }}</div>
                        </td>

                        <td>
                            @php 
                                $countryMap = [
                                    'sri lanka' => 'lk', 'united arab emirates' => 'ae', 'saudi arabia' => 'sa',
                                    'qatar' => 'qa', 'oman' => 'om', 'kuwait' => 'kw', 'united kingdom' => 'gb',
                                    'france' => 'fr', 'germany' => 'de', 'italy' => 'it', 'netherlands' => 'nl',
                                    'united states' => 'us', 'canada' => 'ca', 'australia' => 'au',
                                    'new zealand' => 'nz', 'india' => 'in', 'singapore' => 'sg',
                                    'malaysia' => 'my', 'japan' => 'jp', 'south korea' => 'kr', 'maldives' => 'mv'
                                ];
                                $rawCountry = strtolower($user->country ?? '');
                                $isSriLanka = ($rawCountry == 'sri lanka');
                                $countryCode = $countryMap[$rawCountry] ?? 'un'; 
                            @endphp
                            
                            <img src="https://flagcdn.com/w40/{{ $countryCode }}.png" class="flag-img" alt="flag">
                            <span class="small fw-bold text-capitalize">{{ $user->country ?? 'N/A' }}</span>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-light text-dark border rounded-pill px-3">{{ $user->orders_count }}</span>
                        </td>

                        <td class="text-end pe-5">
                            {{-- Regional currency formatting --}}
                            @if($isSriLanka)
                                <span class="currency-lkr">Rs. {{ number_format($user->total_spent ?? 0, 2) }}</span>
                            @else
                                <span class="currency-usd">$ {{ number_format($user->total_spent ?? 0, 2) }}</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <a href="{{ route('admin.customers.show', $user->id) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                <i class="bi bi-eye"></i> View History
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-search display-4"></i>
                            <p class="mt-2">No customers found matching your filters.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function formatCountry(country) {
        if (!country.id) return country.text;
        var flagCode = $(country.element).data('flag');
        var $country = $(
            '<div style="display: flex; align-items: center; line-height: normal;">' +
                '<img src="https://flagcdn.com/w40/' + flagCode + '.png" ' +
                'style="width: 24px; max-height: 18px; margin-right: 10px; border-radius: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);" />' +
                '<span style="font-weight: 500; color: #333;">' + country.text + '</span>' +
            '</div>'
        );
        return $country;
    };

    $('#countrySelect').select2({
        templateResult: formatCountry,
        templateSelection: formatCountry,
        width: '100%',
        dropdownAutoWidth: true
    });

    $('.auto-filter, #countrySelect').on('change', function() {
        $('#filterForm').submit();
    });

    let typingTimer;
    $('input[name="search"]').on('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            $('#filterForm').submit();
        }, 1000);
    });
});
</script>
@endsection