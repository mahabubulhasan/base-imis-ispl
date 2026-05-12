@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
	<div class="card-header bg-transparent">
		<a href="{{ route('swm.landfills.index') }}" class="btn btn-info">{{__('Back to List')}}</a>
	</div>
	<div class="form-horizontal">
		<div class="card-body">
		<div class="form-group row">
    {!! Form::label('landfill_id', __('Landfill ID'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $landfill->landfill_id, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('name', __('Name'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $landfill->name, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('location', __('Location'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $landfill->location, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('operator_name', __('Operator Name'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $landfill->operator_name, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
			{!! Form::label('contact_number', __('Contact Number'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $landfill->contact_number, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('capacity', __('Capacity') . ' (' . __('Ton') . ')', ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $landfill->capacity, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('area', __('Area') . ' (' . __('Decimal') . ')', ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $landfill->area, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('landfill_type_id', __('Landfill Type'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, optional($landfill->landfillType)->name, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('source_sts_ids', __('Source STSs'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $sourceSts->pluck('name')->implode(', '), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('source_wards', __('Source Wards'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, implode(', ', $landfill->source_wards ?? []), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('waste_type_ids', __('Waste Types'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $wasteTypes->pluck('name')->implode(', '), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('segregation_practiced', __('Segregation Practiced?'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, is_null($landfill->segregation_practiced) ? '' : ($landfill->segregation_practiced ? __('Yes') : __('No')), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('reuse_practiced', __('Reuse Practiced?'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, is_null($landfill->reuse_practiced) ? '' : ($landfill->reuse_practiced ? __('Yes') : __('No')), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('treatment', __('Treatment Practiced?'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, is_null($landfill->treatment) ? '' : ($landfill->treatment ? __('Yes') : __('No')), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('weighbridge_facility_available', __('Weighbridge Facility Available?'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, is_null($landfill->weighbridge_facility_available) ? '' : ($landfill->weighbridge_facility_available ? __('Yes') : __('No')), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('boundary_wall_available', __('Boundary Wall Around the Landfill Area Available?'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, is_null($landfill->boundary_wall_available) ? '' : ($landfill->boundary_wall_available ? __('Yes') : __('No')), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('lighting_arrangement_available', __('Lighting Arrangement at the Landfill Site Available?'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, is_null($landfill->lighting_arrangement_available) ? '' : ($landfill->lighting_arrangement_available ? __('Yes') : __('No')), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('manpower_deployed', __('Number of Manpower Deployed at the Landfill Site'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $landfill->manpower_deployed, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('adequate_covering_arrangement_available', __('Adequate Covering Arrangement at the Landfill Site Available?'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, is_null($landfill->adequate_covering_arrangement_available) ? '' : ($landfill->adequate_covering_arrangement_available ? __('Yes') : __('No')), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('gas_control_system_available', __('System for Gas Control from the Filled Landfill Available?'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, is_null($landfill->gas_control_system_available) ? '' : ($landfill->gas_control_system_available ? __('Yes') : __('No')), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('leachate_collection_system_available', __('Leachate Collection System Available?'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, is_null($landfill->leachate_collection_system_available) ? '' : ($landfill->leachate_collection_system_available ? __('Yes') : __('No')), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('operational_status', __('Operational Status'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, ucfirst((string) $landfill->operational_status), ['class' => 'form-control']) !!}
			</div>
		</div>
		</div>
	</div>
</div>
@stop
