<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
{{--Extend the main layout--}}
@extends('layouts.dashboard')
{{--Add sections for the main layout--}}
@section('title', __('Add Emptying Service Details'))
{{--Add sections for the index layout--}}

{{--Include the layout inside the main content section--}}
@section('content')
<div class="card card-info">
    @include('layouts.components.error-list')
    @include('layouts.components.success-alert')
    @include('layouts.components.error-alert')
    <div class="card-body">
    {!! Form::open(['url' => route('emptying.store'), 'class' => 'form-horizontal','files'=>true]) !!}
        @include('layouts.partial-form',["submitButtonText" =>  __('Save')])
    {!! Form::close() !!}
    </div>
</div>
@endsection

@push('scripts')
    <script>
        function autoFillDetails() {
            $(document).ready(function() {
                if ($("input[name='autofill']:checked").val() === 'on') {
                    $("input[name='applicants_name']").val($("input[name=customer_name]").val());
                    $("#applicant_gender").val($("#customer_gender").val());
                    $("input[name='applicants_contact']").val($("input[name=contact_no]").val());
                    $("input[name='applicants_name']").attr('disabled','disabled');
                    $("#applicant_gender").attr('disabled','disabled');
                    $("input[name='applicants_contact']").attr('disabled','disabled');
                } else {
                    $("input[name='applicants_name']").val('');
                    $("#applicant_gender").val('');
                    $("input[name='applicants_contact']").val('');
                    $("input[name='applicants_name']").removeAttr('disabled');
                    $("#applicant_gender").removeAttr('disabled');
                    $("input[name='applicants_contact']").removeAttr('disabled');
                }
            });
        }

        function emptyAutoFields() {
            $('#road_code').val('');
            $('#containment_code').val('');
            $('#ward').val('');
            $('#customer_name').val('');
            $('#customer_gender').val('');
            $('#contact_no').val('');
            $("input[name='applicants_name']").val('');
            $("#applicant_gender").val('');
            $("input[name='applicants_contact']").val('');
            $("input[name='applicants_name']").removeAttr('disabled');
            $("#applicant_gender").removeAttr('disabled');
            $("input[name='applicants_contact']").removeAttr('disabled');
            $("input[name='autofill']").prop('checked', false);
        }

        function onAddressChange() {
                emptyAutoFields();
                if($('#address').find(":selected").text() === 'Address Not Found'){
                    $('#building-if-address').hide();
                    $("#building-if-address :input").each(function () {
                        $(this).attr("disabled",true);
                    });
                    $('#building-if-not-address').show();
                    $("#building-if-not-address :input").each(function () {
                        $(this).attr("disabled",false);
                    });
                    $("input[type='submit']").removeAttr('disabled');
                }else {
                    $('#building-if-not-address').hide();
                    $("#building-if-not-address :input").each(function () {
                        $(this).attr("disabled",true);
                    });
                    $('#building-if-address').show();
                    $("#building-if-address :input").each(function () {
                        $(this).attr("disabled",false);
                    });

                    $.ajax(
                        {
                            url: "{{ route('application.get-building-details') }}",
                            data: {
                                "address" : $('#address').val()
                            },
                            success: function (res) {
                                if (res.status === true){
                                    let containments = '';
                                    res.containments.forEach(function (containment) {
                                        containments+=containment.id + ' ';
                                    })
                                    $('#customer_name').val(res.customer_name);
                                    $('#customer_gender').val(res.customer_gender);
                                    $('#contact_no').val(res.customer_contact);
                                    $('#road_code').val(res.road);
                                    $('#containment_code').val(containments);
                                    $('#ward').val(res.ward);
                                    $("input[type='submit']").removeAttr('disabled');
                                } else if (res.status === false) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: "There is an ongoing application for this address!",
                                    });
                                    emptyAutoFields();
                                    $("input[type='submit']").attr('disabled','disabled');
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: "Error!",
                                    });
                                    emptyAutoFields();
                                }

                            },
                            error: function (err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: err.responseJSON.error,
                                });
                                emptyAutoFields();
                                $("input[type='submit']").attr('disabled','disabled');
                            }
                        }
                    )
                }
        }

        $(document).ready(function() {
            $('#proposed_emptying_date').daterangepicker({
                minDate: moment(),
                singleDatePicker: true,
                autoUpdateInput: false,
            });
            $('#proposed_emptying_date').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY'));
            });

            $('#proposed_emptying_date').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
            $('#address').prepend('<option selected=""></option>').append('<option value="-">Address Not Found</option>').select2({
                placeholder: 'Address',
                matcher: function(params, data) {
                    if (data.id === "-") {
                        return data;
                    } else {
                        return $.fn.select2.defaults.defaults.matcher.apply(this, arguments);
                    }
                },
                closeOnSelect: true,
                width: '100%'
            });
            if ('{{ old('address') }}'!==''){
                $('#address').select2().val('{{ old('address') }}').trigger('change');
                onAddressChange();
            }

            $('#address').on('change',onAddressChange)
        });

            // code that checks house image greater than 5MB from frontend
            $('#house_image').on('change', function() {
                validateFileSize(document.querySelector('#house_image'),'fileSizeHintImg','5');
            });
            
            $('#receipt_image').on('change', function() {
                validateFileSize(document.querySelector('#receipt_image'),'fileSizeRintImg','5');
            });
    </script>
@endpush

