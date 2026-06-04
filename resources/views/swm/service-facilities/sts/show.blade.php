@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
	<div class="card-header bg-transparent">
		<a href="{{ route('swm.sts.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
		@can('Edit SW STS')
		<a href="{{ route('swm.sts.edit', $sts->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
		@endcan
	</div>
	<div class="form-horizontal swm-sts-form-mobile app-mobile-form">
		<div class="card-body">
		<div class="form-group row">
    {!! Form::label('sts_id', __('STS ID'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">{!! Form::label(null, $sts->sts_id, ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
    {!! Form::label('name', __('Name'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">{!! Form::label(null, $sts->name, ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
    {!! Form::label('location', __('Location'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">{!! Form::label(null, $sts->location, ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
    {!! Form::label('ward_no', __('Ward No.'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">{!! Form::label(null, $sts->ward_no, ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
    {!! Form::label('road_id', __('Road No.'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">{!! Form::label(null, $sts->road_id, ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
    {!! Form::label('road_name', __('Road Name'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">{!! Form::label(null, $sts->road_name, ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
    {!! Form::label('latitude', __('Latitude'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">{!! Form::label(null, $sts->latitude, ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
    {!! Form::label('longitude', __('Longitude'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">{!! Form::label(null, $sts->longitude, ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
    {!! Form::label('operator_name', __('Operator Name'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">{!! Form::label(null, $sts->operator_name, ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
			{!! Form::label('contact_number', __('Contact Number'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">{!! Form::label(null, $sts->contact_number, ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
			{!! Form::label('capacity', __('Capacity') . ' (' . __('Ton') . ')', ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">{!! Form::label(null, $sts->capacity, ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
			{!! Form::label('area', __('Area') . ' (' . __('Decimal') . ')', ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">{!! Form::label(null, $sts->area, ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
			{!! Form::label('source_wards', __('Source Wards'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">{!! Form::label(null, implode(', ', $sts->source_wards ?? []), ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
			{!! Form::label('waste_type_ids', __('Waste Type'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">{!! Form::label(null, $wasteTypes->pluck('name')->implode(', '), ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
			{!! Form::label('segregation_practiced', __('Segregation Practiced?'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">{!! Form::label(null, $sts->segregation_practiced ? __('Yes') : __('No'), ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
			{!! Form::label('destination', __('Destination Landfill'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">{!! Form::label(null, optional($sts->landfill)->name, ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
			{!! Form::label('operational_status', __('Operational Status'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">{!! Form::label(null, $sts->operational_status === 'active' ? __('Active') : ($sts->operational_status === 'inactive' ? __('Inactive') : ''), ['class' => 'form-control']) !!}</div>
		</div>
		</div>
	</div>
</div>
@stop
