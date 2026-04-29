@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
	<div class="card-header bg-transparent">
		<a href="{{ route('swm.sts.index') }}" class="btn btn-info">{{__('Back to List')}}</a>
	</div>
	<div class="form-horizontal">
		<div class="card-body">
		<div class="form-group row">
    {!! Form::label('name', __('Name'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">{!! Form::label(null, $sts->name, ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
    {!! Form::label('location', __('Location'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">{!! Form::label(null, $sts->location, ['class' => 'form-control']) !!}</div>
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
			{!! Form::label('segregation_practiced', __('Segregation Practiced'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">{!! Form::label(null, $sts->segregation_practiced ? __('Yes') : __('No'), ['class' => 'form-control']) !!}</div>
		</div>
		<div class="form-group row">
			{!! Form::label('destination', __('Destination Landfill'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">{!! Form::label(null, optional($sts->landfill)->name, ['class' => 'form-control']) !!}</div>
		</div>
		</div>
	</div>
</div>
@stop
