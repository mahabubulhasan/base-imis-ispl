@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">

	<div class="card-header bg-transparent ">
		<a href="{{ action('UtilityInfo\RoadlineController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
		{{-- <a href="{{ action('UtilityInfo\RoadlineController@create') }}" class="btn btn-info">Create new Roadline</a> --}}
	</div>

	<div class="form-horizontal">
		<div class="card-body">
			<div class="form-group row">
				{!! Form::label('code',__('Code'),['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$roadline->code,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('name',__('Road Name'),['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$roadline->name,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('hierarchy',__('Hierarchy'),['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$roadline->hierarchy,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('right_of_way',__('Right of Way (m)'),['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$roadline->right_of_way,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('carrying_width',__('Carrying Width (m)'),['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$roadline->carrying_width,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('surface_type',__('Surface Type'),['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$roadline->surface_type,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('length',__('Road Length (m)'),['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$roadline->length,['class' => 'form-control']) !!}
				</div>
			</div>


		</div><!-- /.box-body -->
	</div>
	<div class="card-footer">

	</div>
</div><!-- /.box -->
@stop