@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.landfill-logs.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
        @can('Edit SW Landfill Log')
        <a href="{{ route('swm.landfill-logs.edit', $landfillLog->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
        @endcan
    </div>
    <div class="app-mobile-form">
        <div class="card-body">
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Landfill Log ID') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $landfillLog->id }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Entry Date and Time') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $landfillLog->entry_at?->format('Y-m-d H:i') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Operation Date') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $landfillLog->operation_date?->format('Y-m-d') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Organization') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $landfillLog->organization?->name }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Vehicle Number') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $landfillLog->vehicle?->vehicle_number ?: '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Vehicle Type') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $landfillLog->vehicle_type_name ?: ($landfillLog->vehicleType?->name ?: '—') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Driver Name') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $landfillLog->driver_name ?: ($landfillLog->driver?->name ?: '—') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Landfill Name') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $landfillLog->landfill_name ?: ($landfillLog->landfill?->name ?: '—') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Waste Type') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $landfillLog->waste_type_name ?: ($landfillLog->wasteType?->name ?: '—') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Quantity (Ton)') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $landfillLog->quantity_ton ?? '—' }}</p></div>
            </div>
            @if($landfillLog->weighbridge_weight_ton === null)
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Effective Quantity (Ton)') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $landfillLog->quantity_ton ?? '—' }}</p></div>
            </div>
            @else
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Weighbridge Weight (Ton)') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $landfillLog->weighbridge_weight_ton ?? '—' }}</p></div>
            </div>
            @endif
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Source STSs') }}</span>
                <div class="col-sm-9">
                    <p class="form-control-plaintext mb-0">
                        @php
                            $sourceStsNames = \App\Models\Swm\Sts::query()
                                ->whereIn('id', is_array($landfillLog->source_sts_ids) ? $landfillLog->source_sts_ids : [])
                                ->whereNull('deleted_at')
                                ->orderBy('name')
                                ->pluck('name')
                                ->all();
                        @endphp
                        {{ count($sourceStsNames) ? implode(', ', $sourceStsNames) : '—' }}
                    </p>
                </div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Source Wards') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ is_array($landfillLog->source_wards) && count($landfillLog->source_wards) ? implode(', ', $landfillLog->source_wards) : '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Operation Status') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ \App\Models\Swm\LandfillLog::statusOptions()[$landfillLog->operation_status] ?? $landfillLog->operation_status }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Remarks') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $landfillLog->remarks ?: '—' }}</p></div>
            </div>
        </div>
    </div>
</div>
@stop
