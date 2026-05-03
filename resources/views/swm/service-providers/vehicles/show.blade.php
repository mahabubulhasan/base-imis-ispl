@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@php
    if ($vehicle->dumping_place_kind === 'sts') {
        $dumping = optional($vehicle->dumpingSts)->name;
    } elseif ($vehicle->dumping_place_kind === 'landfill') {
        $dumping = optional($vehicle->dumpingLandfill)->name;
    } elseif ($vehicle->dumping_place_kind === 'other') {
        $dumping = $vehicle->dumping_place_other;
    } else {
        $dumping = '';
    }
@endphp
<div class="card card-info">
	<div class="card-header bg-transparent">
		<a href="{{ route('swm.vehicles.index') }}" class="btn btn-info">{{__('Back to List')}}</a>
	</div>
	<div class="form-horizontal">
		<div class="card-body">
		<div class="form-group row">
    {!! Form::label('organization', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, optional($vehicle->organization)->name, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('vehicle_type', __('Vehicle Type'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, optional($vehicle->vehicleType)->name, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('vehicle_number', __('Vehicle Number'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $vehicle->vehicle_number, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('vehicle_id_no', __('Vehicle ID'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $vehicle->vehicle_id_no, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('chassis_no', __('Chassis No.'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $vehicle->chassis_no, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('capacity', __('Capacity') . ' (' . __('Ton') . ')', ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $vehicle->capacity, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('driver', __('Driver'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, optional($vehicle->driver)->name, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('dumping', __('Dumping Place'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $dumping, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('service_area', __('Service Area'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $vehicle->service_area, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('fuel_type', __('Fuel Type'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $vehicle->fuel_type, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('operational_type', __('Operational Type'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $vehicle->operational_type, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('vehicle_registration_no', __('Vehicle Registration No.'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $vehicle->vehicle_registration_no, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('engine_no', __('Engine No.'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $vehicle->engine_no, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('status', __('Status'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $vehicle->status, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('last_maintenance_year', __('Last Maintenance (Year)'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $vehicle->last_maintenance_year, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('remarks', __('Remarks'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $vehicle->remarks, ['class' => 'form-control']) !!}
    </div>
		</div>
		</div>
	</div>
</div>
@stop
