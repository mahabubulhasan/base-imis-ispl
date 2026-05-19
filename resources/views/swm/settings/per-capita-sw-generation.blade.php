@extends('layouts.dashboard')

@section('title', $page_title)

@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')

<div class="card card-info">
    @can('Edit SW Per Capita Generation Setting')
    {!! Form::model($setting, ['route' => 'swm.settings.per-capita-sw-generation.update', 'method' => 'PUT', 'class' => 'form-horizontal']) !!}
    @else
    {!! Form::model($setting, ['class' => 'form-horizontal']) !!}
    @endcan
        @include('swm.settings.per-capita-sw-generation.partial-form')
    {!! Form::close() !!}
</div>
@endsection
