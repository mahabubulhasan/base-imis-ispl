@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    {!! Form::model($primaryCollectionSite, ['method' => 'PATCH', 'route' => ['swm.primary-collection-sites.update', $primaryCollectionSite->id], 'class' => 'form-horizontal']) !!}
        @include('swm.service-coverage.primary-collection-sites.partial-form')
    {!! Form::close() !!}
</div>
@stop
