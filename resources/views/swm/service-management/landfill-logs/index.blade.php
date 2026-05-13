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
        @can('Add SW Landfill Log')
        <a href="{{ route('swm.landfill-logs.create') }}" class="btn btn-info">{{ __('Add Landfill Log') }}</a>
        @endcan
        @can('Export SW Landfill Logs to CSV')
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
                    <label for="vehicle_search" class="col-md-2 col-form-label">{{ __('Vehicle Number') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="vehicle_search" /></div>
                    <label for="landfill_id" class="col-md-2 col-form-label">{{ __('Landfill Name') }}</label>
                    <div class="col-md-2">
                        <select class="form-control" id="landfill_id">
                            <option value="">{{ __('All') }}</option>
                            @foreach($landfillList as $lid => $lname)
                            <option value="{{ $lid }}">{{ $lname }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="operation_status" class="col-md-2 col-form-label">{{ __('Operation Status') }}</label>
                    <div class="col-md-2">
                        <select class="form-control" id="operation_status">
                            <option value="">{{ __('All') }}</option>
                            @foreach($statusOptions as $k => $label)
                            <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="date_from" class="col-md-2 col-form-label">{{ __('Operation Date From') }}</label>
                    <div class="col-md-2"><input type="date" class="form-control" id="date_from" /></div>
                    <label for="date_to" class="col-md-2 col-form-label">{{ __('Operation Date To') }}</label>
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
                        <th>{{ __('Landfill Log ID') }}</th>
                        <th>{{ __('Entry Date and Time') }}</th>
                        <th>{{ __('Operation Date') }}</th>
                        <th>{{ __('Vehicle Number') }}</th>
                        <th>{{ __('Landfill Name') }}</th>
                        <th>{{ __('Waste Type') }}</th>
                        <th>{{ __('Quantity (Ton)') }}</th>
                        <th>{{ __('Weighbridge Weight (Ton)') }}</th>
                        <th>{{ __('Effective Quantity (Ton)') }}</th>
                        <th>{{ __('Operation Status') }}</th>
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
            url: '{!! route("swm.landfill-logs.data") !!}',
            data: function(d) {
                d.vehicle_search = $('#vehicle_search').val();
                d.landfill_id = $('#landfill_id').val();
                d.operation_status = $('#operation_status').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'entry_at', name: 'entry_at' },
            { data: 'operation_date', name: 'operation_date' },
            { data: 'vehicle_number', name: 'vehicle_number', orderable: true, searchable: false },
            { data: 'landfill_label', name: 'landfill_name' },
            { data: 'waste_type_label', name: 'waste_type_name' },
            { data: 'quantity_ton', name: 'quantity_ton' },
            { data: 'weighbridge_weight_ton', name: 'weighbridge_weight_ton' },
            { data: 'effective_quantity_ton', name: 'effective_quantity_ton', orderable: false, searchable: false },
            { data: 'operation_status', name: 'operation_status' },
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
        var vehicle_search = $('#vehicle_search').val() || '';
        var landfill_id = $('#landfill_id').val() || '';
        var operation_status = $('#operation_status').val() || '';
        var date_from = $('#date_from').val() || '';
        var date_to = $('#date_to').val() || '';
        window.location.href = "{!! route('swm.landfill-logs.export') !!}?vehicle_search=" + encodeURIComponent(vehicle_search) +
            "&landfill_id=" + encodeURIComponent(landfill_id) +
            "&operation_status=" + encodeURIComponent(operation_status) +
            "&date_from=" + encodeURIComponent(date_from) +
            "&date_to=" + encodeURIComponent(date_to);
    });
});
</script>
@endpush
