@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::open(['url' => 'fsm/help-desks', 'class' => 'form-horizontal']) !!}
		@include('fsm/help-desks.partial-form', ['submitButtomText' => __('Save')])
	{!! Form::close() !!}
</div><!-- /.box -->
@stop