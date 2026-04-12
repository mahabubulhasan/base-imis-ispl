@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($vehicleType, ['method' => 'PATCH', 'route' => ['swm.vehicle-types.update', $vehicleType->id], 'class' => 'form-horizontal']) !!}
		@include('swm.service-providers.vehicle-types.partial-form')
	{!! Form::close() !!}
</div>
@stop
