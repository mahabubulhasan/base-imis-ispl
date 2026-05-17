@extends('layouts.dashboard')

@section('title', $page_title)

@push('style')
<link rel="stylesheet" href="{{ asset('css/swm-dashboard.css') }}">
@endpush

@section('content')
<div class="swm-dashboard">
    <div class="swm-page-header">
        <div>
            <h2>{{ $page_title }}</h2>
            <p class="swm-page-meta">{{ __('Data from the beginning through end of selected month.') }}</p>
        </div>
        <form id="swm-dashboard-filter-form" class="form-inline d-flex flex-wrap align-items-end gap-2" method="get" action="{{ route('swm.dashboard-kpis.index') }}">
            <div>
                <label for="to_month" class="d-block small text-muted mb-0">{{ __('To month') }}</label>
                <input type="month" id="to_month" name="to_month" class="form-control"
                    max="{{ $dashboard['period']['max_to_month'] ?? now()->subMonth()->format('Y-m') }}"
                    value="{{ $dashboard['period']['to_month'] ?? ($dashboard['period']['max_to_month'] ?? now()->subMonth()->format('Y-m')) }}">
            </div>
            <button type="submit" class="btn btn-info">{{ __('Apply') }}</button>
        </form>
    </div>

    <div id="swm-dashboard-modules">
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
