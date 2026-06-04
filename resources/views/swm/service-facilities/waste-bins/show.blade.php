@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.waste-bins.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
        @can('Edit SW Waste Bin')
        <a href="{{ route('swm.waste-bins.edit', $wasteBin->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
        @endcan
    </div>
    <div class="form-horizontal swm-waste-bin-form-mobile app-mobile-form">
        <div class="card-body">
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Waste Bin ID') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteBin->waste_bin_id, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Waste Bin Type') }}</label>
                <div class="col-sm-3">{!! Form::label(null, optional($wasteBin->wasteBinType)->name, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Capacity (kg)') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteBin->total_capacity_kg, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Placed at Buildings?') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteBin->placed_at_buildings ? __('Yes') : __('No'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('BIN') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteBin->bin, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Sub Location') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteBin->sub_location, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Ward No.') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteBin->ward_no, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Road No.') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteBin->road_no, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Road Name') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteBin->road_name, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Latitude') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteBin->latitude, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Longitude') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $wasteBin->longitude, ['class' => 'form-control']) !!}</div>
            </div>
        </div>
    </div>
</div>
@stop
