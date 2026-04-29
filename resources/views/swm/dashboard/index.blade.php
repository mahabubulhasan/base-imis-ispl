@extends('layouts.dashboard')

@section('title', $page_title)

@section('content')
<div class="card">
    <div class="card-header">
        <form id="swm-dashboard-filter-form" method="GET" class="form-inline d-flex flex-wrap gap-2 align-items-end" action="{{ route('swm.dashboard-kpis.index') }}">
            <div>
                <label for="month_from" class="d-block small text-muted mb-0">{{ __('Month from') }}</label>
                <input type="month" id="month_from" name="month_from" class="form-control" value="{{ $dashboard['range']['from'] ?? now()->format('Y-m') }}">
            </div>
            <div>
                <label for="month_to" class="d-block small text-muted mb-0">{{ __('Month to') }}</label>
                <input type="month" id="month_to" name="month_to" class="form-control" value="{{ $dashboard['range']['to'] ?? now()->format('Y-m') }}">
            </div>
            <button type="submit" id="swm-dashboard-apply" class="btn btn-info">{{ __('Apply') }}</button>
        </form>
    </div>
</div>

<div id="swm-kpi-dashboard-body">
@php
    $sections = [
        __('Existing KPIs') => $dashboard['existing_kpis'] ?? [],
        __('Coverage') => $dashboard['coverage'] ?? [],
        __('Billing Module') => $dashboard['billing'] ?? [],
        __('Complaints') => $dashboard['complaints'] ?? [],
        __('Service Providers') => $dashboard['service_providers'] ?? [],
        __('Service Facilities') => $dashboard['service_facilities'] ?? [],
        __('City Statistics') => $dashboard['city_statistics'] ?? [],
    ];
@endphp

@foreach($sections as $title => $rows)
<div class="card swm-kpi-section-card">
    <div class="card-header"><strong>{{ $title }}</strong></div>
    <div class="card-body p-0">
        <div style="overflow:auto">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width:70%">{{ __('Indicator') }}</th>
                        <th>{{ __('Value') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $label => $value)
                    <tr>
                        <td>{{ __($label) }}</td>
                        <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2">{{ __('No data available') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endforeach

<div class="card swm-kpi-section-card">
    <div class="card-header"><strong>{{ __('Ward-wise Statistics') }}</strong></div>
    <div class="card-body p-0">
        <div style="overflow:auto">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Ward No') }}</th>
                        <th>{{ __('Total Households') }}</th>
                        <th>{{ __('Waste Vans') }}</th>
                        <th>{{ __('Covered Households') }}</th>
                        <th>{{ __('Coverage Percent') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($dashboard['ward_statistics'] ?? []) as $row)
                    <tr>
                        <td>{{ $row['ward_no'] ?? '' }}</td>
                        <td>{{ $row['total_households'] ?? 0 }}</td>
                        <td>{{ $row['waste_vans'] ?? __('N/A') }}</td>
                        <td>{{ $row['covered_households'] ?? 0 }}</td>
                        <td>{{ $row['coverage_percent'] ?? 0 }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5">{{ __('No data available') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card swm-kpi-section-card">
    <div class="card-header"><strong>{{ __('Vehicle Statistics') }}</strong></div>
    <div class="card-body p-0">
        <div style="overflow:auto">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Vehicle Type') }}</th>
                        <th>{{ __('Total Count') }}</th>
                        <th>{{ __('Remarks') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($dashboard['vehicle_statistics'] ?? []) as $row)
                    <tr>
                        <td>{{ $row['vehicle_type'] ?? '' }}</td>
                        <td>{{ $row['total_count'] ?? 0 }}</td>
                        <td>{{ $row['remarks'] ?? '' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3">{{ __('No data available') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
@php
    $swmDashboardKpiJs = [
        'sectionTitles' => [
            __('Existing KPIs'),
            __('Coverage'),
            __('Billing Module'),
            __('Complaints'),
            __('Service Providers'),
            __('Service Facilities'),
            __('City Statistics'),
        ],
        'indicatorLabel' => __('Indicator'),
        'valueLabel' => __('Value'),
        'noDataLabel' => __('No data available'),
        'wardTitle' => __('Ward-wise Statistics'),
        'vehicleTitle' => __('Vehicle Statistics'),
        'wardCols' => [
            __('Ward No'),
            __('Total Households'),
            __('Waste Vans'),
            __('Covered Households'),
            __('Coverage Percent'),
        ],
        'vehicleCols' => [
            __('Vehicle Type'),
            __('Total Count'),
            __('Remarks'),
        ],
        'naLabel' => __('N/A'),
        'loaderMsg' => __('Loading dashboard…'),
        'errorTitle' => __('Error'),
        'loadFailedMsg' => __('Could not load dashboard data. Please try again.'),
        'kpiIndexUrl' => route('swm.dashboard-kpis.index'),
    ];
@endphp
<script>
(function () {
    var L = @json($swmDashboardKpiJs);
    var sectionTitles = L.sectionTitles;
    var indicatorLabel = L.indicatorLabel;
    var valueLabel = L.valueLabel;
    var noDataLabel = L.noDataLabel;
    var wardTitle = L.wardTitle;
    var vehicleTitle = L.vehicleTitle;
    var wardCols = L.wardCols;
    var vehicleCols = L.vehicleCols;
    var naLabel = L.naLabel;

    function esc(s) {
        if (s === null || s === undefined) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatCell(v) {
        if (v !== null && typeof v === 'object') {
            return esc(JSON.stringify(v));
        }
        return esc(v);
    }

    function buildKeyValueTable(rows) {
        var keys = Object.keys(rows || {});
        if (!keys.length) {
            return '<tr><td colspan="2">' + esc(noDataLabel) + '</td></tr>';
        }
        return keys.map(function (label) {
            return '<tr><td>' + esc(label) + '</td><td>' + formatCell(rows[label]) + '</td></tr>';
        }).join('');
    }

    function buildWardTable(rows) {
        if (!rows || !rows.length) {
            return '<tr><td colspan="5">' + esc(noDataLabel) + '</td></tr>';
        }
        return rows.map(function (row) {
            return '<tr>'
                + '<td>' + esc(row.ward_no) + '</td>'
                + '<td>' + esc(row.total_households) + '</td>'
                + '<td>' + (row.waste_vans === null || row.waste_vans === undefined || row.waste_vans === '' ? esc(naLabel) : esc(row.waste_vans)) + '</td>'
                + '<td>' + esc(row.covered_households) + '</td>'
                + '<td>' + esc(row.coverage_percent) + '</td>'
                + '</tr>';
        }).join('');
    }

    function buildVehicleTable(rows) {
        if (!rows || !rows.length) {
            return '<tr><td colspan="3">' + esc(noDataLabel) + '</td></tr>';
        }
        return rows.map(function (row) {
            return '<tr>'
                + '<td>' + esc(row.vehicle_type) + '</td>'
                + '<td>' + esc(row.total_count) + '</td>'
                + '<td>' + esc(row.remarks) + '</td>'
                + '</tr>';
        }).join('');
    }

    function renderDashboard(d) {
        var sectionKeys = [
            'existing_kpis',
            'coverage',
            'billing',
            'complaints',
            'service_providers',
            'service_facilities',
            'city_statistics',
        ];
        var html = '';
        for (var i = 0; i < sectionKeys.length; i++) {
            html += '<div class="card swm-kpi-section-card">'
                + '<div class="card-header"><strong>' + esc(sectionTitles[i]) + '</strong></div>'
                + '<div class="card-body p-0"><div style="overflow:auto">'
                + '<table class="table table-bordered mb-0">'
                + '<thead><tr><th style="width:70%">' + esc(indicatorLabel) + '</th><th>' + esc(valueLabel) + '</th></tr></thead>'
                + '<tbody>' + buildKeyValueTable(d[sectionKeys[i]] || {}) + '</tbody>'
                + '</table></div></div></div>';
        }
        html += '<div class="card swm-kpi-section-card">'
            + '<div class="card-header"><strong>' + esc(wardTitle) + '</strong></div>'
            + '<div class="card-body p-0"><div style="overflow:auto">'
            + '<table class="table table-bordered mb-0">'
            + '<thead><tr>'
            + '<th>' + esc(wardCols[0]) + '</th><th>' + esc(wardCols[1]) + '</th><th>' + esc(wardCols[2]) + '</th>'
            + '<th>' + esc(wardCols[3]) + '</th><th>' + esc(wardCols[4]) + '</th>'
            + '</tr></thead>'
            + '<tbody>' + buildWardTable(d.ward_statistics || []) + '</tbody>'
            + '</table></div></div></div>';
        html += '<div class="card swm-kpi-section-card">'
            + '<div class="card-header"><strong>' + esc(vehicleTitle) + '</strong></div>'
            + '<div class="card-body p-0"><div style="overflow:auto">'
            + '<table class="table table-bordered mb-0">'
            + '<thead><tr>'
            + '<th>' + esc(vehicleCols[0]) + '</th><th>' + esc(vehicleCols[1]) + '</th><th>' + esc(vehicleCols[2]) + '</th>'
            + '</tr></thead>'
            + '<tbody>' + buildVehicleTable(d.vehicle_statistics || []) + '</tbody>'
            + '</table></div></div></div>';
        document.getElementById('swm-kpi-dashboard-body').innerHTML = html;
    }

    var kpiIndexUrl = L.kpiIndexUrl;

    /** Full URL for fetch (same origin). */
    function dashboardFetchUrl(monthFrom, monthTo) {
        var u = new URL(kpiIndexUrl, window.location.href);
        if (monthFrom) u.searchParams.set('month_from', monthFrom);
        if (monthTo) u.searchParams.set('month_to', monthTo);
        return u.toString();
    }

    /** Path + query for history (relative to site root). */
    function dashboardHistoryUrl(monthFrom, monthTo) {
        var u = new URL(kpiIndexUrl, window.location.href);
        if (monthFrom) u.searchParams.set('month_from', monthFrom);
        if (monthTo) u.searchParams.set('month_to', monthTo);
        return u.pathname + u.search;
    }

    $('#swm-dashboard-filter-form').on('submit', function (e) {
        e.preventDefault();
        var monthFrom = $('#month_from').val();
        var monthTo = $('#month_to').val();
        displayAjaxLoader(L.loaderMsg);
        fetch(dashboardFetchUrl(monthFrom, monthTo), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (payload) {
                if (!payload.dashboard) throw new Error('Invalid response');
                renderDashboard(payload.dashboard);
                if (window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', dashboardHistoryUrl(monthFrom, monthTo));
                }
            })
            .catch(function (err) {
                console.error(err);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: L.errorTitle, text: L.loadFailedMsg });
                } else {
                    alert(L.loadFailedMsg);
                }
            })
            .finally(function () {
                removeAjaxLoader();
            });
    });
})();
</script>
@endpush
