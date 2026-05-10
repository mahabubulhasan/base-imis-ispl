@extends('layouts.dashboard')
@push('style')
<style type="text/css">
.dataTables_filter {
    display: none;
}
.bs-filter-select2 .select2-container {
    width: 100% !important;
    max-width: 100%;
}
.bs-filter-select2 .select2-selection--multiple {
    min-height: calc(1.5em + 0.75rem + 2px);
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}
#data-table thead th {
    vertical-align: middle;
    white-space: nowrap;
}
#data-table thead tr.header-group th {
    background: #f4f6f9;
    font-weight: 700;
    text-align: center;
}
#data-table thead tr.header-columns th {
    background: #ffffff;
    font-weight: 600;
    font-size: 0.86rem;
    line-height: 1.2;
    text-align: center;
}
</style>
@endpush
@section('title', $page_title)
@section('content')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')

@php
    $defaultMonthFrom = now()->copy()->subMonths(5)->format('Y-m');
    $defaultMonthTo = now()->format('Y-m');
@endphp

<div class="row">
    <div class="col-md-4 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-calendar-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Due for this month') }}</span>
                <span class="info-box-number" id="summary-due-this-month">—</span>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-file-invoice-dollar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Total due') }} ({{ __('Taka') }})</span>
                <span class="info-box-number" id="summary-total-due">—</span>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-coins"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ __('Total revenue collected') }} ({{ __('Taka') }})</span>
                <span class="info-box-number" id="summary-revenue-ytd">—</span>
            </div>
        </div>
    </div>
</div>

<div class="card app-mobile-index">
    <div class="card-header">
        <a href="#" class="btn btn-danger float-right ml-2" id="download-pdf">
            {{ __('Download PDF') }}
        </a>
        <a href="#" class="btn btn-info float-right" id="headingFilters" type="button" data-toggle="collapse"
            data-target="#collapseFilters" aria-expanded="true" aria-controls="collapseFilters">
            {{ __('Show Filter') }}
        </a>
    </div>
    <div class="card-body">
        <div id="collapseFilters" class="collapse">
            <form class="form-horizontal" id="filter-form">
                <div class="form-group row">
                    <label for="month_from" class="col-md-2 col-form-label">{{ __('Month from') }}</label>
                    <div class="col-md-2"><input type="month" class="form-control" id="month_from" value="{{ $defaultMonthFrom }}" /></div>
                    <label for="month_to" class="col-md-2 col-form-label">{{ __('Month to') }}</label>
                    <div class="col-md-2"><input type="month" class="form-control" id="month_to" value="{{ $defaultMonthTo }}" /></div>
                    <label for="filter_holding_select" class="col-md-2 col-form-label">{{ __('Holding Number') }}</label>
                    <div class="col-md-2 bs-filter-select2">
                        <select class="form-control" id="filter_holding_select" name="holding_numbers[]" multiple="multiple" style="width:100%"></select>
                        <small class="form-text text-muted">{{ __('Search and select one or more holdings (min. 2 characters).') }}</small>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="filter_customer_select" class="col-md-2 col-form-label">{{ __('Household ID') }}</label>
                    <div class="col-md-2 bs-filter-select2">
                        <select class="form-control" id="filter_customer_select" name="customer_site_ids[]" multiple="multiple" style="width:100%"></select>
                        <small class="form-text text-muted">{{ __('Search households globally, or narrow by selected holdings.') }}</small>
                    </div>
                    <label for="is_owner" class="col-md-2 col-form-label">{{ __('Owner') }}</label>
                    <div class="col-md-2">
                        <select class="form-control" id="is_owner">
                            <option value="">{{ __('All') }}</option>
                            <option value="1">{{ __('Yes') }}</option>
                            <option value="0">{{ __('No') }}</option>
                        </select>
                    </div>
                    <label for="van_puller_id" class="col-md-2 col-form-label">{{ __('Van Puller') }}</label>
                    <div class="col-md-2">
                        <select class="form-control chosen-select" id="van_puller_id" data-placeholder="{{ __('Select Van Puller') }}">
                            <option value="">{{ __('All') }}</option>
                            @foreach($vanPullers as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-info">{{ __('Filter') }}</button>
                    <button type="button" id="reset-filter" class="btn btn-info">{{ __('Reset') }}</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="data-table" class="table table-bordered table-striped" width="100%">
                <thead>
                    <tr class="header-group">
                        <th rowspan="2">{{ __('SL') }}</th>
                        <th rowspan="2">{{ __('Holding Number') }}</th>
                        <th rowspan="2">{{ __('Household ID') }}</th>
                        <th rowspan="2">{{ __('Household Owner Name') }}</th>
                        <th rowspan="2">{{ __("Father's/Husband's Name") }}</th>
                        <th rowspan="2">{{ __('Sub Location') }}</th>
                        <th rowspan="2">{{ __('Ward') }}</th>
                        <th rowspan="2">{{ __('Contact Number') }}</th>
                        <th colspan="9">{{ __('Amount Section') }} ({{ __('Taka') }})</th>
                    </tr>
                    <tr class="header-columns">
                        <th>{{ __('Fixed Service Fee') }}</th>
                        <th>{{ __('Previous Due') }}</th>
                        <th>{{ __('Current Due') }}</th>
                        <th>{{ __('Payable Amount') }}</th>
                        <th>{{ __('Due Months') }}</th>
                        <th>{{ __('Current Paid') }}</th>
                        <th>{{ __('Previous Due Paid') }}</th>
                        <th>{{ __('Total Collected') }}</th>
                        <th>{{ __('Closing Due') }}</th>
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
    var holdingsUrl = @json(route('swm.bill-collection.holdings-search'));
    var customersSearchUrl = @json(route('swm.billing-status.customers-search'));
    var pdfUrl = @json(route('swm.billing-status.pdf'));
    var csrf = @json(csrf_token());

    function loadSummary() {
        $.getJSON('{!! route("swm.billing-status.summary") !!}')
            .done(function(data) {
                $('#summary-due-this-month').text(data.due_for_this_month);
                $('#summary-total-due').text(data.total_due);
                $('#summary-revenue-ytd').text(data.total_revenue_collected);
            })
            .fail(function() {
                $('#summary-due-this-month').text('—');
                $('#summary-total-due').text('—');
                $('#summary-revenue-ytd').text('—');
            });
    }

    loadSummary();

    if ($.fn.chosen) {
        $('#van_puller_id').chosen({ width: '100%', allow_single_deselect: true });
    }

    $('#filter_holding_select').select2({
        placeholder: '{{ __('Search holdings') }}',
        allowClear: true,
        multiple: true,
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

    $('#filter_holding_select').on('select2:opening', function() {
        $('#filter_customer_select').prop('disabled', true);
    });
    $('#filter_holding_select').on('select2:close', function() {
        $('#filter_customer_select').prop('disabled', false);
    });

    function selectedHoldingNumbers() {
        return $('#filter_holding_select').val() || [];
    }

    function customerMinInputLength() {
        return selectedHoldingNumbers().length > 0 ? 0 : 2;
    }

    function initCustomerSelect2() {
        if ($('#filter_customer_select').data('select2')) {
            $('#filter_customer_select').select2('destroy');
        }
        $('#filter_customer_select').select2({
            placeholder: '{{ __('Search customers') }}',
            allowClear: true,
            multiple: true,
            minimumInputLength: customerMinInputLength(),
            ajax: {
                url: customersSearchUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    var holdings = selectedHoldingNumbers();
                    return {
                        q: params.term,
                        holding_numbers: holdings
                    };
                },
                traditional: true,
                processResults: function(data) {
                    return { results: data.results || [] };
                },
                headers: { 'X-CSRF-TOKEN': csrf }
            }
        });
    }

    initCustomerSelect2();

    $('#filter_holding_select').on('change', function() {
        $('#filter_customer_select').val(null).trigger('change');
        initCustomerSelect2();
    });

    var dataTable = $('#data-table').DataTable({
        bFilter: false,
        searching: false,
        processing: true,
        serverSide: true,
        scrollCollapse: true,
        ajax: {
            url: '{!! route("swm.billing-status.data") !!}',
            data: function(d) {
                d.month_from = $('#month_from').val();
                d.month_to = $('#month_to').val();
                d.holding_numbers = selectedHoldingNumbers();
                d.customer_site_ids = $('#filter_customer_select').val();
                d.is_owner = $('#is_owner').val();
                d.van_puller_id = $('#van_puller_id').val();
            }
        },
        columns: [
            {
                data: null,
                name: 'sl',
                searchable: false,
                orderable: false,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'holding_number', name: 'holding_number', searchable: false, orderable: true },
            { data: 'household_id', name: 'household_id', searchable: false, orderable: true },
            { data: 'household_owner_name', name: 'household_owner_name', searchable: false, orderable: true },
            { data: 'father_or_husband_name', name: 'father_or_husband_name', searchable: false, orderable: true },
            { data: 'sub_location', name: 'sub_location', searchable: false, orderable: true },
            { data: 'ward', name: 'ward', searchable: false, orderable: true },
            { data: 'contact_number', name: 'contact_number', searchable: false, orderable: true },
            { data: 'current_service_fee', name: 'current_service_fee', searchable: false, orderable: false, className: 'text-right' },
            { data: 'previous_due_amount', name: 'previous_due_amount', searchable: false, orderable: false, className: 'text-right' },
            { data: 'due_current_month', name: 'due_current_month', searchable: false, orderable: false, className: 'text-right' },
            { data: 'total_due_amount', name: 'total_due_amount', searchable: false, orderable: false, className: 'text-right' },
            { data: 'due_months_of', name: 'due_months_of', searchable: false, orderable: false },
            { data: 'current_month_paid', name: 'current_month_paid', searchable: false, orderable: true, className: 'text-right' },
            { data: 'previous_due_paid', name: 'previous_due_paid', searchable: false, orderable: true, className: 'text-right' },
            { data: 'revenue_collected', name: 'revenue_collected', searchable: false, orderable: true, className: 'text-right' },
            { data: 'remaining_due', name: 'remaining_due', searchable: false, orderable: false, className: 'text-right' }
        ],
        order: [[1, 'asc']]
    });

    if (typeof resetDataTable === 'function') {
        resetDataTable(dataTable);
    }

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        dataTable.draw();
    });

    $('#reset-filter').on('click', function() {
        $('#month_from').val('{{ $defaultMonthFrom }}');
        $('#month_to').val('{{ $defaultMonthTo }}');
        $('#filter_holding_select').val(null).trigger('change');
        $('#filter_customer_select').val(null).trigger('change');
        $('#is_owner').val('');
        $('#van_puller_id').val('').trigger('chosen:updated');
        dataTable.draw();
    });

    $('#download-pdf').on('click', function(e) {
        e.preventDefault();
        var params = new URLSearchParams();
        params.set('month_from', $('#month_from').val() || '');
        params.set('month_to', $('#month_to').val() || '');
        params.set('is_owner', $('#is_owner').val() || '');
        params.set('van_puller_id', $('#van_puller_id').val() || '');

        var holdings = selectedHoldingNumbers();
        for (var i = 0; i < holdings.length; i++) {
            params.append('holding_numbers[]', holdings[i]);
        }

        var siteIds = $('#filter_customer_select').val() || [];
        for (var j = 0; j < siteIds.length; j++) {
            params.append('customer_site_ids[]', siteIds[j]);
        }

        window.open(pdfUrl + '?' + params.toString(), '_blank');
    });
});
</script>
@endpush
