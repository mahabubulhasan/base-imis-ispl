@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
	<div class="card-header bg-transparent">
		<a href="{{ route('swm.workers.index') }}" class="btn btn-info">{{__('Back to List')}}</a>
	</div>
	<div class="form-horizontal">
		<div class="card-body">
		<div class="form-group row">
    {!! Form::label('organization', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, optional($worker->organization)->name, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('work_type', __('Work Type'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, optional($worker->workType)->name, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
    {!! Form::label('name', __('Worker Name'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::label(null, $worker->name, ['class' => 'form-control']) !!}
    </div>
		</div>
		<div class="form-group row">
			{!! Form::label('mobile', __('Mobile'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->mobile, ['class' => 'form-control']) !!}
			</div>
		</div>
		<div class="form-group row">
			{!! Form::label('email', __('Email'), ['class' => 'col-sm-3 control-label']) !!}
			<div class="col-sm-3">
				{!! Form::label(null, $worker->email, ['class' => 'form-control']) !!}
			</div>
		</div>
		</div>
	</div>
</div>
@stop
