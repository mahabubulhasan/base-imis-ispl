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
        @can('Add SW Complaint')
        <a href="{{ route('swm.complaints.create') }}" class="btn btn-info">{{ __('Add Complaint') }}</a>
        @endcan
        @can('Export SW Complaints to CSV')
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
                    <label for="complaint_id" class="col-md-2 col-form-label">{{ __('Complaint ID') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="complaint_id" /></div>
                    <label for="date_from" class="col-md-2 col-form-label">{{ __('Date From') }}</label>
                    <div class="col-md-2"><input type="date" class="form-control" id="date_from" /></div>
                    <label for="date_to" class="col-md-2 col-form-label">{{ __('Date To') }}</label>
                    <div class="col-md-2"><input type="date" class="form-control" id="date_to" /></div>
                </div>
                <div class="form-group row">
                    <label for="filter_holding_number_select" class="col-md-2 col-form-label">{{ __('Holding Number') }}</label>
                    <div class="col-md-2"><select class="form-control" id="filter_holding_number_select" style="width:100%"></select></div>
                    <label for="filter_customer_id_select" class="col-md-2 col-form-label">{{ __('Household ID') }}</label>
                    <div class="col-md-2"><select class="form-control" id="filter_customer_id_select" style="width:100%"></select></div>
                    <label for="complaint_status" class="col-md-2 col-form-label">{{ __('Complaint Status') }}</label>
                    <div class="col-md-2">
                        <select class="form-control" id="complaint_status">
                            <option value="">{{ __('Select Complaint Status') }}</option>
                            @foreach($complaintStatuses as $k => $label)
                            <option value="{{ $k }}">{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="complaint_type" class="col-md-2 col-form-label">{{ __('Complaint Type') }}</label>
                    <div class="col-md-2">
                        <select class="form-control" id="complaint_type">
                            <option value="">{{ __('Select Complaint Type') }}</option>
                            @foreach($complaintTypes as $k => $label)
                            <option value="{{ $k }}">{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="submitted_through" class="col-md-2 col-form-label">{{ __('Complaint Submitted Through') }}</label>
                    <div class="col-md-2">
                        <select class="form-control" id="submitted_through">
                            <option value="">{{ __('Select') }}</option>
                            @foreach($submittedThroughOptions as $k => $label)
                            <option value="{{ $k }}">{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="contact_number" class="col-md-2 col-form-label">{{ __('Contact Number') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="contact_number" /></div>
                </div>
                <div class="form-group row">
                    <label for="priority_level" class="col-md-2 col-form-label">{{ __('Priority Level') }}</label>
                    <div class="col-md-2">
                        <select class="form-control" id="priority_level">
                            <option value="">{{ __('Select Priority') }}</option>
                            @foreach($priorityLevels as $k => $label)
                            <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="assigned_to" class="col-md-2 col-form-label">{{ __('Assigned To') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="assigned_to" /></div>
                    <label for="duplicate_complaint" class="col-md-2 col-form-label">{{ __('Duplicate Complaint') }}</label>
                    <div class="col-md-2">
                        <select class="form-control" id="duplicate_complaint">
                            <option value="">{{ __('Select') }}</option>
                            @foreach($duplicateOptions as $k => $label)
                            <option value="{{ $k }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="ward_no" class="col-md-2 col-form-label">{{ __('Ward No.') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="ward_no" /></div>
                    <label for="incident_date_from" class="col-md-2 col-form-label">{{ __('Incident Date From') }}</label>
                    <div class="col-md-2"><input type="date" class="form-control" id="incident_date_from" /></div>
                    <label for="incident_date_to" class="col-md-2 col-form-label">{{ __('Incident Date To') }}</label>
                    <div class="col-md-2"><input type="date" class="form-control" id="incident_date_to" /></div>
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
                        <th>{{ __('Complaint ID') }}</th>
                        <th>{{ __('Date and Time') }}</th>
                        <th>{{ __('Incident Date') }}</th>
                        <th>{{ __('Holding Number') }}</th>
                        <th>{{ __('Household ID') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Contact Number') }}</th>
                        <th>{{ __('Ward No.') }}</th>
                        <th>{{ __('Complaint Type') }}</th>
                        <th>{{ __('Complaint Submitted Through') }}</th>
                        <th>{{ __('Priority') }}</th>
                        <th>{{ __('Assigned To') }}</th>
                        <th>{{ __('Duplicate') }}</th>
                        <th>{{ __('Complaint Status') }}</th>
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
    var holdingsUrl = @json(route('swm.complaints.holdings-search'));
    var customersUrl = @json(route('swm.complaints.customers-search'));
    var csrf = @json(csrf_token());

    $('#filter_holding_number_select').select2({
        placeholder: '{{ __('Search Holdings') }}',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: holdingsUrl,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(data) {
                return { results: data.results || [] };
            },
            headers: { 'X-CSRF-TOKEN': csrf }
        }
    });

    function selectedHoldingFilter() {
        var v = $('#filter_holding_number_select').val();
        return v ? [v] : [];
    }

    function initCustomerFilterSelect2() {
        if ($('#filter_customer_id_select').data('select2')) {
            $('#filter_customer_id_select').select2('destroy');
        }
        $('#filter_customer_id_select').select2({
            placeholder: '{{ __('Search Households') }}',
            allowClear: true,
            minimumInputLength: selectedHoldingFilter().length > 0 ? 0 : 2,
            ajax: {
                url: customersUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        holding_numbers: selectedHoldingFilter(),
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return { results: data.results || [] };
                },
                headers: { 'X-CSRF-TOKEN': csrf }
            }
        });
    }

    initCustomerFilterSelect2();
    $('#filter_holding_number_select').on('change', function() {
        $('#filter_customer_id_select').val(null).trigger('change');
        initCustomerFilterSelect2();
    });

    var dataTable = $('#data-table').DataTable({
        bFilter: false,
        processing: true,
        serverSide: true,
        scrollCollapse: true,
        ajax: {
            url: '{!! route("swm.complaints.data") !!}',
            data: function(d) {
                d.complaint_id = $('#complaint_id').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.holding_number = $('#filter_holding_number_select').val();
                d.household_id = $('#filter_customer_id_select').val();
                d.contact_number = $('#contact_number').val();
                d.complaint_type = $('#complaint_type').val();
                d.submitted_through = $('#submitted_through').val();
                d.priority_level = $('#priority_level').val();
                d.assigned_to = $('#assigned_to').val();
                d.duplicate_complaint = $('#duplicate_complaint').val();
                d.ward_no = $('#ward_no').val();
                d.incident_date_from = $('#incident_date_from').val();
                d.incident_date_to = $('#incident_date_to').val();
                d.complaint_status = $('#complaint_status').val();
            }
        },
        columns: [
            { data: 'complaint_id', name: 'complaint_id' },
            { data: 'date_time', name: 'date_time' },
            { data: 'incident_date', name: 'incident_date' },
            { data: 'holding_number', name: 'holding_number' },
            { data: 'household_id', name: 'customer_id' },
            { data: 'name', name: 'name' },
            { data: 'contact_number', name: 'contact_number' },
            { data: 'ward_no', name: 'ward_no' },
            { data: 'complaint_type', name: 'complaint_type' },
            { data: 'submitted_through', name: 'submitted_through' },
            { data: 'priority_level', name: 'priority_level' },
            { data: 'assigned_to', name: 'assigned_to' },
            { data: 'duplicate_complaint_text', name: 'duplicate_complaint' },
            { data: 'complaint_status', name: 'complaint_status' },
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
                confirmButtonText: '{{ __('Yes, Delete It!') }}',
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
        var searchData = $('input[type=search]').val() || '';
        var complaint_id = $('#complaint_id').val() || '';
        var date_from = $('#date_from').val() || '';
        var date_to = $('#date_to').val() || '';
        var holding_number = $('#filter_holding_number_select').val() || '';
        var household_id = $('#filter_customer_id_select').val() || '';
        var contact_number = $('#contact_number').val() || '';
        var complaint_type = $('#complaint_type').val() || '';
        var submitted_through = $('#submitted_through').val() || '';
        var priority_level = $('#priority_level').val() || '';
        var assigned_to = $('#assigned_to').val() || '';
        var duplicate_complaint = $('#duplicate_complaint').val() || '';
        var ward_no = $('#ward_no').val() || '';
        var incident_date_from = $('#incident_date_from').val() || '';
        var incident_date_to = $('#incident_date_to').val() || '';
        var complaint_status = $('#complaint_status').val() || '';
        window.location.href = "{!! route('swm.complaints.export') !!}?searchData=" + encodeURIComponent(searchData) +
            "&complaint_id=" + encodeURIComponent(complaint_id) +
            "&date_from=" + encodeURIComponent(date_from) +
            "&date_to=" + encodeURIComponent(date_to) +
            "&holding_number=" + encodeURIComponent(holding_number) +
            "&household_id=" + encodeURIComponent(household_id) +
            "&contact_number=" + encodeURIComponent(contact_number) +
            "&complaint_type=" + encodeURIComponent(complaint_type) +
            "&submitted_through=" + encodeURIComponent(submitted_through) +
            "&priority_level=" + encodeURIComponent(priority_level) +
            "&assigned_to=" + encodeURIComponent(assigned_to) +
            "&duplicate_complaint=" + encodeURIComponent(duplicate_complaint) +
            "&ward_no=" + encodeURIComponent(ward_no) +
            "&incident_date_from=" + encodeURIComponent(incident_date_from) +
            "&incident_date_to=" + encodeURIComponent(incident_date_to) +
            "&complaint_status=" + encodeURIComponent(complaint_status);
    });
});
</script>
@endpush
