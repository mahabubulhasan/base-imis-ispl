@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($worker, ['method' => 'PATCH', 'route' => ['swm.workers.update', $worker->id], 'class' => 'form-horizontal']) !!}
		@include('swm.service-providers.workers.partial-form')
	{!! Form::close() !!}
</div>
@stop
