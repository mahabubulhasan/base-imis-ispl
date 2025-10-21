<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', __('User Detail'))
@section('content')
<div class="col-md-12">
<div class="card card-info">

    <div class="form-horizontal">
        <div class="card-footer">
            <a href="{{ action('Auth\UserController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
        </div>
        <div class="card-body">
            <div class="form-group row">
                {!! Form::label(__('Full Name'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label($userDetail->name,null,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label(__('Gender'), null, ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(
                        $userDetail->gender == 'Male' ? 'Male' :
                        ($userDetail->gender == 'Female' ? 'Female' : 'Others'),
                        null,
                        ['class' => 'form-control']
                    ) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label(__('Username'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                {!! Form::label('username', $userDetail->username, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label(__('Email'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label('email',$userDetail->email,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label(__('User Type'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(@$userDetail->user_type,null,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label(__('Role'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(implode(', ', $userRoles),null,['class' => 'form-control']) !!}
                </div>
            </div>
            {{--<div class="form-group row">
                {!! Form::label('ward',null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label($userDetail->ward,null,['class' => 'form-control']) !!}
                </div>
            </div>--}}
          

            @isset($treatmentPlants->name)
            <div class="form-group row">
                {!! Form::label(__('Treatment Plant'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(@$treatmentPlants->name,null,['class' => 'form-control']) !!}
                </div>
            </div>
            @endisset
            @isset($serviceProviders->company_name)
                <div class="form-group row">
                    {!! Form::label(__('Service Provider'),null,['class' => 'col-sm-3 control-label']) !!}
                    <div class="col-sm-3">
                        {!! Form::label(@$serviceProviders->company_name,null,['class' => 'form-control']) !!}
                    </div>
                </div>
            @endisset
            @isset($helpDesks->name)
            <div class="form-group row">
                {!! Form::label(__('Help Desk'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(@$helpDesks->name,null,['class' => 'form-control']) !!}
                </div>
            </div>
            @endisset

            <div class="form-group row">
                {!! Form::label(__('Status'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label($status,null,['class' => 'form-control']) !!}
                </div>
            </div>

        </div><!-- /.card-body -->
    </div>

</div><!-- /.card -->
</div>
@stop

