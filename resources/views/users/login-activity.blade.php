<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', __('Login Activity'))
@section('content')
<div class="col-md-12">
<div class="card card-info">
    <div class="card-footer">
        <a href="{{ action('Auth\UserController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
    </div>
    <div class="form-horizontal">
        <div class="card-body">
            <div class="form-group row">
                {!! Form::label(__('Full Name'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label($userDetail->name,null,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label(__('Last Login At'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                {!! Form::label($last_login_at,null,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label(__('Last Login IP'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                {!! Form::label($last_login_ip,null,['class' => 'form-control']) !!}
                </div>
            </div>

        </div><!-- /.card-body -->
    </div>

</div><!-- /.card -->
</div>
@stop
