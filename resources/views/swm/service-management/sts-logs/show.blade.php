@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.sts-logs.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
        @can('Edit SW STS Log')
        <a href="{{ route('swm.sts-logs.edit', $stsLog->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
        @endcan
    </div>
    <div class="swm-sts-log-form-mobile app-mobile-form">
        <div class="card-body">
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('STS Log ID') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $stsLog->id }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Entry Date and Time') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $stsLog->entry_at?->format('Y-m-d H:i') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Operation Date') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $stsLog->operation_date?->format('Y-m-d') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Vehicle Number') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $stsLog->vehicle?->vehicle_number ?: '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Vehicle Type') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $stsLog->vehicle_type_name ?: ($stsLog->vehicleType?->name ?: '—') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Driver Name') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $stsLog->driver_name ?: ($stsLog->driver?->name ?: '—') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('STS Name') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $stsLog->sts_name ?: ($stsLog->sts?->name ?: '—') }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Waste Type') }}</span>
                <div class="col-sm-9">
                    <p class="form-control-plaintext mb-0">
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
                        {{ $wasteShow ?: '—' }}
                    </p>
                </div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Quantity (Ton)') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $stsLog->quantity_ton ?? '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Source Wards') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ is_array($stsLog->source_wards) && count($stsLog->source_wards) ? implode(', ', $stsLog->source_wards) : '—' }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Operation Status') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ \App\Models\Swm\StsLog::statusOptions()[$stsLog->operation_status] ?? $stsLog->operation_status }}</p></div>
            </div>
            <div class="form-group row">
                <span class="col-sm-3 control-label">{{ __('Remarks') }}</span>
                <div class="col-sm-9"><p class="form-control-plaintext mb-0">{{ $stsLog->remarks ?: '—' }}</p></div>
            </div>
        </div>
    </div>
</div>
@stop
