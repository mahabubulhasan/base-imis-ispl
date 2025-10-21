@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
	<div class="card-header bg-white">

	<div class="form-inline">
		<a href="{{ action('Cwis\CwisMneController@index') }}" class="btn btn-info float-left">{{__('Back to List')}}</a>
		<div class="form-group float-right text-right ml-auto">
			<label for="year_new">{{__('Year')}}</label>
			<select class="form-control" id="year_new" name="year_new">
				
					<option value= "{{$year}}"> {{$year}} </option>
				
			</select>
		</div>
	</div>

	</div><!-- /.card-header -->
	@include('errors.list')
	{!! Form::open(['action'=> 'Cwis\CwisMneController@store', 'method' => 'POST', 'class' => 'form-horizontal']) !!}
		@include('cwis/cwis-df-mne.partial-form', ['submitButtomText' => __('Save')])
	{!! Form::close() !!}
</div><!-- /.card -->
@stop
