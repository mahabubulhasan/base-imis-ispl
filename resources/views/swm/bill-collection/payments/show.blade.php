@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.bill-collection-payments.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
        @can('Edit SW Bill Collection Payment')
        <a href="{{ route('swm.bill-collection-payments.edit', $payment->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
        @endcan
    </div>
    @php
        $hhSite = $payment->primaryCollectionSite;
        $hhStatusLabel = $hhSite && $hhSite->status
            ? (\App\Models\BuildingInfo\Household::statusOptions()[$hhSite->status] ?? $hhSite->status)
            : '—';
        $siteCharge = optional($payment->primaryCollectionSite)->waste_charge;
        $chargeLabel = $siteCharge !== null && $siteCharge !== '' ? currency($siteCharge) : '—';
        $householdLabel = $payment->customer_id;
        if (optional($payment->primaryCollectionSite)->household_owner_name) {
            $householdLabel .= ' — ' . optional($payment->primaryCollectionSite)->household_owner_name;
        }
        $receiptLabel = ! empty($payment->receipt_copy_url)
            ? __('View receipt')
            : '—';
    @endphp
    <div class="form-horizontal bcp-payment-form-mobile app-mobile-form">
        <div class="card-body">
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Holding') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $payment->holding_number, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Household') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $householdLabel, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Payment for the month of') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $payment->payment_for_month?->format('M, Y'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Charge') }} ({{ __('Taka') }} / {{ __('Month') }})</label>
                <div class="col-sm-3">{!! Form::label(null, $chargeLabel, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Status') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $hhStatusLabel, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Payment time') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $payment->payment_time?->format('Y-m-d H:i'), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Current month paid') }} ({{ __('Taka') }})</label>
                <div class="col-sm-3">{!! Form::label(null, currency($payment->amount), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Previous due paid') }} ({{ __('Taka') }})</label>
                <div class="col-sm-3">{!! Form::label(null, currency($payment->due_paid ?? 0), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Total collected') }} ({{ __('Taka') }})</label>
                <div class="col-sm-3">{!! Form::label(null, currency((float) $payment->amount + (float) ($payment->due_paid ?? 0)), ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Payment method') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $payment->payment_method ? (config('bill_collection.payment_methods')[$payment->payment_method] ?? $payment->payment_method) : '', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Receipt no') }}</label>
                <div class="col-sm-3">{!! Form::label(null, $payment->receipt_no ?: '—', ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Payment received by') }}</label>
                <div class="col-sm-3">{!! Form::label(null, optional($payment->receivedBy)->name, ['class' => 'form-control']) !!}</div>
            </div>
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Payment receipt copy') }}</label>
                <div class="col-sm-3">
                    @if(!empty($payment->receipt_copy_url))
                        <a href="{{ $payment->receipt_copy_url }}" target="_blank" rel="noopener" class="form-control d-block">{{ $receiptLabel }}</a>
                    @else
                        {!! Form::label(null, '—', ['class' => 'form-control']) !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop
