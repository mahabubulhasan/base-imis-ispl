@extends('layouts.dashboard')

@section('title', $page_title)

@section('content')
<style>
    #swm-dashboard-count-boxes .info-box-content {
        padding-right: 56px; /* keep text clear of icon */
    }
    #swm-dashboard-count-boxes .info-box-text {
        white-space: normal;
        line-height: 1.2;
        margin-bottom: 2px;
    }
    #swm-dashboard-count-boxes .info-box-number {
        white-space: nowrap;
    }
</style>
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

@php
    $swmCity = $dashboard['city_statistics'] ?? [];
    $swmCov = $dashboard['coverage'] ?? [];
    $swmSp = $dashboard['service_providers'] ?? [];
    $swmComp = $dashboard['complaints'] ?? [];
    $fmtSwmInt = function ($v) {
        return number_format((int) (is_numeric($v) ? $v : 0));
    };
@endphp
<div class="row" style="padding: 15px 0;" id="swm-dashboard-count-boxes">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="info-box bg-info bg-gradient">
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Households') }}</span>
                <span class="info-box-number" id="swm-count-households">{{ $fmtSwmInt($swmCity['Total Households'] ?? 0) }}</span>
            </div>
            <div class="info-box-icon">
                <img src="{{ asset('img/svg/imis-icons/residential.svg') }}" alt="">
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="info-box bg-info bg-gradient">
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Covered households') }}</span>
                <span class="info-box-number" id="swm-count-covered">{{ $fmtSwmInt($swmCov['Covered households'] ?? 0) }}</span>
            </div>
            <div class="info-box-icon">
                <img src="{{ asset('img/svg/imis-icons/buildingIMS.svg') }}" height="70px" alt="">
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="info-box bg-info bg-gradient">
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Workers') }}</span>
                <span class="info-box-number" id="swm-count-workers">{{ $fmtSwmInt($swmSp['Total number of workers'] ?? 0) }}</span>
            </div>
            <div class="info-box-icon">
                <img src="{{ asset('img/svg/imis-icons/institution.svg') }}" alt="">
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="info-box bg-info bg-gradient">
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Vehicles') }}</span>
                <span class="info-box-number" id="swm-count-vehicles">{{ $fmtSwmInt($swmSp['Total vehicles'] ?? 0) }}</span>
            </div>
            <div class="info-box-icon">
                <img src="{{ asset('img/svg/imis-icons/desludgingVehicle.svg') }}" alt="">
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="info-box bg-info bg-gradient">
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Service providers') }}</span>
                <span class="info-box-number" id="swm-count-providers">{{ $fmtSwmInt($swmSp['Total number of service providers'] ?? 0) }}</span>
            </div>
            <div class="info-box-icon">
                <img src="{{ asset('img/svg/imis-icons/serviceProvider.svg') }}" alt="">
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="info-box bg-info bg-gradient">
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Complaints (period)') }}</span>
                <span class="info-box-number" id="swm-count-complaints">{{ $fmtSwmInt($swmComp['Total complaints received'] ?? 0) }}</span>
            </div>
            <div class="info-box-icon">
                <img src="{{ asset('img/svg/imis-icons/health_building.svg') }}" alt="">
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs px-2 pt-2" id="swm-dashboard-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="swm-tab-dashboard-link" data-toggle="tab" href="#swm-tab-dashboard" role="tab" aria-controls="swm-tab-dashboard" aria-selected="true">{{ __('Dashboard') }}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="swm-tab-kpis-link" data-toggle="tab" href="#swm-tab-kpis" role="tab" aria-controls="swm-tab-kpis" aria-selected="false">{{ __('KPIs') }}</a>
    </li>
</ul>

<div class="tab-content px-2 pb-3">
    <div class="tab-pane fade show active" id="swm-tab-dashboard" role="tabpanel" aria-labelledby="swm-tab-dashboard-link">
        <div class="row">
            <div class="col-md-6">
                @include('layouts.dashboard.chart-card', [
                    'card_title' => __('Billing by month'),
                    'export_chart_btn_id' => 'swmExportBillingByMonth',
                    'canvas_id' => 'swmChartBillingByMonth',
                    'with_chart_loader' => true,
                ])
            </div>
            <div class="col-md-6">
                @include('layouts.dashboard.chart-card', [
                    'card_title' => __('Complaints by type'),
                    'export_chart_btn_id' => 'swmExportComplaintsByType',
                    'canvas_id' => 'swmChartComplaintsByType',
                    'with_chart_loader' => true,
                ])
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                @include('layouts.dashboard.chart-card', [
                    'card_title' => __('Complaints by ward'),
                    'export_chart_btn_id' => 'swmExportComplaintsByWard',
                    'canvas_id' => 'swmChartComplaintsByWard',
                    'with_chart_loader' => true,
                ])
            </div>
            <div class="col-md-6">
                @include('layouts.dashboard.chart-card', [
                    'card_title' => __('Workers by type'),
                    'export_chart_btn_id' => 'swmExportWorkersByType',
                    'canvas_id' => 'swmChartWorkersByType',
                    'with_chart_loader' => true,
                ])
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                @include('layouts.dashboard.chart-card', [
                    'card_title' => __('Vehicles by type'),
                    'export_chart_btn_id' => 'swmExportVehiclesByType',
                    'canvas_id' => 'swmChartVehiclesByType',
                    'with_chart_loader' => true,
                ])
            </div>
            <div class="col-md-6">
                @include('layouts.dashboard.chart-card', [
                    'card_title' => __('Households by ward'),
                    'export_chart_btn_id' => 'swmExportHouseholdsByWard',
                    'canvas_id' => 'swmChartHouseholdsByWard',
                    'with_chart_loader' => true,
                ])
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                @include('layouts.dashboard.chart-card', [
                    'card_title' => __('Household coverage by ward'),
                    'export_chart_btn_id' => 'swmExportHouseholdCoverageByWard',
                    'canvas_id' => 'swmChartHouseholdCoverageByWard',
                    'with_chart_loader' => true,
                ])
            </div>
            <div class="col-md-6">
                @include('layouts.dashboard.chart-card', [
                    'card_title' => __('Households vs van pullers by ward'),
                    'export_chart_btn_id' => 'swmExportHouseholdsVsVanPullers',
                    'canvas_id' => 'swmChartHouseholdsVsVanPullers',
                    'with_chart_loader' => true,
                ])
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="swm-tab-kpis" role="tabpanel" aria-labelledby="swm-tab-kpis-link">
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
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <strong>{{ $title }}</strong>
                <input type="search" class="form-control form-control-sm swm-kpi-search" style="max-width:220px" placeholder="{{ __('Search…') }}" autocomplete="off">
            </div>
            <div class="card-body p-0">
                <div style="overflow:auto">
                    <table class="table table-bordered mb-0 swm-kpi-table">
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
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <strong>{{ __('Ward-wise Statistics') }}</strong>
                <input type="search" class="form-control form-control-sm swm-kpi-search" style="max-width:220px" placeholder="{{ __('Search…') }}" autocomplete="off">
            </div>
            <div class="card-body p-0">
                <div style="overflow:auto">
                    <table class="table table-bordered mb-0 swm-kpi-table">
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
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <strong>{{ __('Vehicle Statistics') }}</strong>
                <input type="search" class="form-control form-control-sm swm-kpi-search" style="max-width:220px" placeholder="{{ __('Search…') }}" autocomplete="off">
            </div>
            <div class="card-body p-0">
                <div style="overflow:auto">
                    <table class="table table-bordered mb-0 swm-kpi-table">
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
    </div>
</div>
@endsection

@push('scripts')
@php
    $swmDashboardConfig = [
        'kpiIndexUrl' => route('swm.dashboard-kpis.index'),
        'urls' => [
            'billingByMonth' => route('swm.dashboard-kpis.charts.billing-by-month'),
            'complaintsByType' => route('swm.dashboard-kpis.charts.complaints-by-type'),
            'complaintsByWard' => route('swm.dashboard-kpis.charts.complaints-by-ward'),
            'workersByType' => route('swm.dashboard-kpis.charts.workers-by-type'),
            'vehiclesByType' => route('swm.dashboard-kpis.charts.vehicles-by-type'),
            'householdsByWard' => route('swm.dashboard-kpis.charts.households-by-ward'),
            'householdCoverageByWard' => route('swm.dashboard-kpis.charts.household-coverage-by-ward'),
            'householdsVsVanPullersByWard' => route('swm.dashboard-kpis.charts.households-vs-van-pullers-by-ward'),
        ],
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
        'chartsFailedMsg' => __('Could not load chart data. Please try again.'),
        'searchPlaceholder' => __('Search…'),
        'axisMonth' => __('Month'),
        'axisAmount' => __('Amount'),
        'axisWard' => __('Ward'),
        'axisCount' => __('Count'),
        'axisPercent' => __('Percent'),
        'axisCategory' => __('Category'),
        'chartMeta' => [
            'swmChartBillingByMonth' => __('Billing by month'),
            'swmChartComplaintsByType' => __('Complaints by type'),
            'swmChartComplaintsByWard' => __('Complaints by ward'),
            'swmChartWorkersByType' => __('Workers by type'),
            'swmChartVehiclesByType' => __('Vehicles by type'),
            'swmChartHouseholdsByWard' => __('Households by ward'),
            'swmChartHouseholdCoverageByWard' => __('Household coverage by ward'),
            'swmChartHouseholdsVsVanPullers' => __('Households vs van pullers by ward'),
        ],
    ];
@endphp
<script>window.swmDashboardConfig = @json($swmDashboardConfig);</script>
<script src="{{ asset('js/vendor/chartjs-plugin-datalabels.min.js') }}"></script>
<script src="{{ asset('js/swm-dashboard.js') }}"></script>
<script>
    (function () {
        function hideLoaderByCanvasId(canvasId) {
            var loader = document.querySelector('[data-swm-chart-loader="' + canvasId + '"]');
            if (loader) {
                loader.classList.remove('d-flex');
                loader.classList.add('d-none');
                loader.style.display = 'none';
                loader.style.pointerEvents = 'none';
                loader.setAttribute('aria-busy', 'false');
            }
        }

        function hideAllLoaders() {
            document.querySelectorAll('[data-swm-chart-loader]').forEach(function (loader) {
                loader.classList.remove('d-flex');
                loader.classList.add('d-none');
                loader.style.display = 'none';
                loader.style.pointerEvents = 'none';
                loader.setAttribute('aria-busy', 'false');
            });
        }

        function hideLoadersForRenderedCharts() {
            var C = window.Chart;
            if (!C || !C.instances) {
                return;
            }
            Object.keys(C.instances).forEach(function (key) {
                var ch = C.instances[key];
                if (ch && ch.canvas && ch.canvas.id) {
                    hideLoaderByCanvasId(ch.canvas.id);
                }
            });
        }

        function startLoaderFallbackPolling() {
            var poll = setInterval(hideLoadersForRenderedCharts, 250);
            setTimeout(function () {
                clearInterval(poll);
                hideAllLoaders();
            }, 12000);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startLoaderFallbackPolling);
        } else {
            startLoaderFallbackPolling();
        }
    })();
</script>
@endpush
