@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
	<div class="card-header bg-transparent">
		<a href="{{ route('swm.landfill-types.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
		@can('Edit SW Landfill Type')
		<a href="{{ route('swm.landfill-types.edit', $landfillType->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
		@endcan
	</div>
	<div class="form-horizontal swm-landfill-type-form-mobile app-mobile-form">
		<div class="card-body">
			<div class="form-group row">
				<label class="col-sm-3 control-label">{{ __('Landfill Type') }}</label>
				<div class="col-sm-3">{!! Form::label(null, $landfillType->name, ['class' => 'form-control']) !!}</div>
			</div>
			<div class="form-group row">
				<label class="col-sm-3 control-label">{{ __('Description') }}</label>
				<div class="col-sm-3">{!! Form::label(null, $landfillType->description ?? '', ['class' => 'form-control']) !!}</div>
			</div>
		</div>
	</div>
</div>
@stop
