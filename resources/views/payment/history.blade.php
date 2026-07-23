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

    <!-- Transaction Log Modal -->
    <div class="modal fade" id="transactionLogModal" tabindex="-1" role="dialog" aria-labelledby="transactionLogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionLogModalLabel">{{ __('Transaction Log') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><strong>{{ __('Transaction ID') }}</strong></label>
                        <p id="log-transaction-id"></p>
                    </div>
                    <div class="form-group">
                        <label><strong>{{ __('Transaction Date') }}</strong></label>
                        <p id="log-transaction-date"></p>
                    </div>
                    <div class="form-group">
                        <label>
                            <strong>{{ __('Response') }}</strong>
                            <button type="button" class="btn btn-sm btn-outline-secondary float-right" id="copy-json-btn" title="{{ __('Copy to Clipboard') }}">
                                <i class="fas fa-copy"></i> {{ __('Copy') }}
                            </button>
                        </label>
                        <pre id="log-response" style="background-color: #f5f5f5; padding: 10px; border-radius: 4px; max-height: 400px; overflow-y: auto;"></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                </div>
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

            // Handle view transaction log button click
            $(document).on('click', '.view-log-btn', function(e) {
                e.preventDefault();

                var transactionId = $(this).data('transaction-id');

                // Disable button and show loading state
                var $btn = $(this);
                $btn.prop('disabled', true);
                var originalHtml = $btn.html();
                $btn.html('<i class="fas fa-spinner fa-spin"></i>');

                // Send AJAX request
                $.ajax({
                    url: '{{ route("payment.transaction-log") }}',
                    type: 'GET',
                    data: {
                        transaction_id: transactionId
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            // Populate modal with data
                            $('#log-transaction-id').text(response.data.transaction_id);
                            $('#log-transaction-date').text(response.data.transaction_date);

                            // Pretty print JSON
                            try {
                                var jsonObject = JSON.parse(response.data.response);
                                var prettyJson = JSON.stringify(jsonObject, null, 2);
                                $('#log-response').text(prettyJson);
                            } catch (error) {
                                // If response is already formatted or not JSON
                                $('#log-response').text(response.data.response);
                            }

                            // Store the JSON for copy functionality
                            $('#copy-json-btn').data('json-data', response.data.response);

                            // Show modal
                            $('#transactionLogModal').modal('show');
                        } else {
                            alert('{{ __("Error") }}: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        var message = '{{ __("Failed to fetch transaction log") }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        alert('{{ __("Error") }}: ' + message);
                    },
                    complete: function() {
                        // Re-enable button and restore original state
                        $btn.prop('disabled', false);
                        $btn.html(originalHtml);
                    }
                });
            });

            // Handle copy JSON button click
            $(document).on('click', '#copy-json-btn', function(e) {
                e.preventDefault();

                var jsonData = $('#log-response').text();

                if (!jsonData) {
                    alert('{{ __("No data to copy") }}');
                    return;
                }

                // Copy to clipboard
                navigator.clipboard.writeText(jsonData).then(function() {
                    // Show success toast
                    var alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">' +
                        '<strong>{{ __("Success!") }}</strong> {{ __("JSON copied to clipboard") }}' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span>' +
                        '</button>' +
                        '</div>';

                    $('body').append(alertHtml);

                    // Remove alert after 3 seconds
                    setTimeout(function() {
                        $('.alert-success').fadeOut(function() {
                            $(this).remove();
                        });
                    }, 3000);

                    // Change button text temporarily
                    var $btn = $('#copy-json-btn');
                    var originalHtml = $btn.html();
                    $btn.html('<i class="fas fa-check"></i> {{ __("Copied") }}');

                    setTimeout(function() {
                        $btn.html(originalHtml);
                    }, 2000);
                }).catch(function(err) {
                    alert('{{ __("Failed to copy to clipboard") }}');
                });
            });

            // Handle refresh status button click
            $(document).on('click', '.refresh-status-btn', function(e) {
                e.preventDefault();

                var $btn = $(this);
                var $form = $btn.closest('.refresh-status-form');
                var transactionId = $btn.data('transaction-id');

                // Disable button and show loading state
                $btn.prop('disabled', true);
                var originalHtml = $btn.html();
                $btn.html('<i class="fas fa-spinner fa-spin"></i> {{ __("Checking...") }}');

                // Send AJAX request
                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update the status badge
                            var statusBadge = $('#status-badge-' + transactionId);
                            statusBadge.removeClass(function(index, css) {
                                return (css.match(/\bbadge-\S+/g) || []).join(' ');
                            });
                            statusBadge.addClass('badge-' + response.badge_class);
                            statusBadge.text(response.status);

                            // Show success message
                            var alertHtml = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                '<strong>{{ __("Success!") }}</strong> ' + response.message +
                                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                                '<span aria-hidden="true">&times;</span>' +
                                '</button>' +
                                '</div>';

                            // Insert alert at top of card
                            $('.card:first').prepend(alertHtml);

                            // Remove alert after 5 seconds
                            setTimeout(function() {
                                $('.alert-success').fadeOut(function() {
                                    $(this).remove();
                                });
                            }, 5000);

                            // Refresh the datatable after 2 seconds
                            setTimeout(function() {
                                dataTable.draw(false);
                            }, 2000);
                        } else {
                            alert('{{ __("Error") }}: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        var message = '{{ __("Failed to check payment status") }}';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        alert('{{ __("Error") }}: ' + message);
                    },
                    complete: function() {
                        // Re-enable button and restore original state
                        $btn.prop('disabled', false);
                        $btn.html(originalHtml);
                    }
                });
            });
        });
    </script>
@endpush
