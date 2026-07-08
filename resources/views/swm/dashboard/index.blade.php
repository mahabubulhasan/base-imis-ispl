@extends('layouts.dashboard')

@section('title', $page_title)

@section('content_header_right')
@endsection

@push('style')
@php
    $swmDashboardCssPath = public_path('css/swm-dashboard.css');
    $swmDashboardCssVersion = file_exists($swmDashboardCssPath) ? filemtime($swmDashboardCssPath) : time();
    $visNetworkCssPath = public_path('css/vendor/vis-network.min.css');
    $visNetworkCssVersion = file_exists($visNetworkCssPath) ? filemtime($visNetworkCssPath) : time();
@endphp
<link rel="stylesheet" href="{{ asset('css/swm-dashboard.css') }}?v={{ $swmDashboardCssVersion }}">
<link rel="stylesheet" href="{{ asset('css/vendor/vis-network.min.css') }}?v={{ $visNetworkCssVersion }}">
@endpush

@section('content')
<div class="swm-dashboard-toolbar d-flex justify-content-between align-items-center flex-wrap mb-3">
    <form id="swm-dashboard-filter-form" class="swm-dashboard-filter-form form-inline d-flex flex-wrap align-items-center mb-0" method="get" action="{{ route('swm.dashboard-kpis.index') }}">
        <label for="to_month" class="col-form-label mb-0 mr-2 font-weight-bold">{{ __('Through Month') }}</label>
        <input type="month" id="to_month" name="to_month" class="form-control"
            max="{{ $dashboard['period']['max_to_month'] ?? now()->format('Y-m') }}"
            value="{{ $dashboard['period']['to_month'] ?? ($dashboard['period']['max_to_month'] ?? now()->format('Y-m')) }}"
            aria-label="{{ __('Through month') }}">
        <button type="submit" class="btn btn-info ml-2">{{ __('Apply') }}</button>
    </form>
    <button type="button" id="swm-dashboard-generate-report" class="btn btn-info swm-dashboard-generate-report flex-shrink-0 ml-2">{{ __('Generate Report') }}</button>
</div>

<div class="swm-dashboard">
    <div id="swm-dashboard-modules" class="swm-dashboard-modules">
        @foreach($dashboard['modules'] ?? [] as $key => $modulePayload)
            @php $viewName = $dashboard['moduleViews'][$key] ?? null; @endphp
            @if($viewName)
                @include($viewName, ['module' => $modulePayload])
            @endif
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
@php
    $swmDashboardConfig = [
        'indexUrl' => route('swm.dashboard-kpis.index'),
        'dataUrl' => route('swm.dashboard-kpis.data'),
        'modulesUrl' => route('swm.dashboard-kpis.modules'),
        'complianceReportUrl' => route('swm.dashboard-kpis.compliance-report'),
        'period' => $dashboard['period'] ?? [],
        'charts' => $dashboard['charts'] ?? [],
        'chartColors' => config('swm_dashboard.chart_colors', []),
        'wardAxisLabel' => __('Ward'),
        'serviceWardsAxisLabel' => __('Service Wards'),
    ];
@endphp
<script src="{{ asset('js/vendor/Chart.min.js') }}"></script>
@php
    $visNetworkJsPath = public_path('js/vendor/vis-network.min.js');
    $visNetworkJsVersion = file_exists($visNetworkJsPath) ? filemtime($visNetworkJsPath) : time();
@endphp
<script src="{{ asset('js/vendor/vis-network.min.js') }}?v={{ $visNetworkJsVersion }}"></script>
<script>
    window.swmDashboardConfig = @json($swmDashboardConfig);
</script>
@php
    $swmDashboardJsSource = resource_path('js/swm-dashboard.js');
    $swmDashboardJsPath = public_path('js/swm-dashboard.js');
    if (file_exists($swmDashboardJsSource)
        && (! file_exists($swmDashboardJsPath) || filemtime($swmDashboardJsSource) > filemtime($swmDashboardJsPath))) {
        @copy($swmDashboardJsSource, $swmDashboardJsPath);
    }
    $swmDashboardJsVersion = file_exists($swmDashboardJsPath) ? filemtime($swmDashboardJsPath) : time();
@endphp
<script src="{{ asset('js/swm-dashboard.js') }}?v={{ $swmDashboardJsVersion }}"></script>
@endpush
