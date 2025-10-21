<!-- Last Modified Date: 11-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">

	<div class="card-header bg-transparent ">
		<a href="{{ action('UtilityInfo\SewerLineController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
		{{-- <a href="{{ action('UtilityInfo\SewerLineController@create') }}" class="btn btn-info">{{__('')}}Create new Roadline</a> --}}
	</div>

	<div class="form-horizontal">
		<div class="card-body">
			<div class="form-group row">
				{!! Form::label('code',__('Code'),['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$sewerLine->code,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('road_code',__('Road Code'),['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$sewerLine->road_code,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('location',__('Location'),['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$sewerLine->location,['class' => 'form-control']) !!}
				</div>
			</div>

			<div class="form-group row">
				{!! Form::label('length',__('Length (m)'),['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$sewerLine->length,['class' => 'form-control']) !!}
				</div>
			</div>

			<div class="form-group row">
				{!! Form::label('diameter',__('Diameter (mm)'),['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$sewerLine->diameter,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('treatment_plant_id',__('Treatment Plant'),['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$treatmentplant,['class' => 'form-control']) !!}
				</div>
			</div>
		</div><!-- /.card-body -->
	</div>
	<div class="card-footer">

	</div>
</div><!-- /.box -->
@stop