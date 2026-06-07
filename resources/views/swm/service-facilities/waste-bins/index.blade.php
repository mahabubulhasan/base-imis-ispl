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
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="data-table" class="table table-bordered table-striped" width="100%">
                <thead>
                    <tr>
                        <th>{{ __('Waste Bin ID') }}</th>
                        <th>{{ __('Type of Waste Bin') }}</th>
                        <th>{{ __('Placed at Buildings') }}</th>
                        <th>{{ __('BIN') }}</th>
                        <th>{{ __('Ward No.') }}</th>
                        <th>{{ __('Capacity (kg)') }}</th>
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
    $('#data-table').DataTable({
        bFilter: false,
        processing: true,
        serverSide: true,
        scrollCollapse: true,
        ajax: '{!! route("swm.waste-bins.data") !!}',
        columns: [
            { data: 'waste_bin_id', name: 'waste_bin_id' },
            { data: 'waste_bin_type_name', name: 'waste_bin_type_name', orderable: false, searchable: false },
            { data: 'placed_at_buildings_label', name: 'placed_at_buildings_label', orderable: false, searchable: false },
            { data: 'bin', name: 'bin' },
            { data: 'ward_no', name: 'ward_no' },
            { data: 'total_capacity_kg', name: 'total_capacity_kg' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#export').on('click', function (e) {
        e.preventDefault();
        window.location.href = "{!! route('swm.waste-bins.export') !!}";
    });

    $('#data-table').on('draw', function () {
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
});
</script>
@endpush
