@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    {!! Form::model($complaint, ['method' => 'PATCH', 'route' => ['swm.complaints.update', $complaint->id], 'class' => 'form-horizontal', 'id' => 'swm-complaint-form']) !!}
        @include('swm.complaints.partial-form')
    {!! Form::close() !!}
</div>
@stop
