@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
@if(session('warning'))
<div class="alert alert-warning">{{ session('warning') }}</div>
@endif
<div class="card card-info">
    {!! Form::open(['route' => 'swm.bill-collection-payments.store', 'class' => 'form-horizontal', 'id' => 'bill-collection-payment-form', 'files' => true]) !!}
        @include('swm.bill-collection.payments.partial-form')
    {!! Form::close() !!}
</div>
@stop
