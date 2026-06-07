@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    <div class="card-header">
        <a href="{{ $backRoute }}" class="btn btn-info">{{ __('Back to List') }}</a>
    </div>
    <div class="app-mobile-form">
    <div class="card-body">
        {!! Form::open(['route' => $storeRoute, 'files' => true, 'class' => 'form-horizontal']) !!}
        <div class="form-group row required">
            {!! Form::label('import_file', __('Excel file (.xlsx)'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-6">
                {!! Form::file('import_file', ['class' => 'form-control', 'accept' => '.xlsx']) !!}
            </div>
        </div>
        <div class="card-footer">
            {!! Form::submit(__('Import'), ['class' => 'btn btn-info']) !!}
        </div>
        {!! Form::close() !!}
    </div>
    </div>
</div>
@stop
