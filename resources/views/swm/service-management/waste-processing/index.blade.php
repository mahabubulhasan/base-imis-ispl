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
<div class="card app-mobile-index">
    <div class="card-header">
        @can('Add SW Waste Processing')
        <a href="{{ route('swm.waste-processing.create') }}" class="btn btn-info">{{ __('Add Waste Processing Log') }}</a>
        @endcan
        @can('Export SW Waste Processing to CSV')
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
                    <label for="reporting_month" class="col-md-2 col-form-label">{{ __('Reporting Month') }}</label>
                    <div class="col-md-2"><input type="month" class="form-control" id="reporting_month" /></div>
                    <label for="date_from" class="col-md-2 col-form-label">{{ __('Report Date From') }}</label>
                    <div class="col-md-2"><input type="date" class="form-control" id="date_from" /></div>
                </div>
                <div class="form-group row">
                    <label for="date_to" class="col-md-2 col-form-label">{{ __('Report Date To') }}</label>
                    <div class="col-md-2"><input type="date" class="form-control" id="date_to" /></div>
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
            <table id="data-table" class="table table-bordered table-striped" width="100%">
                <thead>
                    <tr>
                        <th>{{ __('Waste Processing Log ID') }}</th>
                        <th>{{ __('Entry Date and Time') }}</th>
                        <th>{{ __('Report Date') }}</th>
                        <th>{{ __('Reporting Month') }}</th>
                        <th>{{ __('Quantity of Waste Received (Ton)') }}</th>
                        <th>{{ __('Residual Waste Landfilled (Ton)') }}</th>
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
            url: '{!! route("swm.waste-processing.data") !!}',
            data: function(d) {
                d.reporting_month = $('#reporting_month').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'entry_at', name: 'entry_at' },
            { data: 'report_date', name: 'report_date' },
            { data: 'reporting_month_label', name: 'reporting_month' },
            { data: 'waste_received_ton', name: 'waste_received_ton' },
            { data: 'residual_waste_landfilled_ton', name: 'residual_waste_landfilled_ton' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[2, 'desc']]
    }).on('draw', function() {
        $('.delete').on('click', function(e) {
            var form = $(this).closest('form');
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

    if (typeof resetDataTable === 'function') {
        resetDataTable(dataTable);
    }

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        dataTable.draw();
    });

    $('#export').on('click', function(e) {
        e.preventDefault();
        var reporting_month = $('#reporting_month').val() || '';
        var date_from = $('#date_from').val() || '';
        var date_to = $('#date_to').val() || '';
        window.location.href = "{!! route('swm.waste-processing.export') !!}?reporting_month=" + encodeURIComponent(reporting_month) +
            "&date_from=" + encodeURIComponent(date_from) +
            "&date_to=" + encodeURIComponent(date_to);
    });
});
</script>
@endpush
