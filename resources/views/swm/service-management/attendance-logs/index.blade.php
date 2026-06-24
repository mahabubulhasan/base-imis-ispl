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
<div class="card app-mobile-index">
    <div class="card-header">
        @include('swm.partials.excel-import-export-header', [
            'addPermission' => 'Add SW Attendance Log',
            'addRoute' => route('swm.attendance-logs.create'),
            'addLabel' => __('Add Attendance Log'),
            'importRoute' => route('swm.attendance-logs.import'),
            'importPermission' => 'Import SW Attendance Logs From Excel',
            'templateRoute' => route('swm.attendance-logs.template'),
            'exportPermission' => 'Export SW Attendance Logs to Excel',
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
                    @if(!$scopedOrganizationId)
                    <label for="filter_organization_id" class="col-md-2 col-form-label">{{ __('Organization') }}</label>
                    <div class="col-md-2">
                        <select class="form-control" id="filter_organization_id">
                            <option value="">{{ __('All') }}</option>
                            @foreach($organizations as $oid => $oname)
                            <option value="{{ $oid }}">{{ $oname }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <label for="worker_search" class="col-md-2 col-form-label">{{ __('Worker Name-ID') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="worker_search" /></div>
                    <label for="attendance_status" class="col-md-2 col-form-label">{{ __('Attendance Status') }}</label>
                    <div class="col-md-2">
                        <select class="form-control" id="attendance_status">
                            <option value="">{{ __('All') }}</option>
                            @foreach($statusOptions as $k => $label)
                            <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="date_from" class="col-md-2 col-form-label">{{ __('Date From') }}</label>
                    <div class="col-md-2"><input type="date" class="form-control" id="date_from" /></div>
                    <label for="date_to" class="col-md-2 col-form-label">{{ __('Date To') }}</label>
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
            <table id="data-table" class="table table-bordered table-striped tbl-aligned" width="100%">
                <thead>
                    <tr>
                        <th>{{ __('Attendance Log ID') }}</th>
                        <th>{{ __('Entry Date and Time') }}</th>
                        <th>{{ __('Organization') }}</th>
                        <th>{{ __('Worker Name-ID') }}</th>
                        <th>{{ __('Worker Type') }}</th>
                        <th>{{ __("Supervisor's Name") }}</th>
                        <th>{{ __('Attendance Status') }}</th>
                        <th>{{ __('Check-in Time') }}</th>
                        <th>{{ __('Check-out Time') }}</th>
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
            url: '{!! route("swm.attendance-logs.data") !!}',
            data: function(d) {
                d.organization_id = $('#filter_organization_id').length ? $('#filter_organization_id').val() : '';
                d.worker_search = $('#worker_search').val();
                d.attendance_status = $('#attendance_status').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'entry_at', name: 'entry_at' },
            { data: 'organization_name', name: 'organization_id', orderable: true },
            { data: 'worker_label', name: 'worker_id', orderable: false, searchable: false },
            { data: 'work_type_name', name: 'work_type_name' },
            { data: 'supervisor_name', name: 'supervisor_name' },
            { data: 'attendance_status', name: 'attendance_status' },
            { data: 'check_in_at', name: 'check_in_at' },
            { data: 'check_out_at', name: 'check_out_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']]
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
        var organization_id = $('#filter_organization_id').length ? ($('#filter_organization_id').val() || '') : '';
        var worker_search = $('#worker_search').val() || '';
        var attendance_status = $('#attendance_status').val() || '';
        var date_from = $('#date_from').val() || '';
        var date_to = $('#date_to').val() || '';
        window.location.href = "{!! route('swm.attendance-logs.export') !!}?organization_id=" + encodeURIComponent(organization_id) +
            "&worker_search=" + encodeURIComponent(worker_search) +
            "&attendance_status=" + encodeURIComponent(attendance_status) +
            "&date_from=" + encodeURIComponent(date_from) +
            "&date_to=" + encodeURIComponent(date_to);
    });
});
</script>
@endpush
