@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    {!! Form::model($stsLog, ['method' => 'PATCH', 'route' => ['swm.sts-logs.update', $stsLog->id], 'class' => 'form-horizontal']) !!}
        @include('swm.service-management.sts-logs.partial-form')
    {!! Form::close() !!}
</div>
@stop
