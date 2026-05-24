@extends('layouts.dashboard')

@section('title', $page_title)

@push('style')
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&family=Noto+Serif+Bengali:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/doe-compliance-report.css') }}?v={{ filemtime(public_path('css/doe-compliance-report.css')) }}">
@endpush

@section('content_header_right')
<a href="{{ route('swm.dashboard-kpis.index') }}" class="doe-report-back-btn">{{ __('Back to Dashboard') }}</a>
    @endsection

@section('content')
@php
    $r = $report;
    $wq = $r['waste_quantity'] ?? [];
    $wp = $r['waste_processing'] ?? [];
    $inst = $r['institution'] ?? [];
    $storage = $r['storage'] ?? [];
    $lic = $r['lic'] ?? [];
    $lfInfo = $r['landfill_info'] ?? [];
    $autoTip = __('Auto-filled from IMIS');
    $bnDigits = static fn ($value) => strtr((string) $value, [
        '0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
        '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯',
    ]);
@endphp

<div class="doe-report-page">
    <div class="doe-report-toolbar">
        <div class="doe-report-toolbar-group">
            <label for="doe-report-year">{{ __('Reporting Year') }}</label>
            <select id="doe-report-year" class="doe-report-year-select">
                @foreach($years as $y)
                    <option value="{{ $y }}" @selected($y === $selectedYear)>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" id="doe-report-refresh" class="doe-report-toolbar-btn">{{ __('Refresh Data') }}</button>
    </div>

    <form id="doe-compliance-form" method="post" action="{{ route('swm.dashboard-kpis.compliance-report.pdf') }}" target="_blank">
        @csrf
        <input type="hidden" name="year" id="doe-form-year" value="{{ $selectedYear }}">

        <div class="page">
            <header class="topbar">
                <div class="subtitle"><span class="doe-report-municipality-name">{{ $inst['org_name'] ?? '' }}</span></div>
                <h1>কঠিন বর্জ্য ব্যবস্থাপনা সংক্রান্ত বার্ষিক প্রতিবেদন</h1>
                <div class="subtitle">পরিবেশ অধিদপ্তরের জন্য প্রস্তুতকৃত <span class="doe-report-display-year">{{ $bnDigits($selectedYear) }}</span> সালের প্রতিবেদন</div>
            </header>

            <section class="section">
                {{-- <div class="section-header"><h2>প্রতিবেদনের পরিচিতি</h2></div> --}}
                <div class="section-body">
                    <div class="grid">
                        <div class="field">
                            <label><span class="text">প্রতিবেদন নম্বর</span></label>
                            <input name="report_no" type="text" />
                        </div>
                        <div class="field">
                            <label><span class="text">তারিখ</span></label>
                            <input name="report_date" type="date" value="{{ now()->toDateString() }}" />
                        </div>
                    </div>
                </div>
            </section>

            @include('swm.dashboard.compliance-report.sections.institution')
            @include('swm.dashboard.compliance-report.sections.waste-quantity')
            @include('swm.dashboard.compliance-report.sections.storage')
            @include('swm.dashboard.compliance-report.sections.transport')
            @include('swm.dashboard.compliance-report.sections.misc')

            <div class="footer-actions">
                <button type="button" class="btn btn-outline" id="doe-report-reset">{{ __('Reset') }}</button>
                <button type="submit" class="btn btn-solid">{{ __('Download PDF') }}</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    window.doeComplianceReportConfig = {
        dataUrl: @json(route('swm.dashboard-kpis.compliance-report.data')),
        landfillTypeOptions: @json($landfillTypeOptions),
        wasteBinTypeOptions: @json($report['waste_bin_type_options'] ?? []),
        wasteBinCatalog: @json($report['waste_bin_catalog'] ?? []),
        vehicleTypeOptions: @json($report['vehicle_type_options'] ?? []),
        vehicleCatalog: @json($report['vehicle_catalog'] ?? []),
        landfillCatalog: @json($report['landfill_catalog'] ?? []),
        defaultLandfillUnit: @json(config('doe_compliance_report.default_landfill_unit', 'টন')),
        defaultLandfillAreaUnit: @json(config('doe_compliance_report.default_landfill_area_unit', 'একর')),
        autoTip: @json($autoTip),
        initialReport: @json($report),
    };
</script>
<script src="{{ asset('js/doe-compliance-report.js') }}?v={{ filemtime(public_path('js/doe-compliance-report.js')) }}"></script>
@endpush
