@extends('layouts.dashboard')
@section('title', __('Create Role'))
@section('content')
<div class="box box-info">
    <div class="box-header with-border">
	</div><!-- /.box-header -->
    <div class="card">
      {{ Form::open([ 'action' => 'Auth\RoleController@store','class' => 'form-horizontal' ]) }}
      <div class="card-body">
        @include('roles.form')
      </div>
      <div class="card-footer">
        <a href="{{ action('Auth\RoleController@index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
        <button class="btn btn-info" type="submit">{{ __('Save')}}</button>
      </div>
      {{ Form::close() }}
    </div>
</div>
@endsection
