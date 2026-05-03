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
<div class="card">
    <div class="card-header">
        @can('Add Household')
        <a href="{{ action('BuildingInfo\HouseholdController@create') }}" class="btn btn-info">{{ __('Add Household') }}</a>
        @endcan
        @can('Export Households to CSV')
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
                    <label for="household_id" class="col-md-2 col-form-label">{{ __('Household ID') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="household_id"></div>
                    <label for="household_owner_name" class="col-md-2 col-form-label">{{ __('Household Owner Name') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="household_owner_name"></div>
                    <label for="contact_number" class="col-md-2 col-form-label">{{ __('Contact Number') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="contact_number"></div>
                </div>
                <div class="form-group row">
                    <label for="bin" class="col-md-2 col-form-label">{{ __('BIN') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="bin"></div>
                    <label for="is_lic" class="col-md-2 col-form-label">{{ __('LIC') }}</label>
                    <div class="col-md-2">
                        <select class="form-control" id="is_lic">
                            <option value="">{{ __('All') }}</option>
                            <option value="true">{{ __('Yes') }}</option>
                            <option value="false">{{ __('No') }}</option>
                        </select>
                    </div>
                    <label for="lic_id" class="col-md-2 col-form-label">{{ __('LIC ID') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="lic_id"></div>
                </div>
                <div class="form-group row">
                    <label for="survey_date" class="col-md-2 col-form-label">{{ __('Survey Date') }}</label>
                    <div class="col-md-2"><input type="date" class="form-control" id="survey_date"></div>
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
                        <th>{{ __('Household ID') }}</th>
                        <th>{{ __('Household Owner Name') }}</th>
                        <th>{{ __('Contact Number') }}</th>
                        <th>{{ __('BIN') }}</th>
                        <th>{{ __('Ward') }}</th>
                        <th>{{ __('Sub Location') }}</th>
                        <th>{{ __('Road No./Name') }}</th>
                        <th>{{ __('LIC') }}</th>
                        <th>{{ __('LIC ID') }}</th>
                        <th>{{ __('Survey Date') }}</th>
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
            url: '{!! route("building-info.households.data") !!}',
            data: function(d) {
                d.household_id = $('#household_id').val();
                d.household_owner_name = $('#household_owner_name').val();
                d.contact_number = $('#contact_number').val();
                d.bin = $('#bin').val();
                d.is_lic = $('#is_lic').val();
                d.lic_id = $('#lic_id').val();
                d.survey_date = $('#survey_date').val();
            }
        },
        columns: [
            { data: 'household_id', name: 'household_id' },
            { data: 'household_owner_name', name: 'household_owner_name' },
            { data: 'contact_number', name: 'contact_number' },
            { data: 'bin', name: 'bin' },
            { data: 'ward', name: 'ward' },
            { data: 'sub_location', name: 'sub_location' },
            { data: 'road_no_name', name: 'road_no_name' },
            { data: 'is_lic', name: 'is_lic' },
            { data: 'lic_id', name: 'lic_id' },
            { data: 'survey_date', name: 'survey_date' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    }).on('draw', function() {
        $('.delete').on('click', function(e) {
            var form = $(this).closest('form');
            e.preventDefault();
            Swal.fire({
                title: '{{ __('Are you sure?') }}',
                text: @json(__("You won't be able to revert this!")),
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
        window.location.href = "{!! route('building-info.households.export') !!}?searchData=" + searchData +
            "&household_id=" + encodeURIComponent($('#household_id').val()) +
            "&household_owner_name=" + encodeURIComponent($('#household_owner_name').val()) +
            "&contact_number=" + encodeURIComponent($('#contact_number').val()) +
            "&bin=" + encodeURIComponent($('#bin').val()) +
            "&is_lic=" + encodeURIComponent($('#is_lic').val()) +
            "&lic_id=" + encodeURIComponent($('#lic_id').val()) +
            "&survey_date=" + encodeURIComponent($('#survey_date').val());
    });
});
</script>
@endpush
