<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    <div class="form-horizontal">
{!! Form::open(['url' => 'fsm/feedback', 'files' => true, 'class' => 'form-horizontal']) !!}
        <div class="card-body">
            <div class="form-group row">
                {!! Form::label('application_id',__('Application ID'),['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    
                    {!! Form::text('application_id',$application->id,['class' => 'form-control','readonly' => true]) !!}
                </div>
            </div>
            <div class="form-group row required">
                {!! Form::label('customer_name', __('Applicant Name'),['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('customer_name',$application->applicant_name,['class' => 'form-control','readonly' => true]) !!}
                </div>
            </div>
            <div class="form-group row required">
                {!! Form::label('customer_gender',__('Applicant Gender'),['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('customer_gender',$application->applicant_gender,['class' => 'form-control','placeholder'=>'Select Gender','readonly' => true]) !!}
                </div>
            </div>
            <div class="form-group row required">
                {!! Form::label('customer_number',__('Applicant Contact Number'),['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::text('customer_number',$application->applicant_contact,['class' => 'form-control','readonly' => true]) !!}
                </div>
            </div>
            <div class="form-group row required">
                <label class="col-sm-3 control-label" for="fsm_service_quality">{{__('Are you satisfied with the Service Quality?')}}</label>
                <div class="col-sm-6">
                    <label class="radio-inline">
                        <input value="1" name="fsm_service_quality" type="radio" id="fsm_service_quality"> {{__('Yes')}}
                    </label>
                    <label class="radio-inline">
                        <input value="0" name="fsm_service_quality" type="radio" id="fsm_service_quality"> {{__('No')}}
                    </label>
                </div>
            </div>
            <div class="form-group row required">
                <label class="col-sm-3 control-label" for="wear_ppe">{{__('Did the sanitation workers wear PPE during desludging?')}}</label>
                <div class="col-sm-6">
                    <label class="radio-inline">
                        <input value="1" name="wear_ppe" type="radio" id="wear_ppe"> {{__('Yes')}}
                    </label>
                    <label class="radio-inline">
                        <input value="0" name="wear_ppe" type="radio" id="wear_ppe"> {{__('No')}}
                    </label>
                </div>
            </div>

          
            <div class="form-group row">
                {!! Form::label('comments',__('Comments'),['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-6">
                    {!! Form::textarea('comments', null,['class' => 'form-control']) !!}
                </div>
            </div>
            
            
            
            
            
            
        </div><!-- /.card-body -->
        
        <div class="card-footer">
    <a href="{{ action('Fsm\ApplicationController@index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
     {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div>
        {!! Form::close() !!}
    </div>
</div><!-- /.card -->
@stop
@push('scripts')
<script>
$(function() {
   /* $('#application_date, #payment_date').datepicker({
        format: "yyyy-mm-dd"
    });

    $('#service_fees').on("keyup change", function(){
        var fees = Number($(this).val());
        var vat = Number((fees * 0.15).toFixed(2));
        var total = fees + vat;
        $('#vat').val(vat);
        $('#total_amount').val(total);
    });*/
    //    $('.date').datepicker({  

    //    format: 'yyyy-mm-dd',
    //    todayHighlight: true

    //  }); 
    // //    $('.timepicker').datetimepicker({
    // //     format: 'hh:mm A'
    // // });

    // $('.chosen-select').chosen();
    // $('.date').focus(function(){
    //     $(this).blur();
    // });
});
</script>
@endpush
