@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($sts, ['method' => 'PATCH', 'route' => ['swm.sts.update', $sts->id], 'class' => 'form-horizontal']) !!}
		@include('swm.service-facilities.sts.partial-form')
	{!! Form::close() !!}
</div>
@stop
