{{-- Last Modified: 2026-04-18 --}}
{{-- Developed By: Streams Tech Ltd. --}}
{{-- Description: Form view for adding a new tax payment record --}}
@extends('layouts.dashboard')
@section('title', $page_title)

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $page_title }}</h3>
    </div>
    <form class="form-horizontal" action="{{ route('tax-payment.storeNew') }}" method="POST">
        @csrf
        <div class="card-body">
            @include('taxpayment-info.form-fields', ['taxPayment' => $taxPayment])
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-info">{{ __('Save') }}</button>
            <a href="{{ route('tax-payment.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
