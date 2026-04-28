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
        @can('Add SW STS')
        <a href="{{ action('Swm\StsController@create') }}" class="btn btn-info">{{ __('Add STS') }}</a>
        @endcan
        @can('Export SW STS to CSV')
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
                                    <label for="name" class="col-md-2 col-form-label">{{ __('Name') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="name" placeholder="{{ __('Name') }}" />
                                    </div>
                                    <label for="operator_name" class="col-md-2 col-form-label">{{ __('Operator Name') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="operator_name" placeholder="{{ __('Operator Name') }}" />
                                    </div>
                                    <label for="contact_number" class="col-md-2 col-form-label">{{ __('Contact Number') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="contact_number" placeholder="{{ __('Contact Number') }}" />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="destination_landfill_id" class="col-md-2 col-form-label">{{ __('Destination Landfill') }}</label>
                                    <div class="col-md-2">
                                        <select class="form-control chosen-select" id="destination_landfill_id" name="destination_landfill_id">
                                            <option value="">{{ __('Destination Landfill') }}</option>
                                            @foreach($landfills as $lid => $lname)
                                            <option value="{{ $lid }}">{{ $lname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label for="segregation_practiced" class="col-md-2 col-form-label">{{ __('Segregation Practiced') }}</label>
                                    <div class="col-md-2">
                                        <select class="form-control" id="segregation_practiced" name="segregation_practiced">
                                            <option value="">{{ __('All') }}</option>
                                            <option value="true">{{ __('Yes') }}</option>
                                            <option value="false">{{ __('No') }}</option>
                                        </select>
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
        <table id="data-table" class="table table-bordered table-striped" width="100%">
            <thead>
                <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Location') }}</th>
                <th>{{ __('Operator Name') }}</th>
                <th>{{ __('Contact Number') }}</th>
                <th>{{ __('Capacity') }}</th>
                <th>{{ __('Segregation Practiced') }}</th>
                <th>{{ __('Destination Landfill') }}</th>
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
            url: '{!! route("swm.sts.data") !!}',
            data: function(d) {
                d.name = $('#name').val();
                d.operator_name = $('#operator_name').val();
                d.contact_number = $('#contact_number').val();
                d.destination_landfill_id = $('#destination_landfill_id').val();
                d.segregation_practiced = $('#segregation_practiced').val();
            }
        },
        columns: [
            { data: 'name', name: 'swm.sts.name' },
            { data: 'location', name: 'swm.sts.location' },
            { data: 'operator_name', name: 'swm.sts.operator_name' },
            { data: 'contact_number', name: 'swm.sts.contact_number' },
            { data: 'capacity', name: 'swm.sts.capacity' },
            { data: 'segregation_practiced', name: 'swm.sts.segregation_practiced' },
            { data: 'destination_landfill_name', name: 'destination_landfill_name' },
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
        window.location.href = "{!! route('swm.sts.export') !!}?searchData=" + searchData +
            "&name=" + encodeURIComponent($('#name').val()) +
            "&operator_name=" + encodeURIComponent($('#operator_name').val()) +
            "&contact_number=" + encodeURIComponent($('#contact_number').val()) +
            "&destination_landfill_id=" + encodeURIComponent($('#destination_landfill_id').val() || '') +
            "&segregation_practiced=" + encodeURIComponent($('#segregation_practiced').val());
    })
});
</script>
@endpush
