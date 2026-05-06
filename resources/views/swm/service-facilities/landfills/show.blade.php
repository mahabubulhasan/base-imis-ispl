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
				{!! Form::label(null, $landfill->segregation_practiced ? __('Yes') : __('No'), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('reuse_practiced', __('Reuse Practiced?'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $landfill->reuse_practiced ? __('Yes') : __('No'), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('treatment', __('Treatment Practiced?'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $landfill->treatment ? __('Yes') : __('No'), ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('monthly_waste_for_composting', __('Monthly Amount of Waste Provided for Composting') . ' (' . __('Ton') . ')', ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $landfill->monthly_waste_for_composting, ['class' => 'form-control']) !!}
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
