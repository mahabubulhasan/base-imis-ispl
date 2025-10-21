@extends('layouts.dashboard')
@section('title',$page_title)
@section('content')
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	 @can('Edit Drain on Map')
	<div class="card-header bg-transparent ">
		<a href="{{ url('maps#edit_drain_control') . '-' . $drain->code }}" class="btn btn-info">
			{{ __('Edit Drain on Map')}}
		</a>
	</div>
	 @endcan 
	{!! Form::model($drain, ['method' => 'PATCH', 'action' => ['UtilityInfo\DrainController@update', $drain->code], 'class' => 'form-horizontal']) !!}
		@include('utility-info/drains.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div><!-- /.card -->
@endsection
