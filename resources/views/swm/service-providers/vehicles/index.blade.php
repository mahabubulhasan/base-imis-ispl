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
<div class="card app-mobile-index">
    <div class="card-header">
        @include('swm.partials.excel-import-export-header', [
            'addPermission' => 'Add SW Vehicle',
            'addRoute' => action('Swm\VehicleController@create'),
            'addLabel' => __('Add Vehicle'),
            'importRoute' => route('swm.vehicles.import'),
            'importPermission' => 'Import SW Vehicles From Excel',
            'templateRoute' => route('swm.vehicles.template'),
            'exportPermission' => 'Export SW Vehicles to Excel',
        ])
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
                                    <label for="vehicle_number" class="col-md-2 col-form-label">{{ __('Vehicle No.') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="vehicle_number" placeholder="{{ __('Vehicle No.') }}" />
                                    </div>
                                    <label for="vehicle_id_no" class="col-md-2 col-form-label">{{ __('Vehicle ID') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="vehicle_id_no" placeholder="{{ __('Vehicle ID') }}" />
                                    </div>
                                    @if(empty($scopedOrganizationId))
                                    <label for="organization_id" class="col-md-2 col-form-label">{{ __('Organization') }}</label>
                                    <div class="col-md-2">
                                        <select class="form-control chosen-select" id="organization_id" name="organization_id">
                                            <option value="">{{ __('Organization') }}</option>
                                            @foreach($organizations as $oid => $oname)
                                            <option value="{{ $oid }}">{{ $oname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <label for="vehicle_type_id" class="col-md-2 col-form-label">{{ __('Vehicle Type') }}</label>
                                    <div class="col-md-2">
                                        <select class="form-control chosen-select" id="vehicle_type_id" name="vehicle_type_id">
                                            <option value="">{{ __('Vehicle Type') }}</option>
                                            @foreach($vehicleTypes as $vid => $vname)
                                            <option value="{{ $vid }}">{{ $vname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="driver_worker_id" class="col-md-2 col-form-label">{{ __('Driver') }}</label>
                                    <div class="col-md-2">
                                        <select class="form-control chosen-select" id="driver_worker_id" name="driver_worker_id">
                                            <option value="">{{ __('Driver') }}</option>
                                            @foreach($driverWorkers as $did => $dname)
                                            <option value="{{ $did }}">{{ $dname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label for="chassis_no" class="col-md-2 col-form-label">{{ __('Chassis No.') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="chassis_no" placeholder="{{ __('Chassis No.') }}" />
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
    <div class="table-responsive">
        <table id="data-table" class="table table-bordered table-striped tbl-aligned" width="100%">
            <thead>
                <tr>
                <th>{{ __('Vehicle ID') }}</th>
                <th>{{ __('Vehicle No.') }}</th>
                <th>{{ __('Vehicle Type') }}</th>
                <th>{{ __('Capacity (Ton)') }}</th>
                <th>{{ __('Organization') }}</th>
                <th>{{ __('Driver') }}</th>
                <th>{{ __('Dumping Place') }}</th>
                <th>{{ __('Operational Type') }}</th>
                <th>{{ __('Status') }}</th>
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
            url: '{!! route("swm.vehicles.data") !!}',
            data: function(d) {
                d.vehicle_number = $('#vehicle_number').val();
                d.vehicle_id_no = $('#vehicle_id_no').val();
                d.chassis_no = $('#chassis_no').val();
                @if(empty($scopedOrganizationId))
                d.organization_id = $('#organization_id').val();
                @endif
                d.vehicle_type_id = $('#vehicle_type_id').val();
                d.driver_worker_id = $('#driver_worker_id').val();
            }
        },
        columns: [{
                data: 'vehicle_id_no',
                name: 'swm.vehicles.vehicle_id_no'
            },
            {
                data: 'vehicle_number',
                name: 'swm.vehicles.vehicle_number'
            },
            {
                data: 'vehicle_type_name',
                name: 'vehicle_type_name'
            },
            {
                data: 'capacity',
                name: 'swm.vehicles.capacity',
                className: 'col-num'
            },
            {
                data: 'organization_name',
                name: 'organization_name'
            },
            {
                data: 'driver_name',
                name: 'driver_name'
            },
            {
                data: 'dumping_place',
                name: 'dumping_place',
                orderable: false,
                searchable: false
            },
            {
                data: 'operational_type_label',
                name: 'operational_type_label',
                orderable: false,
                searchable: false
            },
            {
                data: 'status',
                name: 'swm.vehicles.status'
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
        var vehicle_number = $('#vehicle_number').val();
        var vehicle_id_no = $('#vehicle_id_no').val();
        var chassis_no = $('#chassis_no').val();
        var organization_id = '';
        @if(empty($scopedOrganizationId))
        organization_id = $('#organization_id').val() || '';
        @endif
        var vehicle_type_id = $('#vehicle_type_id').val();
        var driver_worker_id = $('#driver_worker_id').val();
        window.location.href = "{!! route('swm.vehicles.export') !!}?searchData=" + searchData +
            "&vehicle_number=" + encodeURIComponent(vehicle_number || '') +
            "&vehicle_id_no=" + encodeURIComponent(vehicle_id_no || '') +
            "&chassis_no=" + encodeURIComponent(chassis_no || '') +
            "&organization_id=" + encodeURIComponent(organization_id || '') +
            "&vehicle_type_id=" + encodeURIComponent(vehicle_type_id || '') +
            "&driver_worker_id=" + encodeURIComponent(driver_worker_id || '');
    })
});
</script>
@endpush
