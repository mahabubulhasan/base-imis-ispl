@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    {!! Form::model($landfillLog, ['method' => 'PATCH', 'route' => ['swm.landfill-logs.update', $landfillLog->id], 'class' => 'form-horizontal']) !!}
        @include('swm.service-management.landfill-logs.partial-form')
    {!! Form::close() !!}
</div>
@stop
