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
    <div class="form-horizontal swm-waste-processing-form-mobile app-mobile-form">
        <div class="card-body">
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Waste Processing Log ID') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteProcessingLog->id, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Entry Date and Time') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteProcessingLog->entry_at?->format('Y-m-d H:i'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Report Date') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteProcessingLog->report_date?->format('Y-m-d'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Reporting Month') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteProcessingLog->reporting_month?->format('M Y'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Waste Processing Site Name') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteProcessingLog->waste_processing_site_name ?: '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Quantity of Waste Received (Ton)') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteProcessingLog->waste_received_ton ?? '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Organic Waste Composted (Ton)') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteProcessingLog->organic_waste_composted_ton ?? '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Inorganic Non-biodegradable Waste Recycled (Ton)') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteProcessingLog->inorganic_waste_recycled_ton ?? '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Waste Incinerated (Ton)') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteProcessingLog->waste_incinerated_ton ?? '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Waste Burned in Open Air (Ton)') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteProcessingLog->waste_burned_open_air_ton ?? '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Residual Waste Landfilled (Ton)') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteProcessingLog->residual_waste_landfilled_ton ?? '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Remarks') }}</label>
                <div class="col-sm-3">{!! Form::textarea(null, $wasteProcessingLog->remarks ?: '—', ['class' => 'form-control', 'rows' => 2, 'readonly' => true]) !!}</div>
            </div>
        </div>
    </div>
</div>
@stop
