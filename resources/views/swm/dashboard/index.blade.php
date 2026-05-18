@extends('layouts.dashboard')

@section('title', $page_title)

@section('content_header_right')
<form id="swm-dashboard-filter-form" class="form-inline swm-dashboard-filter-form d-flex flex-wrap align-items-center justify-content-sm-end gap-2" method="get" action="{{ route('swm.dashboard-kpis.index') }}">
    <input type="month" id="to_month" name="to_month" class="form-control"
        max="{{ $dashboard['period']['max_to_month'] ?? now()->subMonth()->format('Y-m') }}"
        value="{{ $dashboard['period']['to_month'] ?? ($dashboard['period']['max_to_month'] ?? now()->subMonth()->format('Y-m')) }}"
        aria-label="{{ __('To month') }}">
    <button type="submit" class="btn btn-info">{{ __('Apply') }}</button>
</form>
@endsection

@push('style')
@php
    $swmDashboardCssPath = public_path('css/swm-dashboard.css');
    $swmDashboardCssVersion = file_exists($swmDashboardCssPath) ? filemtime($swmDashboardCssPath) : time();
@endphp
<link rel="stylesheet" href="{{ asset('css/swm-dashboard.css') }}?v={{ $swmDashboardCssVersion }}">
@endpush

@section('content')
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
        'dataUrl' => route('swm.dashboard-kpis.data'),
        'period' => $dashboard['period'] ?? [],
        'charts' => $dashboard['charts'] ?? [],
    ];
@endphp
<script src="{{ asset('js/vendor/Chart.min.js') }}"></script>
<script>
    window.swmDashboardConfig = @json($swmDashboardConfig);
</script>
@php
    $swmDashboardJsPath = public_path('js/swm-dashboard.js');
    $swmDashboardJsVersion = file_exists($swmDashboardJsPath) ? filemtime($swmDashboardJsPath) : time();
@endphp
<script src="{{ asset('js/swm-dashboard.js') }}?v={{ $swmDashboardJsVersion }}"></script>
@endpush
