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
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">{{ __('Holding Number') }}</dt>
            <dd class="col-sm-9">{{ $payment->holding_number }}</dd>
            <dt class="col-sm-3">{{ __('Household ID') }}</dt>
            <dd class="col-sm-9">{{ $payment->customer_id }}</dd>
            <dt class="col-sm-3">{{ __('Household Owner Name') }}</dt>
            <dd class="col-sm-9">{{ optional($payment->primaryCollectionSite)->household_owner_name }}</dd>
            <dt class="col-sm-3">{{ __('Amount') }} ({{ __('Taka') }})</dt>
            <dd class="col-sm-9">{{ number_format((float) $payment->amount, 2) }}</dd>
            <dt class="col-sm-3">{{ __('Payment For Month') }}</dt>
            <dd class="col-sm-9">{{ $payment->payment_for_month?->format('M, Y') }}</dd>
            <dt class="col-sm-3">{{ __('Payment Time') }}</dt>
            <dd class="col-sm-9">{{ $payment->payment_time?->format('Y-m-d H:i:s') }}</dd>
            <dt class="col-sm-3">{{ __('Payment Method') }}</dt>
            <dd class="col-sm-9">{{ config('bill_collection.payment_methods')[$payment->payment_method] ?? $payment->payment_method }}</dd>
            <dt class="col-sm-3">{{ __('Received By') }}</dt>
            <dd class="col-sm-9">{{ optional($payment->receivedBy)->name }}</dd>
            <dt class="col-sm-3">{{ __('Payment receipt copy') }}</dt>
            <dd class="col-sm-9">
                @if(!empty($payment->receipt_copy_url))
                    <a href="{{ $payment->receipt_copy_url }}" target="_blank" rel="noopener">{{ __('View receipt') }}</a>
                @else
                    —
                @endif
            </dd>
        </dl>
    </div>
</div>
@stop
