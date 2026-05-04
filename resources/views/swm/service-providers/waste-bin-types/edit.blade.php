@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($wasteBinType, ['method' => 'PATCH', 'route' => ['swm.waste-bin-types.update', $wasteBinType->id], 'class' => 'form-horizontal']) !!}
		@include('swm.service-providers.waste-bin-types.partial-form')
	{!! Form::close() !!}
</div>
@stop
