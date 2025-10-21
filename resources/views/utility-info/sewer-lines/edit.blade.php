@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	 @can('Edit Sewer on Map')
<div class="card-header bg-transparent ">
<a href="{{ url('maps#edit_sewer_control') . '-' . $sewerLine->code }}" class="btn btn-info">
     {{ __('Edit Sewer on Map') }}
</a>
	</div>
@endcan
	{!! Form::model($sewerLine, ['method' => 'PATCH', 'action' => ['UtilityInfo\SewerLineController@update', $sewerLine->code], 'class' => 'form-horizontal']) !!}
		@include('utility-info/sewer-lines.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div><!-- /.card -->
@endsection
