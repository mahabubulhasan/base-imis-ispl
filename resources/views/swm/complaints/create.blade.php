@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    {!! Form::open(['route' => 'swm.complaints.store', 'class' => 'form-horizontal', 'id' => 'swm-complaint-form']) !!}
        @include('swm.complaints.partial-form')
    {!! Form::close() !!}
</div>
@stop
