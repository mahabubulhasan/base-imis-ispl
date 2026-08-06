<!-- // Last Modified: 12-03-2026
// Developed By: Streams Tech Ltd.
// Description: Displays pending applications list with view and delete actions. -->
@extends('layouts.dashboard')
@section('title', __('Pending Application'))

@push('style')
    <style type="text/css">
        .dataTables_filter {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            @if (!empty($createBtnLink) && !empty($createBtnTitle))
                <a href="{{ $createBtnLink }}" class="btn btn-info">{{ $createBtnTitle }}</a>
            @endif

            <a class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse" data-target="#collapseOne"
                aria-expanded="true" aria-controls="collapseOne">
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
                                            <label class="col-md-2 col-form-label">{{ __('Tax Code') }}</label>
                                            <div class="col-md-4">
                                                <input type="text" id="tax_id" class="form-control" placeholder="{{ __('Tax Code') }}">
                                            </div>
                                            <label class="col-md-2 col-form-label">{{ __('Customer Name') }}</label>
                                            <div class="col-md-4">
                                                <input type="text" id="customer_name" class="form-control" placeholder="{{ __('Customer Name') }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-md-2 col-form-label">{{ __('Ward') }}</label>
                                            <div class="col-md-4">
                                                <input type="text" id="ward" class="form-control" placeholder="{{ __('Ward') }}">
                                            </div>
                                            <label class="col-md-2 col-form-label">{{ __('Customer Contact') }}</label>
                                            <div class="col-md-4">
                                                <input type="text" id="customer_contact" class="form-control" placeholder="{{ __('Customer Contact') }}">
                                            </div>
                                        </div>
                                        <div class="card-footer text-right">
                                            <button type="submit" class="btn btn-info">{{ __('Filter') }}</button>
                                            <button id="reset-filter" class="btn btn-info">{{ __('Reset') }}</button>
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
                <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
                    <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Tax Code') }}</th>
                            <th>{{ __('Applicant Name') }}</th>
                            <th>{{ __('Applicant Contact') }}</th>
                            <th>{{ __('Ward') }}</th>
                            <th>{{ __('Address') }}</th>
                            <th>{{ __('Proposed Emptying Date') }}</th>
                            <th>{{ __('Application Date') }}</th>
                            <th>{{ __('Payment Status') }}</th>
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
                stateSave: true,
                scrollCollapse: true,
                ajax: {
                    url: '{!! route('pending-application.get-data') !!}',
                    data: function(d) {
                        d.tax_id = $('#tax_id').val();
                        d.customer_name = $('#customer_name').val();
                        d.ward = $('#ward').val();
                        d.customer_contact = $('#customer_contact').val();
                    },
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'tax_code',
                        name: 'tax_id'
                    },
                    {
                        data: 'applicant_name',
                        name: 'applicant_name'
                    },
                    {
                        data: 'applicant_contact',
                        name: 'applicant_contact'
                    },
                    {
                        data: 'ward',
                        name: 'ward'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    {
                        data: 'proposed_emptying_date',
                        name: 'proposed_emptying_date'
                    },
                    {
                        data: 'application_date',
                        name: 'application_date'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'desc']
                ]
            }).on('draw', function() {
                $('.delete').on('click', function(event) {
                    var form = $(this).closest('form');
                    event.preventDefault();
                    Swal.fire({
                        title: "{{ __('Are you sure?') }}",
                        text: "{{ __('This action cannot be undone.') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{ __('Yes, delete it!') }}",
                        cancelButtonText: "{{ __('Cancel') }}",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    })
                });
            });

            filterDataTable(dataTable);
            resetDataTable(dataTable);
        });
    </script>
@endpush
