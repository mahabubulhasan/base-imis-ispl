@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    <div class="card-header">
        <a href="{{ route('swm.bill-collection-payments.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    </div>
    <div class="app-mobile-form">
    <div class="card-body">
        {!! Form::open(['route' => 'swm.bill-collection-payments.import.store', 'files' => true, 'class' => 'form-horizontal']) !!}
        <div class="form-group row required">
            {!! Form::label('import_file', __('CSV or Excel file'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-6">
                {!! Form::file('import_file', ['class' => 'form-control', 'accept' => '.csv,.xlsx']) !!}
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
