@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    {!! Form::model($household, ['method' => 'PATCH', 'route' => ['building-info.households.update', $household->id], 'class' => 'form-horizontal']) !!}
        @include('swm.service-coverage.households.partial-form')
    {!! Form::close() !!}
</div>
@stop
