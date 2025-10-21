{{-- Last Modified Date: 14-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL) --}}
@extends('layouts.dashboard')

@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ action('Fsm\EmployeeInfoController@index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    </div><!-- /.box-header -->
    <div class="form-horizontal">
        <div class="card-body">
            <div class="form-group row">
                {!! Form::label('service_provider_id', __('Service Provider Name'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $service_provider_id, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('name', __('Employee Name'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $employeeInfos->name, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('gender', __('Employee Gender'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $employeeInfos->gender, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('contact_number', __('Employee Contact Number'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $employeeInfos->contact_number, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('dob', __('Date of Birth'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $employeeInfos->dob, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('address', __('Address'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $employeeInfos->address, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('employee_type', __('Designation'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $employeeInfos->employee_type, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('year_of_experience', __('Working Experience (Years)'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $employeeInfos->year_of_experience, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('wage', __('Monthly Remuneration'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $employeeInfos->wage, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div id="license_number" class="form-group row">
                {!! Form::label('license_number', __('Driving License Number'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $employeeInfos->license_number, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div id="license_issue_date" class="form-group row">
                {!! Form::label('license_issue_date', __('License Issue Date'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $employeeInfos->license_issue_date, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('training_received', __('Training Received (if any)'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $employeeInfos->training_status, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('status', __('Status'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $status, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label('employment_start', __('Job Start Date'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $employeeInfos->employment_start, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div id="employment_end" class="form-group row">
                {!! Form::label('employment_end', __('Job End Date'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $employeeInfos->employment_end, ['class' => 'form-control']) !!}
                </div>
            </div>

        </div><!-- /.box-body -->
    </div>
</div><!-- /.box -->
@stop
