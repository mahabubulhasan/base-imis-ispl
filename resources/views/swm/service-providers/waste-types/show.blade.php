@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
	<div class="card-header bg-transparent">
		<a href="{{ route('swm.waste-types.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
		@can('Edit SW Waste Type')
		<a href="{{ route('swm.waste-types.edit', $wasteType->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
		@endcan
	</div>
	<div class="form-horizontal swm-waste-type-form-mobile app-mobile-form">
		<div class="card-body">
			<div class="form-group row">
				<label class="col-sm-3 control-label">{{ __('Waste Type') }}</label>
				<div class="col-sm-3">{!! Form::label(null, $wasteType->name, ['class' => 'form-control']) !!}</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-3 control-label">{{ __('Description') }}</label>
				<div class="col-sm-3">{!! Form::label(null, $wasteType->description ?? '', ['class' => 'form-control']) !!}</div>
			</div>
		</div>
	</div>
</div>
@stop
