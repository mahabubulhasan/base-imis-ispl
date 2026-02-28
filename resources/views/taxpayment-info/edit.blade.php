@extends('layouts.dashboard')
@section('title', $page_title)

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $page_title }}</h3>
    </div>
    <form class="form-horizontal" action="{{ route('tax-payment.update', $taxPayment->tax_code) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('taxpayment-info.form-fields', ['taxPayment' => $taxPayment])
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-info">{{ __('Save Changes') }}</button>
            <a href="{{ route('tax-payment.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
