@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::open(['route' => 'swm.landfills.store', 'class' => 'form-horizontal']) !!}
		@include('swm.service-facilities.landfills.partial-form')
	{!! Form::close() !!}
</div>
@stop
