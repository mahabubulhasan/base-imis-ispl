@extends('layouts.dashboard')
@push('style')
<style type="text/css">
.dataTables_filter {
    display: none;
}
</style>
@endpush
@section('title', $page_title)
@section('content')
@include('swm.partials.import-errors')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
@if(session('warning'))
<div class="alert alert-warning">{{ session('warning') }}</div>
@endif
<div class="card app-mobile-index">
    <div class="card-header">
        @include('swm.partials.excel-import-export-header', [
            'addPermission' => 'Add SW Bill Collection Payment',
            'addRoute' => route('swm.bill-collection-payments.create'),
            'addLabel' => __('Add Payment'),
            'importRoute' => route('swm.bill-collection-payments.import'),
            'importPermission' => 'Import SW Bill Collection Payments From Excel',
            'templateRoute' => route('swm.bill-collection-payments.template'),
            'exportPermission' => 'Export SW Bill Collection Payments to Excel',
            'exportId' => 'export',
        ])
        <a href="#" class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse"
            data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            {{ __('Show Filter') }}
        </a>
    </div>
    <div class="card-body">
        <div id="collapseOne" class="collapse">
            <form class="form-horizontal" id="filter-form">
                <div class="form-group row">
                    <label for="holding_number" class="col-md-2 col-form-label">{{ __('Holding No.') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="holding_number" /></div>
                    <label for="household_id" class="col-md-2 col-form-label">{{ __('Household ID') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="household_id" /></div>
                    <label for="payment_for_month" class="col-md-2 col-form-label">{{ __('Payment For Month') }}</label>
                    <div class="col-md-2"><input type="month" class="form-control" id="payment_for_month" /></div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-info">{{ __('Filter') }}</button>
                    <button type="reset" id="reset-filter" class="btn btn-info">{{ __('Reset') }}</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="data-table" class="table table-bordered table-striped tbl-aligned" width="100%">
                <thead>
                    <tr>
                        <th>{{ __('Household ID') }}</th>
                        <th>{{ __('Household Owner Name') }}</th>
                        <th>{{ __("Father's/Husband's Name") }}</th>
                        <th>{{ __('Contact No.') }}</th>
                        <th>{{ __('Holding No.') }}</th>
                        <th>{{ __('Ward No.') }}</th>
                        <th>{{ __('Transaction Month') }}</th>
                        <th>{{ __('Current Month Payment (Taka)') }}</th>
                        <th>{{ __('Previous Due Payment (Taka)') }}</th>
                        <th>{{ __('Total Payment (Taka)') }}</th>
                        <th>{{ __('Payment Method') }}</th>
                        <th>{{ __('Payment Received by') }}</th>
                        <th>{{ __('Receipt No.') }}</th>
                        <th>{{ __('Payment Time') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@stop

@push('scripts')
<script>
$(function() {
    var dataTable = $('#data-table').DataTable({
        bFilter: false,
        processing: true,
        serverSide: true,
        scrollCollapse: true,
        ajax: {
            url: '{!! route("swm.bill-collection-payments.data") !!}',
            data: function(d) {
                d.holding_number = $('#holding_number').val();
                d.household_id = $('#household_id').val();
                d.payment_for_month = $('#payment_for_month').val();
            }
        },
        columns: [
            { data: 'household_id', name: 'household_id' },
            { data: 'household_owner_name', name: 'household_owner_name' },
            { data: 'father_or_husband_name', name: 'father_or_husband_name' },
            { data: 'contact_number', name: 'contact_number' },
            { data: 'holding_number', name: 'swm.bill_collection_payments.holding_number' },
            { data: 'ward', name: 'ward', className: 'col-num' },
            { data: 'payment_for_month', name: 'swm.bill_collection_payments.payment_for_month' },
            { data: 'amount', name: 'swm.bill_collection_payments.amount', className: 'col-currency' },
            { data: 'due_paid', name: 'swm.bill_collection_payments.due_paid', className: 'col-currency' },
            { data: 'total_collected', name: 'total_collected', searchable: false, orderable: false, className: 'col-currency' },
            { data: 'payment_method', name: 'swm.bill_collection_payments.payment_method' },
            { data: 'received_by_name', name: 'received_by_name' },
            { data: 'receipt_no', name: 'swm.bill_collection_payments.receipt_no' },
            { data: 'payment_time', name: 'swm.bill_collection_payments.payment_time' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    }).on('draw', function() {
        $('.delete').on('click', function(e) {
            var form = $(this).closest("form");
            e.preventDefault();
            Swal.fire({
                title: '{{ __('Are you sure?') }}',
                text: "{!! __('You won\'t be able to revert this!') !!}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{ __('Yes, delete it!') }}',
                cancelButtonText: '{{ __('Cancel') }}',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    resetDataTable(dataTable);

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        dataTable.draw();
    });

    $('#export').on('click', function(e) {
        e.preventDefault();
        var searchData = $('input[type=search]').val();
        var holding_number = $('#holding_number').val() || '';
        var household_id = $('#household_id').val() || '';
        var payment_for_month = $('#payment_for_month').val() || '';
        window.location.href = "{!! route('swm.bill-collection-payments.export') !!}?searchData=" + encodeURIComponent(searchData || '') +
            "&holding_number=" + encodeURIComponent(holding_number) +
            "&household_id=" + encodeURIComponent(household_id) +
            "&payment_for_month=" + encodeURIComponent(payment_for_month);
    });
});
</script>
@endpush
