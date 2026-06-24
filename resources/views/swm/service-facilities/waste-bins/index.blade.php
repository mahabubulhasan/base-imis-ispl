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
            'addPermission' => 'Add SW Waste Bin',
            'addRoute' => route('swm.waste-bins.create'),
            'addLabel' => __('Add Waste Bin'),
            'importRoute' => route('swm.waste-bins.import'),
            'importPermission' => 'Import SW Waste Bins From Excel',
            'templateRoute' => route('swm.waste-bins.template'),
            'exportPermission' => 'Export SW Waste Bins to Excel',
            'exportId' => 'export',
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
                    <label for="waste_bin_id" class="col-md-2 col-form-label">{{ __('Waste Bin ID') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="waste_bin_id" /></div>
                    <label for="waste_bin_type_id" class="col-md-2 col-form-label">{{ __('Waste Bin Type') }}</label>
                    <div class="col-md-2">
                        <select class="form-control" id="waste_bin_type_id">
                            <option value="">{{ __('All') }}</option>
                            @foreach($wasteBinTypes as $typeId => $typeName)
                            <option value="{{ $typeId }}">{{ $typeName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="bin" class="col-md-2 col-form-label">{{ __('BIN') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="bin" /></div>
                </div>
                <div class="form-group row">
                    <label for="ward_no" class="col-md-2 col-form-label">{{ __('Ward No.') }}</label>
                    <div class="col-md-2">
                        <select class="form-control" id="ward_no">
                            <option value="">{{ __('All') }}</option>
                            @foreach($wards as $ward)
                            <option value="{{ $ward }}">{{ $ward }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="placed_at_buildings" class="col-md-2 col-form-label">{{ __('Placed at Buildings?') }}</label>
                    <div class="col-md-2">
                        <select class="form-control" id="placed_at_buildings">
                            <option value="">{{ __('All') }}</option>
                            <option value="true">{{ __('Yes') }}</option>
                            <option value="false">{{ __('No') }}</option>
                        </select>
                    </div>
                    <label for="road_no" class="col-md-2 col-form-label">{{ __('Road No.') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="road_no" /></div>
                </div>
                <div class="form-group row">
                    <label for="road_name" class="col-md-2 col-form-label">{{ __('Road Name') }}</label>
                    <div class="col-md-2"><input type="text" class="form-control" id="road_name" /></div>
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
                        <th>{{ __('Waste Bin ID') }}</th>
                        <th>{{ __('Waste Bin Type') }}</th>
                        <th>{{ __('Capacity (kg)') }}</th>
                        <th>{{ __('Placed at Buildings?') }}</th>
                        <th>{{ __('BIN') }}</th>
                        <th>{{ __('Ward No.') }}</th>
                        <th>{{ __('Road No.') }}</th>
                        <th>{{ __('Road Name') }}</th>
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
$(function () {
    var dataTable = $('#data-table').DataTable({
        bFilter: false,
        processing: true,
        serverSide: true,
        scrollCollapse: true,
        ajax: {
            url: '{!! route("swm.waste-bins.data") !!}',
            data: function (d) {
                d.waste_bin_id = $('#waste_bin_id').val();
                d.waste_bin_type_id = $('#waste_bin_type_id').val();
                d.bin = $('#bin').val();
                d.ward_no = $('#ward_no').val();
                d.placed_at_buildings = $('#placed_at_buildings').val();
                d.road_no = $('#road_no').val();
                d.road_name = $('#road_name').val();
            }
        },
        columns: [
            { data: 'waste_bin_id', name: 'waste_bin_id' },
            { data: 'waste_bin_type_name', name: 'waste_bin_type_name', orderable: false, searchable: false },
            { data: 'total_capacity_kg', name: 'total_capacity_kg', className: 'col-num' },
            { data: 'placed_at_buildings_label', name: 'placed_at_buildings_label', orderable: false, searchable: false },
            { data: 'bin', name: 'bin' },
            { data: 'ward_no', name: 'ward_no', className: 'col-num' },
            { data: 'road_no', name: 'road_no' },
            { data: 'road_name', name: 'road_name' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    }).on('draw', function () {
        $('.delete').on('click', function (e) {
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
                cancelButtonText: '{{ __('Cancel') }}'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    resetDataTable(dataTable);

    $('#filter-form').on('submit', function (e) {
        e.preventDefault();
        dataTable.draw();
    });

    $('#export').on('click', function (e) {
        e.preventDefault();
        window.location.href = "{!! route('swm.waste-bins.export') !!}?" +
            "waste_bin_id=" + encodeURIComponent($('#waste_bin_id').val() || '') +
            "&waste_bin_type_id=" + encodeURIComponent($('#waste_bin_type_id').val() || '') +
            "&bin=" + encodeURIComponent($('#bin').val() || '') +
            "&ward_no=" + encodeURIComponent($('#ward_no').val() || '') +
            "&placed_at_buildings=" + encodeURIComponent($('#placed_at_buildings').val() || '') +
            "&road_no=" + encodeURIComponent($('#road_no').val() || '') +
            "&road_name=" + encodeURIComponent($('#road_name').val() || '');
    });
});
</script>
@endpush
