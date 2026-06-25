@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
@php
    $lfWasteTypes = $landfillLog->wasteTypes();
    $wasteShowLf = $landfillLog->waste_type_name;
    if (! $wasteShowLf && $lfWasteTypes->isNotEmpty()) {
        $wasteShowLf = $lfWasteTypes->pluck('name')->implode(', ');
    }
    if (! $wasteShowLf) {
        $wasteShowLf = $landfillLog->wasteType?->name;
    }
    $sourceStsNames = \App\Models\Swm\Sts::query()
        ->whereIn('id', is_array($landfillLog->source_sts_ids) ? $landfillLog->source_sts_ids : [])
        ->whereNull('deleted_at')
        ->orderBy('name')
        ->pluck('name')
        ->all();
@endphp
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.landfill-logs.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
        @can('Edit SW Landfill Log')
        <a href="{{ route('swm.landfill-logs.edit', $landfillLog->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
        @endcan
    </div>
    <div class="form-horizontal swm-landfill-log-form-mobile app-mobile-form">
        <div class="card-body">
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Landfill Loading Log ID') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $landfillLog->id, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Entry Date and Time') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $landfillLog->entry_at?->format('Y-m-d H:i'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Operation Date') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $landfillLog->operation_date?->format('Y-m-d'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Vehicle No.') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $landfillLog->vehicle?->vehicle_number ?: '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Vehicle Type') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $landfillLog->vehicle_type_name ?: ($landfillLog->vehicleType?->name ?: '—'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Driver Name') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $landfillLog->driver_name ?: ($landfillLog->driver?->name ?: '—'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Landfill Name') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $landfillLog->landfill_name ?: ($landfillLog->landfill?->name ?: '—'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Waste Type') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteShowLf ?: '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Quantity (Ton)') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $landfillLog->quantity_ton ?? '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Source STSs') }}</label>
                <div class="col-sm-3">{!! Form::label(null, count($sourceStsNames) ? implode(', ', $sourceStsNames) : '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('STS Source Wards') }}</label>
                <div class="col-sm-3">{!! Form::label(null, count($stsSourceWards) ? implode(', ', $stsSourceWards) : '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Other Source Wards') }}</label>
                <div class="col-sm-3">{!! Form::label(null, count($otherSourceWards) ? implode(', ', $otherSourceWards) : '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Remarks') }}</label>
                <div class="col-sm-3">{!! Form::textarea(null, $landfillLog->remarks ?: '—', ['class' => 'form-control', 'rows' => 2, 'readonly' => true]) !!}</div>
            </div>
        </div>
    </div>
</div>
@stop
