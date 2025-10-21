<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ action('Fsm\TreatmentPlantTestController@index') }}" class="btn btn-info">{{ __('Back to List') }}</a>

    </div><!-- /.card-header -->
    <div class="form-horizontal">
        <div class="card-body">

            <div class="form-group row">
                {!! Form::label(__('Treatment Plant Name'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlantName[0],['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label(__('Sample Date'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlantTest->date,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label(__('Temperature'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlantTest->temperature,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label(__('pH'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlantTest->ph,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row">
                {!! Form::label(__('COD'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlantTest->cod,['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label(__('BOD'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlantTest->bod,['class' => 'form-control']) !!}
                </div>
            </div>


            <div class="form-group row">
                {!! Form::label(__('TSS'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlantTest->tss,['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label(__('Ecoli'),null,['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlantTest->ecoli,['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="form-group row ">
        {!! Form::label('remarks', __('Remark'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
                    {!! Form::label(null,$treatmentPlantTest->remarks,['class' => 'form-control']) !!}
                </div>
        </div>
        </div><!-- /.box-body -->
    </div>
</div><!-- /.box -->
@stop

