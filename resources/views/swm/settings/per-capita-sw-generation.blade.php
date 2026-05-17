@extends('layouts.dashboard')

@section('title', $page_title)

@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')

<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">{{ __('Per Capita SW Generation') }}</h3>
    </div>
    <div class="card-body">
        @can('Edit SW Per Capita Generation Setting')
        {!! Form::model($setting, ['route' => 'swm.settings.per-capita-sw-generation.update', 'method' => 'PUT', 'class' => 'form-horizontal']) !!}
        @endcan
            <div class="form-group row">
                <label class="col-sm-4 control-label" for="per_capita_sw_generation_kg_per_day">{{ __('Per Capita Waste Generation (Kg/day)') }}</label>
                <div class="col-sm-4">
                    <input
                        type="number"
                        name="per_capita_sw_generation_kg_per_day"
                        id="per_capita_sw_generation_kg_per_day"
                        class="form-control"
                        step="0.01"
                        min="0"
                        value="{{ old('per_capita_sw_generation_kg_per_day', $setting->per_capita_sw_generation_kg_per_day) }}"
                        @cannot('Edit SW Per Capita Generation Setting') readonly @endcannot
                        @can('Edit SW Per Capita Generation Setting') required @endcan
                    >
                </div>
            </div>
        @can('Edit SW Per Capita Generation Setting')
            <div class="form-group row">
                <div class="col-sm-offset-4 col-sm-4">
                    <button type="submit" class="btn btn-info">{{ __('Save') }}</button>
                </div>
            </div>
        {!! Form::close() !!}
        @endcan
    </div>
</div>
@endsection
