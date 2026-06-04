@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.lic.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
        @can('Edit SW LIC')
        <a href="{{ route('swm.lic.edit', $lic->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
        @endcan
    </div>
    <div class="form-horizontal swm-lic-form-mobile app-mobile-form">
        <div class="card-body">
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('LIC ID') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $lic->lic_id, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __("LIC Representative's Name") }}</label>
                <div class="col-sm-3">{!! Form::label(null, $lic->representative_name, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Contact No.') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $lic->contact_no, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Number of HHs') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $lic->number_of_hhs, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Total Population') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $lic->total_population, ['class' => 'form-control']) !!}</div>
            </div>
        </div>
    </div>
</div>
@stop
