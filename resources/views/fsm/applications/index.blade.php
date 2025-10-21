<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', __('Application'))
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
            @if (!empty($exportBtnLink))
                <a href="{{ $exportBtnLink }}" class="btn btn-info" id="export" onclick="exportToCsv(event)" >{{ __('Export to CSV') }}</a>
            @endif
            <a class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse"
                data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                {{ __('Show Filter') }}
            </a>
            @if (!empty($reportBtnLink))
                <a class="btn btn-info" data-toggle="collapse" data-target="#collapseFilterPdf" aria-expanded="false"
                    aria-controls="collapseFilterPdf">{{ __('Generate Report') }}</a>
                <div class="card-body">
                    <div class="col-12">
                        <div id="collapseFilterPdf" class="accordion-collapse collapse" aria-labelledby="headingOne"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <div class="form-group row required">
                                    <label for="bin_text" class="control-label col-md-2">{{ __('Month') }}</label>
                                    <div class="col-md-2">
                                        <select class="form-control row" id="month_select" name="month"
                                            <?php if (!empty($application_months)) {
                                                echo 'disabled';
                                            } ?>>
                                            <?php
                                        foreach($application_months as $unique)
                                        {

                                        ?> <option value="{{ $unique->date1 }}">
                                                {{ date('F', mktime(0, 0, 0, $unique->date1, 10)) }}</option>
                                            <?php  }
                                                ?>
                                        </select>
                                    </div>
                                    <label for="bin_text" class="control-label col-md-2">{{ __('Year') }}</label>
                                    <div class="col-md-2">
                                        <select class="form-control row" id="year_select" name="year"
                                            <?php if (!empty($application_years)) {
                                                echo 'disabled';
                                            } ?>>
                                            <?php

                                                foreach($application_years as $unique) {
                                                    ?> <option value="{{ $unique->date1 }}">
                                                {{ $unique->date1 }}</option>
                                            <?php }
                                            ?>
                                        </select>
                                    </div>
                                    <a class="btn btn-info pdf" id="pdf">{{ __('Export to PDF') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div><!-- /.box-header -->
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="accordion" id="accordionExample">
                        <div class="accordion-item">
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                                data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    <form class="form-horizontal" id="filter-form">
                                        {{-- A Layout for Filter --}}
                                        @foreach ($filterFormFields as $formFieldGroup)
                                            <div class="form-group row">
                                                @foreach ($formFieldGroup as $formField)
                                                    {!! Form::label($formField->labelFor, $formField->label, ['class' => $formField->labelClass]) !!}
                                                    <div class="col-md-2">
                                                        @if ($formField->inputType === 'text')
                                                            {!! Form::text($formField->inputId, $formField->inputValue, [
                                                                'class' => $formField->inputClass,
                                                                'placeholder' => $formField->placeholder,
                                                                'autocomplete' => $formField->autoComplete,
                                                            ]) !!}
                                                        @endif
                                                        @if ($formField->inputType === 'date')
                                                            {!! Form::date($formField->inputId, $formField->inputValue, [
                                                                'class' => $formField->inputClass,
                                                                'placeholder' => $formField->placeholder,
                                                                'autocomplete' => $formField->autoComplete,'onclick' => 'this.showPicker();'
                                                            ]) !!}
                                                        @endif
                                                        @if ($formField->inputType === 'number')
                                                            {!! Form::number($formField->inputId, $formField->inputValue, [
                                                                'class' => $formField->inputClass,
                                                                'placeholder' => $formField->placeholder,
                                                            ]) !!}
                                                        @endif
                                                        @if ($formField->inputType === 'select')
                                                            {!! Form::select($formField->inputId, $formField->selectValues, $formField->selectedValue, [
                                                                'class' => $formField->inputClass,
                                                                'placeholder' => $formField->placeholder,
                                                            ]) !!}
                                                        @endif
                                                        @if ($formField->inputType === 'label')
                                                            {!! Form::label($formField->inputId, $formField->labelValue, ['class' => $formField->inputClass]) !!}
                                                        @endif
                                                        @if ($formField->inputType === 'multiple-select')
                                                            {!! Form::select($formField->inputId, $formField->selectValues, $formField->selectedValue, [
                                                                'class' => $formField->inputClass,
                                                                'disabled' => $formField->disabled,
                                                                'autocomplete' => $formField->autoComplete,
                                                            ]) !!}
                                                            @push('scripts')
                                                                <script>
                                                                    $(document).ready(function() {
                                                                        $('#{{ $formField->inputId }}').prepend(
                                                                                '<option selected=""></option>').append(
                                                                                '<option value="-1">Address Not Found</option>')
                                                                            .select2({
                                                                                placeholder: '{{ $formField->placeholder }}',
                                                                                matcher: function(params, data) {
                                                                                    if (data.id === "-1") {
                                                                                        return data;
                                                                                    } else {
                                                                                        return $.fn.select2.defaults.defaults
                                                                                            .matcher.apply(this, arguments);
                                                                                    }
                                                                                },
                                                                                closeOnSelect: true,
                                                                                width: 'select'
                                                                            });
                                                                    });
                                                                </script>
                                                            @endpush
                                                        @endif

                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach

                                        <div class="card-footer t text-right">
                                            <button type="submit" class="btn btn-info">{{ __('Filter') }}</button>
                                            <button id="reset-filter" class="btn btn-info">{{ __('Reset') }}</button>
                                        </div>
                                    </form>
                                </div>
                                <!--- accordion body!-->
                            </div>
                            <!--- collapseOne!-->
                        </div>
                        <!--- accordion item!-->
                    </div>
                    <!--- accordion !-->
                </div>
            </div>
            <!--- row !-->
        </div>
        <!--- card body !-->

        <div class="card-body">
            <div style="overflow: auto; width: 100%;">
                <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
                    <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('BIN') }}</th>
                            <th>{{ __('House Number') }}</th>
                            <th>{{ __('Containment ID') }}</th>
                            <th>{{ __('Application Date') }}</th>
                            <th>{{ __('Proposed Emptying Date') }}</th>
                            <th>{{ __('Street Code') }}</th>
                            <th>{{ __('Emptying Status') }}</th>
                            <th>{{ __('Sludge Collection Status') }}</th>
                            <th>{{ __('Feedback Status') }}</th>
                            <th>{{ __('Owner Name') }}</th>
                            <th>{{ __('Ward Number') }}</th>
                            <th>{{ __('Contact') }}</th>
                            <th>{{ __('Service Provider Name') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div><!-- /.box-body -->
    </div><!-- /.box -->
@stop

@push('scripts')
    <script>
        $(document).ready(function () {
    // Get the authenticated user's service_provider_id
    var serviceProviderId = {{ Auth::user()->service_provider_id ?? 'null' }}; // Use null if undefined
    var url = serviceProviderId
        ? '{!! url("fsm/service-provider") !!}/' + serviceProviderId
        : '{!! url("fsm/service-provider") !!}/0';


    // Send AJAX request to fetch data and populate the select element
    $.ajax({
        url: url,
        method: 'GET',
        success: function (response) {
            // Clear existing options in the select dropdown
            $('#service_provider_id').empty();

            // Add a default "Select" option
            $('#service_provider_id').append('<option value="">{{ __("Service Provider Name") }}</option>');

            // Append options to the select dropdown
            $.each(response, function (id, name) {
                $('#service_provider_id').append('<option value="' + id + '">' + name + '</option>');
            });
        },
        error: function (error) {
            console.error('Error fetching service provider data:', error);
        }
    });
});

        @if (!empty($reportBtnLink))
            var yearSelect = document.getElementById("year_select");
            var monthSelect = document.getElementById("month_select");
            var pdfSelect = document.getElementById("pdf");
            <?php echo !empty($application_months) ? 'monthSelect.disabled = false;' : 'monthSelect.disabled = true;'; ?>
            <?php echo !empty($application_years) ? 'yearSelect.disabled = false;' : 'yearSelect.disabled = true;'; ?>
        @endif

        $(function() {
            var dataTable = $('#data-table').DataTable({
                bFilter: false,
                processing: true,
                serverSide: true,
                stateSave: true,
                scrollCollapse: true,
                ajax: {
                    url: '{!! route('application.get-data') !!}',
                    data: function(d) {
                        d.bin = $('#bin').val();
                        d.house_address = $('#house_address').val();
                        d.customer_name = $('#customer_name').val();
                        d.ward = $('#ward').val();
                        d.application_id = $('#application_id').val();
                        d.emptying_status = $('#emptying_status').val();
                        d.sludge_collection_status = $('#sludge_collection_status').val();
                        d.feedback_status = $('#feedback_status').val();
                        d.road_code = $('#road_code').val();
                        d.proposed_emptying_date = $('#proposed_emptying_date').val();
                        d.service_provider_id = $('#service_provider_id').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    },
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'bin',
                        name: 'bin'
                    },
                    {
                        data: 'house_address',
                        name: 'house_address'
                    },
                    {
                        data: 'containment_id',
                        name: 'containment_id',
                    },
                    {
                        data: 'application_date',
                        name: 'application_date',
                        render: function(data) {
                            return moment(data).format("dddd, MMMM Do YYYY");
                        }
                    },
                    {
                        data: 'proposed_emptying_date',
                        name: 'proposed_emptying_date',
                        render: function(data) {
                            return moment(data).format("dddd, MMMM Do YYYY");
                        }
                    },
                    {
                        data: 'road_code',
                        name: 'road_code'
                    },
                
                    {
                        data: 'emptying_status',
                        name: 'emptying_status'
                    },
                    {
                        data: 'sludge_collection_status',
                        name: 'sludge_collection_status'
                    },
                    {
                        data: 'feedback_status',
                        name: 'feedback_status'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'ward',
                        name: 'ward'
                    },
                    {
                        data: 'customer_contact',
                        name: 'customer_contact'
                    },
                    {
                        data: 'service_provider_id',
                        name: 'service_provider_id'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [0, 'desc']
                ]
            }).on('draw', function() {
                $('.delete').on('click', function(e) {
                    var form = $(this).closest("form");
                    event.preventDefault();
                    Swal.fire({
                    title: "{{__('Are you sure?')}}",
                    text: "{!! __('You won\'t be able to revert this!') !!}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "{{ __('Yes, delete it!') }}",
                    cancelButtonText: '{{ __('Cancel') }}',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    })

                });
            });
            filterDataTable(dataTable);
            resetDataTable(dataTable);
            $('#filter-form').on('submit', function(e) {
                var date_from = $('#date_from').val();
                var date_to = $('#date_to').val();
             

                if ((date_from !== '') && (date_to === '')) {

                    Swal.fire({
                        title: '{{ __('Date To is Required') }}',
                        text: "{{ __('Please Select Date To!') }}",
                        icon: 'warning',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{ __('Close') }}"

                    })

                    return false;
                }
                if ((date_from === '') && (date_to !== '')) {

                    Swal.fire({
                        title:'{{ __('Date To is Required') }}',
                        text: "{{ __('Please Select Date From!') }}",
                        icon: 'warning',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{ __('Close') }}"

                    })

                    return false;
                }

                if (date_from !== '' && date_to !== '' && date_to <= date_from) {
                    Swal.fire({
                        title: "{{ __('Invalid Date Range') }}",
                        text:"{{ __('Date To cannot be Before Date From!') }}" ,
                        icon: 'warning',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "{{ __('Close') }}"
                    });

                    return false;
                }


                e.preventDefault();
                dataTable.draw();
                treatment_plant_id = $('#treatment_plant_id').val();
                date_from = $('#date_from').val();
                date_to = $('#date_to').val();
                application_id = $('#application_id').val();
                servprov = $('#servprov').val();
                 house_address = $('#house_address').val();
            });


            setTimeout(function() {
                localStorage.clear();
            }, 60 * 60 * 1000); ///for 1 hour
            
            $('#road_code').prepend('<option selected=""></option>').select2({
                ajax: {
                    url: "{{ route('roadlines.get-road-names') }}",
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                },
                placeholder:'{{ __('Street Name / Street Code') }}',
                allowClear: true,
                closeOnSelect: true,
                width: '100%'
            });


            $('[id="pdf"]').click(function(e) {
                <?php if(empty($application_months) && empty($application_years)) { ?>
                return false;
                <?php }  else { ?>
                // e.preventDefault();
                if (localStorage.getItem('year_select') != null && localStorage.getItem('month_select') !=
                    null) {
                    year_sel = localStorage.getItem('year_select');
                    month_sel = localStorage.getItem('month_select');
                } else {
                    year_sel = $('#year_select').val();
                    month_sel = $('#month_select').val();
                }
                const url = `application/pdf/${year_sel}/${month_sel}/monthly-report`;
                window.open(url, "Monthly Report");
                <?php } ?>
            })
            $('.date, #date_from, #date_to, #proposed_emptying_date').focus(function() {
                $(this).blur();
            });

        });
    </script>
@endpush
