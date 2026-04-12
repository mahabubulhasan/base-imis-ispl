@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($workType, ['method' => 'PATCH', 'route' => ['swm.work-types.update', $workType->id], 'class' => 'form-horizontal']) !!}
		@include('swm.service-providers.work-types.partial-form')
	{!! Form::close() !!}
</div>
@stop
