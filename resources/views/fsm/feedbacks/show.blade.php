@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')

{{-- Last Modified: 2026-04-26
// Developed By: Streams Tech Ltd.
// Description: Admin feedback details view with readonly fields --}}

<div class="card card-info">
    <div class="card-footer">
        <a href="{{ action('Fsm\FeedbackController@index') }}" class="btn btn-info" >{{ __('Back to List') }}</a>
    </div>
    <div class="form-horizontal">

        <div class="card-body">
            <!-- Application Information Section -->
            <h4 class="mb-3 font-weight-bold">Application Information</h4>

            <div class="form-group row">
                {!! Form::label('application_id',__('Application ID'),['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$feedback->application_id ?? '-',['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('customer_name',__('Service Receiver Name'),['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$feedback->customer_name ?? '-',['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('customer_number',__('Service Receiver Contact'),['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    @php
                        $displayNumber = $feedback->customer_number ?? '-';
                        if ($displayNumber !== '-' && strlen($displayNumber) === 10 && is_numeric($displayNumber)) {
                            $displayNumber = '0' . $displayNumber;
                        }
                    @endphp
                    {!! Form::label(null, $displayNumber, ['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('service_provider_name',__('Service Provider Name'),['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::label(null,$feedback->application?->service_provider?->company_name ?? '-',['class' => 'form-control']) !!}
                </div>
            </div>

            <div class="form-group row">
                {!! Form::label('service_provider_contact',__('Service Provider Contact'),['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    @php
                        $displayProviderContact = $feedback->application?->service_provider?->contact_number ?? '-';
                        if ($displayProviderContact !== '-' && strlen($displayProviderContact) === 10 && is_numeric($displayProviderContact)) {
                            $displayProviderContact = '0' . $displayProviderContact;
                        }
                    @endphp
                    {!! Form::label(null, $displayProviderContact, ['class' => 'form-control']) !!}
                </div>
            </div>

            <hr>

            <!-- Service Feedback Section -->
            <h4 class="mb-3 font-weight-bold">Service Feedback</h4>

            <div class="form-group row">
                <label class="col-sm-3 control-label">1. Did the emptier wear safety equipment?</label>
                <div class="col-sm-6">
                    <label class="radio-inline">
                        <input type="radio" disabled @if($feedback->safety_measures === 'Yes') checked @endif> Yes
                    </label>
                    <label class="radio-inline">
                        <input type="radio" disabled @if($feedback->safety_measures === 'No') checked @endif> No
                    </label>
                    <label class="radio-inline">
                        <input type="radio" disabled @if($feedback->safety_measures === 'Unknown') checked @endif> Unknown
                    </label>
                    @if(!$feedback->safety_measures)
                        <div class="text-muted small mt-2">Not provided</div>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 control-label">2. How would you rate the attitude of the emptiers during service?</label>
                <div class="col-sm-6">
                    <label class="radio-inline">
                        <input type="radio" disabled @if($feedback->fsm_quality_level === 3) checked @endif> Satisfied
                    </label>
                    <label class="radio-inline">
                        <input type="radio" disabled @if($feedback->fsm_quality_level === 2) checked @endif> Neutral
                    </label>
                    <label class="radio-inline">
                        <input type="radio" disabled @if($feedback->fsm_quality_level === 1) checked @endif> Dissatisfied
                    </label>
                    @if(!$feedback->fsm_quality_level)
                        <div class="text-muted small mt-2">Not provided</div>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 control-label">3. How do you assess the response time of the emptying service?</label>
                <div class="col-sm-6">
                    <label class="radio-inline">
                        <input type="radio" disabled @if($feedback->service_delivery_efficiency === 3) checked @endif> Satisfied
                    </label>
                    <label class="radio-inline">
                        <input type="radio" disabled @if($feedback->service_delivery_efficiency === 2) checked @endif> Neutral
                    </label>
                    <label class="radio-inline">
                        <input type="radio" disabled @if($feedback->service_delivery_efficiency === 1) checked @endif> Dissatisfied
                    </label>
                    @if(!$feedback->service_delivery_efficiency)
                        <div class="text-muted small mt-2">Not provided</div>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 control-label">4. How satisfied are you with the overall emptying service?</label>
                <div class="col-sm-6">
                    <label class="radio-inline">
                        <input type="radio" disabled @if($feedback->fsm_service_quality) checked @endif> Satisfied
                    </label>
                    <label class="radio-inline">
                        <input type="radio" disabled @if(!$feedback->fsm_service_quality && !is_null($feedback->fsm_service_quality)) checked @endif> Dissatisfied
                    </label>
                    @if(is_null($feedback->fsm_service_quality))
                        <div class="text-muted small mt-2">Not provided</div>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 control-label">5. How satisfied are you with the price of this service?</label>
                <div class="col-sm-6">
                    <label class="radio-inline">
                        <input type="radio" disabled @if($feedback->service_quality_price === 3) checked @endif> Satisfied
                    </label>
                    <label class="radio-inline">
                        <input type="radio" disabled @if($feedback->service_quality_price === 2) checked @endif> Neutral
                    </label>
                    <label class="radio-inline">
                        <input type="radio" disabled @if($feedback->service_quality_price === 1) checked @endif> Dissatisfied
                    </label>
                    @if(!$feedback->service_quality_price)
                        <div class="text-muted small mt-2">Not provided</div>
                    @endif
                </div>
            </div>

            @if($feedback->comments)
                <div class="form-group row">
                    {!! Form::label('comments',__('Additional Comments'),['class' => 'col-sm-3 control-label']) !!}
                    <div class="col-sm-6">
                        {!! Form::label(null,$feedback->comments,['class' => 'form-control']) !!}
                    </div>
                </div>
            @endif

        </div><!-- /.card-body -->

    </div>
</div><!-- /.card -->
@stop
