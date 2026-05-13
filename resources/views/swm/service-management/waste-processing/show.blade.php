@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.waste-processing.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
        @can('Edit SW Waste Processing')
        <a href="{{ route('swm.waste-processing.edit', $wasteProcessingLog->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
        @endcan
    </div>
    <div class="app-mobile-form">
        <div class="card-body">
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Waste Processing Log ID') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $wasteProcessingLog->id }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Entry Date and Time') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $wasteProcessingLog->entry_at?->format('Y-m-d H:i') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Report Date') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $wasteProcessingLog->report_date?->format('Y-m-d') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Reporting Month') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $wasteProcessingLog->reporting_month?->format('M Y') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Quantity of Waste Received (Ton)') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $wasteProcessingLog->waste_received_ton ?? '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Organic Waste Composted (Ton)') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $wasteProcessingLog->organic_waste_composted_ton ?? '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Inorganic Non-biodegradable Waste Recycled (Ton)') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $wasteProcessingLog->inorganic_waste_recycled_ton ?? '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Waste Incinerated (Ton)') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $wasteProcessingLog->waste_incinerated_ton ?? '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Waste Burned in Open Air (Ton)') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $wasteProcessingLog->waste_burned_open_air_ton ?? '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Residual Waste Landfilled (Ton)') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $wasteProcessingLog->residual_waste_landfilled_ton ?? '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Remarks') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $wasteProcessingLog->remarks ?: '—' }}</p></div>
            </div>
        </div>
    </div>
</div>
@stop
