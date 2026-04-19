@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.lic.index') }}" class="btn btn-info">{{__('Back to List')}}</a>
    </div>
    <div class="form-horizontal">
        <div class="card-body">
            <div class="form-group row">
                {!! Form::label('lic_id', __('LIC ID'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $lic->lic_id, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                {!! Form::label('representative_name', __("LIC Representative's Name"), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $lic->representative_name, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                {!! Form::label('contact_no', __('Contact No.'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $lic->contact_no, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                {!! Form::label('number_of_hhs', __('Number of HHs'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $lic->number_of_hhs, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                {!! Form::label('total_population', __('Total Population'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">{!! Form::label(null, $lic->total_population, ['class' => 'form-control']) !!}</div>
            </div>
        </div>
    </div>
</div>
@stop
