@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
@php
    $stsWasteTypes = $stsLog->wasteTypes();
    $wasteShow = $stsLog->waste_type_name;
    if (! $wasteShow && $stsWasteTypes->isNotEmpty()) {
        $wasteShow = $stsWasteTypes->pluck('name')->implode(', ');
    }
    if (! $wasteShow) {
        $wasteShow = $stsLog->wasteType?->name;
    }
@endphp
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.sts-logs.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
        @can('Edit SW STS Log')
        <a href="{{ route('swm.sts-logs.edit', $stsLog->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
        @endcan
    </div>
    <div class="form-horizontal swm-sts-log-form-mobile app-mobile-form">
        <div class="card-body">
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('STS Loading Log ID') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $stsLog->id, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Entry Date and Time') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $stsLog->entry_at?->format('Y-m-d H:i'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Operation Date') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $stsLog->operation_date?->format('Y-m-d'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Vehicle Number') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $stsLog->vehicle?->vehicle_number ?: '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Vehicle Type') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $stsLog->vehicle_type_name ?: ($stsLog->vehicleType?->name ?: '—'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Driver Name') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $stsLog->driver_name ?: ($stsLog->driver?->name ?: '—'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('STS Name') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $stsLog->sts_name ?: ($stsLog->sts?->name ?: '—'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Waste Type') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteShow ?: '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Quantity (Ton)') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $stsLog->quantity_ton ?? '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Source Wards') }}</label>
                <div class="col-sm-3">{!! Form::label(null, is_array($stsLog->source_wards) && count($stsLog->source_wards) ? implode(', ', $stsLog->source_wards) : '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Remarks') }}</label>
                <div class="col-sm-3">{!! Form::textarea(null, $stsLog->remarks ?: '—', ['class' => 'form-control', 'rows' => 2, 'readonly' => true]) !!}</div>
            </div>
        </div>
    </div>
</div>
@stop
