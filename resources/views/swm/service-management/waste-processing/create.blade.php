@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    {!! Form::open(['route' => 'swm.waste-processing.store', 'class' => 'form-horizontal']) !!}
        @include('swm.service-management.waste-processing.partial-form')
    {!! Form::close() !!}
</div>
@stop
