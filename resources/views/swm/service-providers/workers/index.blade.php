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
        @can('Add SW Worker')
        <a href="{{ action('Swm\WorkerController@create') }}" class="btn btn-info">{{ __('Add Worker') }}</a>
        @endcan
        @can('Export SW Workers to CSV')
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
                                    <label for="name" class="col-md-2 col-form-label">{{ __('Worker Name') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="name" placeholder="{{ __('Worker Name') }}" />
                                    </div>
                                    <label for="worker_id_no" class="col-md-2 col-form-label">{{ __('Worker ID') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="worker_id_no" placeholder="{{ __('Worker ID') }}" />
                                    </div>
                                    <label for="mobile" class="col-md-2 col-form-label">{{ __('Mobile') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="mobile" placeholder="{{ __('Mobile') }}" />
                                    </div>
                                    <label for="email" class="col-md-2 col-form-label">{{ __('Email') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="email" placeholder="{{ __('Email') }}" />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="employee_id" class="col-md-2 col-form-label">{{ __('Employee ID') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="employee_id" placeholder="{{ __('Employee ID') }}" />
                                    </div>
                                    <label for="national_id_no" class="col-md-2 col-form-label">{{ __('National ID') }}</label>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control" id="national_id_no" placeholder="{{ __('National ID') }}" />
                                    </div>
                                    <label for="employment_type" class="col-md-2 col-form-label">{{ __('Employment Type') }}</label>
                                    <div class="col-md-2">
                                        <select class="form-control chosen-select" id="employment_type" name="employment_type">
                                            <option value="">{{ __('Employment Type') }}</option>
                                            <option value="permanent">{{ __('Permanent') }}</option>
                                            <option value="daily">{{ __('Daily') }}</option>
                                            <option value="contract">{{ __('Contract') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
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
                                    <label for="work_type_id" class="col-md-2 col-form-label">{{ __('Work Type') }}</label>
                                    <div class="col-md-2">
                                        <select class="form-control chosen-select" id="work_type_id" name="work_type_id">
                                            <option value="">{{ __('Work Type') }}</option>
                                            @foreach($workTypes as $wid => $wname)
                                            <option value="{{ $wid }}">{{ $wname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label for="status" class="col-md-2 col-form-label">{{ __('Status') }}</label>
                                    <div class="col-md-2">
                                        <select class="form-control chosen-select" id="status" name="status">
                                            <option value="">{{ __('Status') }}</option>
                                            <option value="active">{{ __('Active') }}</option>
                                            <option value="inactive">{{ __('Inactive') }}</option>
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
                <th>{{ __('Worker Name') }}</th>
                <th>{{ __('Worker ID') }}</th>
                <th>{{ __('Organization') }}</th>
                <th>{{ __('Work Type') }}</th>
                <th>{{ __('Mobile') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Employee ID') }}</th>
                <th>{{ __('National ID') }}</th>
                <th>{{ __('Employment Type') }}</th>
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
            url: '{!! route("swm.workers.data") !!}',
            data: function(d) {
                d.name = $('#name').val();
                d.worker_id_no = $('#worker_id_no').val();
                d.mobile = $('#mobile').val();
                d.email = $('#email').val();
                d.employee_id = $('#employee_id').val();
                d.national_id_no = $('#national_id_no').val();
                d.employment_type = $('#employment_type').val();
                @if(empty($scopedOrganizationId))
                d.organization_id = $('#organization_id').val();
                @endif
                d.work_type_id = $('#work_type_id').val();
                d.status = $('#status').val();
            }
        },
        columns: [{
                data: 'name',
                name: 'swm.workers.name'
            },
            {
                data: 'worker_id_no',
                name: 'swm.workers.worker_id_no'
            },
            {
                data: 'organization_name',
                name: 'organization_name'
            },
            {
                data: 'work_type_name',
                name: 'work_type_name'
            },
            {
                data: 'mobile',
                name: 'swm.workers.mobile'
            },
            {
                data: 'email',
                name: 'swm.workers.email'
            },
            {
                data: 'employee_id',
                name: 'swm.workers.employee_id'
            },
            {
                data: 'national_id_no',
                name: 'swm.workers.national_id_no'
            },
            {
                data: 'employment_type',
                name: 'swm.workers.employment_type'
            },
            {
                data: 'status',
                name: 'swm.workers.status'
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
        var worker_id_no = $('#worker_id_no').val();
        var mobile = $('#mobile').val();
        var email = $('#email').val();
        var employee_id = $('#employee_id').val();
        var national_id_no = $('#national_id_no').val();
        var employment_type = $('#employment_type').val();
        var status = $('#status').val();
        var organization_id = '';
        @if(empty($scopedOrganizationId))
        organization_id = $('#organization_id').val() || '';
        @endif
        var work_type_id = $('#work_type_id').val();
        window.location.href = "{!! route('swm.workers.export') !!}?searchData=" + searchData +
            "&name=" + encodeURIComponent(name) +
            "&worker_id_no=" + encodeURIComponent(worker_id_no || '') +
            "&mobile=" + encodeURIComponent(mobile) +
            "&email=" + encodeURIComponent(email) +
            "&employee_id=" + encodeURIComponent(employee_id) +
            "&national_id_no=" + encodeURIComponent(national_id_no) +
            "&employment_type=" + encodeURIComponent(employment_type || '') +
            "&status=" + encodeURIComponent(status || '') +
            "&organization_id=" + encodeURIComponent(organization_id || '') +
            "&work_type_id=" + encodeURIComponent(work_type_id || '');
    })
});
</script>
@endpush
