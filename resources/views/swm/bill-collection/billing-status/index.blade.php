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
</style>
@endpush
@section('title', $page_title)
@section('content')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')

@php
    $defaultMonth = now()->format('Y-m');
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

<div class="card">
    <div class="card-header">
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
                    <div class="col-md-2"><input type="month" class="form-control" id="month_from" value="{{ $defaultMonth }}" /></div>
                    <label for="month_to" class="col-md-2 col-form-label">{{ __('Month to') }}</label>
                    <div class="col-md-2"><input type="month" class="form-control" id="month_to" value="{{ $defaultMonth }}" /></div>
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
        <div style="overflow: auto; width: 100%;">
            <table id="data-table" class="table table-bordered table-striped" width="100%">
                <thead>
                    <tr>
                        <th>{{ __('Current month due') }}</th>
                        <th>{{ __('Holding Number') }}</th>
                        <th>{{ __('Household ID') }}</th>
                        <th>{{ __('Months with due') }}</th>
                        <th>{{ __('Due in selected months') }} ({{ __('Taka') }})</th>
                        <th>{{ __('Cumilative total due') }} ({{ __('Taka') }})</th>
                        <th>{{ __('Revenue collected') }} ({{ __('Taka') }})</th>
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
            { data: 'due_current_month', name: 'due_current_month', searchable: false, orderable: false },
            { data: 'holding_number', name: 'holding_number', searchable: false, orderable: true },
            { data: 'household_id', name: 'household_id', searchable: false, orderable: true },
            { data: 'due_months_of', name: 'due_months_of', searchable: false, orderable: false },
            { data: 'due_in_selected_months', name: 'due_in_selected_months', searchable: false, orderable: false },
            { data: 'total_due_amount', name: 'total_due_amount', searchable: false, orderable: false },
            { data: 'revenue_collected', name: 'revenue_collected', searchable: false, orderable: true }
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
        $('#month_from').val('{{ $defaultMonth }}');
        $('#month_to').val('{{ $defaultMonth }}');
        $('#filter_holding_select').val(null).trigger('change');
        $('#filter_customer_select').val(null).trigger('change');
        $('#is_owner').val('');
        $('#van_puller_id').val('').trigger('chosen:updated');
        dataTable.draw();
    });
});
</script>
@endpush
