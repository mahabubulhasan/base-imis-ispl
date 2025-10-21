@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
	<div class="card-header bg-transparent">
		<a href="{{ action('Fsm\ContainmentController@index') }}" class="btn btn-info">{{ __("Back to List") }}</a>
	</div><!-- /.box-header -->
	<div class="card-body">
		<ul>

			@foreach($containment->revisionHistory as $history)
			@if($history->key == 'created_at' && !$history->old_value)
                            @if($history->userResponsible())
                            <li>{{ $history->userResponsible()->name }} {{__("created this resource at") }} {{ $history->newValue() }}</li>
                            @endif
                        @else
                            @if($history->userResponsible())
                            <li>{{ $history->userResponsible()->name }} {{ __("changed") }} {{ $history->fieldName() }} {{ __("from") }} {{ $history->oldValue() }} {{ __("to") }} {{ $history->newValue() }} {{ __("on") }} {{ $history->created_at }}</li>
                            @endif
                        @endif
			@endforeach

		</ul>
	</div><!-- /.card-body -->
</div><!-- /.card -->
@stop

