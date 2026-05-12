@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    {!! Form::model($attendanceLog, ['method' => 'PATCH', 'route' => ['swm.attendance-logs.update', $attendanceLog->id], 'class' => 'form-horizontal']) !!}
        @include('swm.service-management.attendance-logs.partial-form')
    {!! Form::close() !!}
</div>
@stop
