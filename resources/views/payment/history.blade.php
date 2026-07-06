<!-- // Last Modified: 2026-07-06
// Developed By: Streams Tech Ltd.
// Description: Displays payment history with view and download receipt actions. -->
@extends('layouts.dashboard')
@section('title', __('Payment History'))

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
            <h3 class="card-title">{{ __('Payment History') }}</h3>
            <a class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse" data-target="#collapseOne"
                aria-expanded="false" aria-controls="collapseOne">
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
                                            <label class="col-md-2 col-form-label">{{ __('Transaction ID') }}</label>
                                            <div class="col-md-4">
                                                <input type="text" id="transaction_id" class="form-control" placeholder="{{ __('Transaction ID') }}">
                                            </div>
                                            <label class="col-md-2 col-form-label">{{ __('Applicant Name') }}</label>
                                            <div class="col-md-4">
                                                <input type="text" id="applicant_name" class="form-control" placeholder="{{ __('Applicant Name') }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-md-2 col-form-label">{{ __('Receipt No') }}</label>
                                            <div class="col-md-4">
                                                <input type="text" id="receipt_no" class="form-control" placeholder="{{ __('Receipt No') }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-md-2 col-form-label">{{ __('Payment Date From') }}</label>
                                            <div class="col-md-4">
                                                <input type="date" id="payment_date_from" class="form-control">
                                            </div>
                                            <label class="col-md-2 col-form-label">{{ __('Payment Date To') }}</label>
                                            <div class="col-md-4">
                                                <input type="date" id="payment_date_to" class="form-control">
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
                            <th>{{ __('Transaction ID') }}</th>
                            <th>{{ __('Applicant Name') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Payment Date') }}</th>
                            <th>{{ __('Applicant Contact') }}</th>
                            <th>{{ __('Receipt No') }}</th>
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
                    url: '{!! route('payment.history.data') !!}',
                    data: function(d) {
                        d.transaction_id = $('#transaction_id').val();
                        d.applicant_name = $('#applicant_name').val();
                        d.receipt_no = $('#receipt_no').val();
                        d.payment_date_from = $('#payment_date_from').val();
                        d.payment_date_to = $('#payment_date_to').val();
                    },
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'transaction_id',
                        name: 'transaction_id'
                    },
                    {
                        data: 'applicant_name',
                        name: 'applicant_name'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'applicant_contact',
                        name: 'applicant_contact'
                    },
                    {
                        data: 'receipt_no',
                        name: 'receipt_no'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [5, 'desc']
                ]
            });

            // Filter form submission
            $('#filter-form').on('submit', function(event) {
                event.preventDefault();
                dataTable.draw();
            });

            // Reset filter button
            $('#reset-filter').on('click', function(event) {
                event.preventDefault();
                $('#transaction_id').val('');
                $('#applicant_name').val('');
                $('#receipt_no').val('');
                $('#payment_date_from').val('');
                $('#payment_date_to').val('');
                dataTable.draw();
            });
        });
    </script>
@endpush
