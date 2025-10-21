@extends('layouts.dashboard')
@section('title', __('Edit Role'))
@section('content')
<div class="box box-info">
<div class="box-header with-border">
		
	</div><!-- /.box-header -->
    <div class="card">
{{--  <div class="text-right">
   {{ Form::open([ 'action' => [ 'Auth\RoleController@searchPermission', $role->id ], 'method' => 'GET', 'class' => 'form-horizontal' ]) }}
      <div class="form-group">
        <label for="search"> Search: </label>
        <input type="text" name="search" ></input>
        </div>
    </div>    
    <div class="text-right">
    <button type="submit" class="btn btn-info"> Submit</button>
    <a href="{{ url('roles/' . $role->id . '/edit') }}" class="btn btn-info">Reset</a>
    </div>
   {{ Form::close() }}--}}
      
  
  
  
    {{ Form::model($role, [ 'action' => [ 'Auth\RoleController@update', $role->id ], 'method' => 'PUT', 'class' => 'form-horizontal' ]) }}
      <div class="card-body">
        @include('roles.form')
      </div>
      <div class="card-footer">
        <a href="{{ action('Auth\RoleController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
        <button class="btn btn-info" type="submit">{{__('Update')}}</button>
      </div>
     {{ Form::close() }}
    </div>




@endsection