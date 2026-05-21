@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
	<div class="card-header bg-transparent">
		<a href="{{ route('swm.organizations.index') }}" class="btn btn-info">{{__('Back to List')}}</a>
	</div>
	<div class="form-horizontal">
		<div class="card-body">
		<div class="form-group row">
    {!! Form::label('name', __('Organization Name'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $organization->name, ['class' => 'form-control']) !!}
    </div>
		</div>

		<div class="form-group row">
			{!! Form::label('email', __('Email'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $organization->email, ['class' => 'form-control']) !!}
			</div>
		</div>

		<div class="form-group row">
			{!! Form::label('address', __('Address'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $organization->address, ['class' => 'form-control']) !!}
			</div>
		</div>

		<div class="form-group row">
			{!! Form::label('contact_person_name', __('Contact Person Name'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $organization->contact_person_name, ['class' => 'form-control']) !!}
			</div>
		</div>

		<div class="form-group row">
			{!! Form::label('contact_number', __('Contact Number'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $organization->contact_number, ['class' => 'form-control']) !!}
			</div>
		</div>

		<div class="form-group row">
			{!! Form::label('organization_type_id', __('Organization Type'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $organization->organization_type_label, ['class' => 'form-control']) !!}
			</div>
		</div>

		<div class="form-group row">
			{!! Form::label('service_wards', __('Service Wards'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, implode(', ', $organization->service_wards ?? []), ['class' => 'form-control']) !!}
			</div>
		</div>

		<div class="form-group row">
			{!! Form::label('remarks', __('Remarks'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $organization->remarks, ['class' => 'form-control']) !!}
			</div>
		</div>

		<div class="form-group row">
			{!! Form::label('status', __('Status'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $status, ['class' => 'form-control']) !!}
			</div>
		</div>

		</div>
	</div>
</div>
@stop
