@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($wasteType, ['method' => 'PATCH', 'route' => ['swm.waste-types.update', $wasteType->id], 'class' => 'form-horizontal']) !!}
		@include('swm.service-providers.waste-types.partial-form')
	{!! Form::close() !!}
</div>
@stop
