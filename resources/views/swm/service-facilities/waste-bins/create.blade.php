@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class='card card-info'>{!! Form::open(['route'=>'swm.waste-bins.store','class'=>'form-horizontal']) !!}@include('swm.service-facilities.waste-bins.partial-form'){!! Form::close() !!}</div>
@stop
