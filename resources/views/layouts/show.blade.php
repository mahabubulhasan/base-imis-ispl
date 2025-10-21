<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
{{--
A Layout for showing resource details
--}}
{{--Extend the main layout--}}
@extends('layouts.dashboard')
{{--Add sections for the main layout--}}
@section('title', $page_title)
{{--Add sections for the index layout--}}

{{--Include the layout inside the main content section--}}
@section('content')
    <div class="card card-info">
        @if(!Request::is('*/create') && !Request::is('*/edit') && !Request::is('*/create/*'))
<div class="card-header bg-transparent">
        <a href="{{ $indexAction }}" class="btn btn-info">{{ __('Back to List') }}</a>
</div>
@endif
        <div class="card-body">
            {!! Form::open(['class' => 'form-horizontal']) !!}
            @include('layouts.partial-form',$formFields)
            {!! Form::close() !!}
        </div>
    </div>
@endsection
