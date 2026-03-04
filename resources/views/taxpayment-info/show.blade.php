@extends('layouts.dashboard')
@section('title', $page_title)

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $page_title }}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">{{ __('Tax Code') }}</th>
                        <td>{{ $taxPayment->tax_code }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Owner Name') }}</th>
                        <td>{{ $taxPayment->owner_name }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Owner Contact') }}</th>
                        <td>{{ $taxPayment->owner_contact }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Ward') }}</th>
                        <td>{{ $taxPayment->ward }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Due Year') }}</th>
                        <td>{{ $taxPayment->due_year === 99 ? 'No Data' : $taxPayment->due_year }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Last Payment Date') }}</th>
                        <td>{{ $taxPayment->last_payment_date ?? '---' }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Created At') }}</th>
                        <td>{{ $taxPayment->created_at }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('Updated At') }}</th>
                        <td>{{ $taxPayment->updated_at }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <a href="{{ route('tax-payment.edit', $taxPayment->tax_code) }}" class="btn btn-info">{{ __('Edit') }}</a>
        <a href="{{ route('tax-payment.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
    </div>
</div>
@endsection
