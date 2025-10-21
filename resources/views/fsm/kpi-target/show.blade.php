@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
<div class="card-header bg-transparent ">
<a href="{{ action('Fsm\KpiTargetController@index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    </div><!-- /.box-header -->
    <div class="form-horizontal">
        <div class="card-body">
            <div class="form-group row">
                {!! Form::label('indicator_id',__('Indicator'),['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label('indicator_id',$indicators, ['class' => 'form-control']) !!}
                </div>
            </div>
           
            <div class="form-group row">
                {!! Form::label(null,__('Year'),['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$kpi->year,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label(null,__('Target (%)'),['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$kpi->target,['class' => 'form-control']) !!}
                </div>
            </div>
         
        </div><!-- /.card-body -->
    </div>

</div><!-- /.card -->


@stop

