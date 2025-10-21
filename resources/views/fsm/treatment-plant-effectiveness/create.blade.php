<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	<div class="card-header bg-white">



	</div><!-- /.card-header -->
	{!! Form::open(['action'=> 'Fsm\TreatmentPlantEffectivenessController@store', 'method' => 'POST', 'class' => 'form-horizontal']) !!}
		@include('fsm.treatment-plant-effectiveness.partial-form', ['submitButtomText' => __('Save')])
	{!! Form::close() !!}
</div><!-- /.card -->
@stop
