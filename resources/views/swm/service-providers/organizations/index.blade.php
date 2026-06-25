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
            'addPermission' => 'Add SW Organization',
            'addRoute' => action('Swm\OrganizationController@create'),
            'addLabel' => __('Add Organization'),
            'importRoute' => route('swm.organizations.import'),
            'importPermission' => 'Import SW Organizations From Excel',
            'templateRoute' => route('swm.organizations.template'),
            'exportPermission' => 'Export SW Organizations to Excel',
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
                                    <label for="name" class="col-md-2 col-form-label">{{ __('Organization Name') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="name" placeholder="{{ __('Organization Name') }}" />
                                    </div>
                                    <label for="email" class="col-md-2 col-form-label">{{ __('Email') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="email" placeholder="{{ __('Email') }}" />
                                    </div>
                                    <label for="address" class="col-md-2 col-form-label">{{ __('Address') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="address" placeholder="{{ __('Address') }}" />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="contact_person_name" class="col-md-2 col-form-label">{{ __('Contact Person Name') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="contact_person_name" placeholder="{{ __('Contact Person Name') }}" />
                                    </div>
                                    <label for="organization_type_id" class="col-md-2 col-form-label">{{ __('Organization Type') }}</label>
                                    <div class="col-md-2">
                                        <select class="form-control chosen-select" id="organization_type_id" name="organization_type_id">
                                            <option value="">{{ __('Organization Type') }}</option>
                                            @foreach($organizationTypes as $typeId => $typeName)
                                            <option value="{{ $typeId }}">{{ $typeName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label for="status" class="col-md-2 col-form-label">{{ __('Status') }}</label>
                                    <div class="col-md-2">
                                        <select class="form-control chosen-select" id="status" name="status">
                                            <option value="">{{ __('Status') }}</option>
                                            <option value="true">{{ __('Operational') }}</option>
                                            <option value="false">{{ __('Not Operational') }}</option>
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
    <div class="table-responsive">
        <table id="data-table" class="table table-bordered table-striped tbl-aligned" width="100%">
            <thead>
                <tr>
                <th>{{ __('Organization Name') }}</th>
                <th>{{ __('Organization Type') }}</th>
                <th>{{ __('Contact Person Name') }}</th>
                <th>{{ __('Contact No.') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Service Wards') }}</th>
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
            url: '{!! route("swm.organizations.data") !!}',
            data: function(d) {
                d.name = $('#name').val();
                d.email = $('#email').val();
                d.address = $('#address').val();
                d.contact_person_name = $('#contact_person_name').val();
                d.organization_type_id = $('#organization_type_id').val();
                d.status = $('#status').val();
            }
        },
        columns: [{
                data: 'name',
                name: 'name'
            },
            {
                data: 'organization_type',
                name: 'organization_type_id'
            },
            {
                data: 'contact_person_name',
                name: 'contact_person_name'
            },
            {
                data: 'contact_number',
                name: 'contact_number'
            },
            {
                data: 'email',
                name: 'email'
            },
            {
                data: 'service_wards_text',
                name: 'service_wards_text',
                orderable: false,
                searchable: false
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ],
        order: [ [0, 'desc'] ]
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
        var email = $('#email').val();
        var address = $('#address').val();
        var contact_person_name = $('#contact_person_name').val();
        var organization_type_id = $('#organization_type_id').val();
        var status = $('#status').val();
        window.location.href = "{!! route('swm.organizations.export') !!}?searchData=" + searchData +
            "&name=" + encodeURIComponent(name) +
            "&email=" + encodeURIComponent(email) +
            "&address=" + encodeURIComponent(address) +
            "&contact_person_name=" + encodeURIComponent(contact_person_name) +
            "&organization_type_id=" + encodeURIComponent(organization_type_id) +
            "&status=" + encodeURIComponent(status);
    })
});
</script>
@endpush
