@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::open(['route' => 'swm.organization-types.store', 'class' => 'form-horizontal']) !!}
		@include('swm.service-providers.organization-types.partial-form', ['organizationType' => null])
	{!! Form::close() !!}
</div>
@stop
