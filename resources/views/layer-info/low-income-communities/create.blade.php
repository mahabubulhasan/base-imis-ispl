@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::open(['url' => 'layer-info/low-income-communities', 'class' => 'form-horizontal']) !!}
		@include('layer-info/low-income-communities.partial-form', ['submitButtomText' => __('Save')])
	{!! Form::close() !!}
</><!-- /.card -->
@stop