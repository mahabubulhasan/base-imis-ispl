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
<div class="card">
    <div class="card-header">
        @can('Add SW Waste Bin Type')
        <a href="{{ action('Swm\WasteBinTypeController@create') }}" class="btn btn-info">{{ __('Add SW Waste Bin Type') }}</a>
        @endcan
        @can('Export SW Waste Bin Types to CSV')
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
                                    <label for="name" class="col-md-2 col-form-label">{{ __('Waste Bin Type Name') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="name" placeholder="{{ __('Waste Bin Type Name') }}" />
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
                <th>{{ __('Waste Bin Type Name') }}</th>
                <th>{{ __('Description') }}</th>
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
            url: '{!! route("swm.waste-bin-types.data") !!}',
            data: function(d) {
                d.name = $('#name').val();
            }
        },
        columns: [{
                data: 'name',
                name: 'name'
            },
            {
                data: 'description',
                name: 'description',
                defaultContent: '',
                orderable: false
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
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
        var name = $('#name').val();
        window.location.href = "{!! route('swm.waste-bin-types.export') !!}?searchData=" + searchData +
            "&name=" + encodeURIComponent(name);
    })
});
</script>
@endpush
