<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ action('Language\LanguageController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>

    </div><!-- /.card-header -->
    <div class="form-horizontal">
        <div class="card-body">


            <div class="form-group row">
                {!! Form::label(__('Language'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$language->name,['class' => 'form-control']) !!}
                </div>
            </div>


            <div class="form-group row">
                {!! Form::label(__('Code'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$language->code,['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label(__('Status'), null, ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null, $language->status == 1 ? 'Active' : 'Disabled', ['class' => 'form-control']) !!}
                </div>
            </div>



        </div><!-- /.box-body -->
    </div>
</div><!-- /.box -->
@stop

