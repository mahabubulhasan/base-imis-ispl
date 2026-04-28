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
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
@if(session('import_errors'))
<div class="alert alert-warning">
    <ul class="mb-0">
        @foreach(session('import_errors') as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif
@if(session('warning'))
<div class="alert alert-warning">{{ session('warning') }}</div>
@endif
<div class="card">
    <div class="card-header">
        @can('Add Payment')
        <a href="{{ route('swm.bill-collection-payments.create') }}" class="btn btn-info">{{ __('Add Payment') }}</a>
        @endcan
        @can('Import Payments From CSV')
        <a href="{{ route('swm.bill-collection-payments.import') }}" class="btn btn-info">{{ __('Import from CSV') }}</a>
        @endcan
        @can('Export Payments to CSV')
        <a href="/templates/bill-collection-payments-import-template.csv" download="bill-collection-payments-import-template.csv" class="btn btn-info">{{ __('Download CSV Template') }}</a>
        @endcan
        @can('Export Payments to CSV')
        <a href="#" id="export" class="btn btn-info">{{ __('Export to CSV') }}</a>
        @endcan
        <a href="#" class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse"
            data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            {{ __('Show Filter') }}
        </a>
    </div>
    <div class="card-body">
        <div id="collapseOne" class="collapse">
            <form class="form-horizontal" id="filter-form">
                <div class="form-group row">
                    <label for="holding_number" class="col-md-2 col-form-label">{{ __('Holding Number') }}</label>
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
        <div style="overflow: auto; width: 100%;">
            <table id="data-table" class="table table-bordered table-striped" width="100%">
                <thead>
                    <tr>
                        <th>{{ __('Holding Number') }}</th>
                        <th>{{ __('Household ID') }}</th>
                        <th>{{ __('Household Owner Name') }}</th>
                        <th>{{ __('Amount') }} ({{ __('Taka') }})</th>
                        <th>{{ __('Payment For Month') }}</th>
                        <th>{{ __('Payment Time') }}</th>
                        <th>{{ __('Payment Method') }}</th>
                        <th>{{ __('Received By') }}</th>
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
            { data: 'holding_number', name: 'swm.bill_collection_payments.holding_number' },
            { data: 'household_id', name: 'household_id' },
            { data: 'household_owner_name', name: 'household_owner_name' },
            { data: 'amount', name: 'swm.bill_collection_payments.amount' },
            { data: 'payment_for_month', name: 'swm.bill_collection_payments.payment_for_month' },
            { data: 'payment_time', name: 'swm.bill_collection_payments.payment_time' },
            { data: 'payment_method', name: 'swm.bill_collection_payments.payment_method' },
            { data: 'received_by_name', name: 'received_by_name' },
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
