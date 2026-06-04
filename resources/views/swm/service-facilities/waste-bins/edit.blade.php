@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    {!! Form::model($wasteBin, ['method' => 'PATCH', 'route' => ['swm.waste-bins.update', $wasteBin->id], 'class' => 'form-horizontal']) !!}
    @include('swm.service-facilities.waste-bins.partial-form')
    {!! Form::close() !!}
</div>
@stop
