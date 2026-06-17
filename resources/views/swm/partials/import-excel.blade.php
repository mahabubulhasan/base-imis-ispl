@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    {!! Form::open(['route' => $storeRoute, 'files' => true, 'class' => 'form-horizontal']) !!}
        <div class="card-body">
            <div class="form-group row required">
                {!! Form::label('import_file', __('Excel file (.xlsx)'), ['class' => 'col-sm-3 control-label', 'style' => 'padding-top:3px;']) !!}
                <div class="col-sm-3">
                    {!! Form::file('import_file', ['accept' => '.xlsx']) !!}
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ $backRoute }}" class="btn btn-info">{{ __('Back to List') }}</a>
            {!! Form::submit(__('Import'), ['class' => 'btn btn-info']) !!}
        </div>
    {!! Form::close() !!}
</div>
@stop
