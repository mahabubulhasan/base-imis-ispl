@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($organization, ['method' => 'PATCH', 'route' => ['swm.organizations.update', $organization->id], 'class' => 'form-horizontal']) !!}
		@include('swm.service-providers.organizations.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div>
@stop
