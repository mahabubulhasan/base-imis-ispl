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
        @can('Add SW LIC')
        <a href="{{ action('Swm\LicController@create') }}" class="btn btn-info">{{ __('Add LIC') }}</a>
        @endcan
        @can('Export SW LIC to CSV')
        <a href="#" id="export" class="btn btn-info">{{ __('Export to CSV') }}</a>
        @endcan
        <a href="#" class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse"
            data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            {{ __('Show Filter') }}
        </a>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                            <form class="form-horizontal" id="filter-form">
                                <div class="form-group row">
                                    <label for="lic_id" class="col-md-2 col-form-label">{{ __('LIC ID') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="lic_id" placeholder="{{ __('LIC ID') }}" />
                                    </div>
                                    <label for="representative_name" class="col-md-2 col-form-label">{{ __("LIC Representative's Name") }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="representative_name" placeholder="{{ __("LIC Representative's Name") }}" />
                                    </div>
                                    <label for="contact_no" class="col-md-2 col-form-label">{{ __('Contact No.') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="contact_no" placeholder="{{ __('Contact No.') }}" />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="number_of_hhs" class="col-md-2 col-form-label">{{ __('Number of HHs') }}</label>
                                    <div class="col-md-2">
                                        <input type="number" min="0" class="form-control" id="number_of_hhs" placeholder="{{ __('Number of HHs') }}" />
                                    </div>
                                    <label for="total_population" class="col-md-2 col-form-label">{{ __('Total Population') }}</label>
                                    <div class="col-md-2">
                                        <input type="number" min="0" class="form-control" id="total_population" placeholder="{{ __('Total Population') }}" />
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-info">{{ __('Filter') }}</button>
                                    <button type="reset" id="reset-filter" class="btn btn-info">{{ __('Reset') }}</button>
                                </div>
                            </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
    <div style="overflow: auto; width: 100%;">
        <table id="data-table" class="table table-bordered table-striped tbl-aligned" width="100%">
            <thead>
                <tr>
                    <th>{{ __('LIC ID') }}</th>
                    <th>{{ __("LIC Representative's Name") }}</th>
                    <th>{{ __('Contact No.') }}</th>
                    <th>{{ __('Number of HHs') }}</th>
                    <th>{{ __('Total Population') }}</th>
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
            url: '{!! route("swm.lic.data") !!}',
            data: function(d) {
                d.lic_id = $('#lic_id').val();
                d.representative_name = $('#representative_name').val();
                d.contact_no = $('#contact_no').val();
                d.number_of_hhs = $('#number_of_hhs').val();
                d.total_population = $('#total_population').val();
            }
        },
        columns: [
            { data: 'lic_id', name: 'lic_id' },
            { data: 'representative_name', name: 'representative_name' },
            { data: 'contact_no', name: 'contact_no' },
            { data: 'number_of_hhs', name: 'number_of_hhs', className: 'col-num' },
            { data: 'total_population', name: 'total_population', className: 'col-num' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [ [0, 'asc'] ]
    }).on('draw', function() {
        $('.delete').on('click', function(e) {
            var form = $(this).closest("form");
            event.preventDefault();
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
            })
        });
    });

    resetDataTable(dataTable);

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        dataTable.draw();
    });

    $("#export").on("click", function(e) {
        e.preventDefault();
        var searchData = $('input[type=search]').val();
        window.location.href = "{!! route('swm.lic.export') !!}?searchData=" + searchData +
            "&lic_id=" + encodeURIComponent($('#lic_id').val()) +
            "&representative_name=" + encodeURIComponent($('#representative_name').val()) +
            "&contact_no=" + encodeURIComponent($('#contact_no').val()) +
            "&number_of_hhs=" + encodeURIComponent($('#number_of_hhs').val()) +
            "&total_population=" + encodeURIComponent($('#total_population').val());
    })
});
</script>
@endpush
