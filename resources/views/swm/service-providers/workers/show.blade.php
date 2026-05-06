@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
	<div class="card-header bg-transparent">
		<a href="{{ route('swm.workers.index') }}" class="btn btn-info">{{__('Back to List')}}</a>
	</div>
	<div class="form-horizontal">
		<div class="card-body">
		<div class="form-group row">
    {!! Form::label('worker_id_no', __('ID'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $worker->worker_id_no, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('name', __('Name'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $worker->name, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('age', __('Age'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $worker->age, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
			{!! Form::label('gender', __('Gender'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->gender, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('mobile', __('Contact'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->mobile, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('work_type_id', __('Worker Type'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, optional($worker->workType)->name, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('service_area', __('Service Area'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->service_area, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('employment_type', __('Employment Type'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->employment_type, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('organization_id', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, optional($worker->organization)->name, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('department', __('Department'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->department, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('supervisor_name', __('Supervisor Name'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->supervisor_name, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('total_work_experience_years', __('Total Work Experience'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->total_work_experience_years, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('organization_work_experience_years', __('Work Experience in This Organization'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->organization_work_experience_years, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('education_level', __('Education Level'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->education_level, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('education_level_other', __('Education'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->education_level_other, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('employee_id', __('Employee ID'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->employee_id, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('national_id_no', __('National ID No.'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->national_id_no, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('status', __('Status'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->status, ['class' => 'form-control']) !!}
			</div>
		</div>
		</div>
	</div>
</div>
@stop
