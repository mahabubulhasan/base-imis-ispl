@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($organizationType, ['method' => 'PATCH', 'route' => ['swm.organization-types.update', $organizationType->id], 'class' => 'form-horizontal']) !!}
		@include('swm.service-providers.organization-types.partial-form')
	{!! Form::close() !!}
</div>
@stop
