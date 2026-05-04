@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::open(['route' => 'swm.waste-bin-types.store', 'class' => 'form-horizontal']) !!}
		@include('swm.service-providers.waste-bin-types.partial-form')
	{!! Form::close() !!}
</div>
@stop
