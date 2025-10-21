<!-- Last Modified Date: 19-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($ctpt, ['method' => 'PATCH', 'action' => ['Fsm\CtptController@update', $ctpt], 'class' => 'form-horizontal']) !!}
		@include('fsm/ct-pt.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</><!-- /.card -->
@stop

@push('scripts')

<script>
    $(document).ready(function() {

    $('#type').on('change', function() {
        var selectedValue = $(this).val();
    if(selectedValue){
        let selectedBINText = null;
        let selectedBINValue = null;
        if ($('.alert.alert-danger.alert-dismissible').length == 0) {
               
                localStorage.removeItem("selectedBINText");
                localStorage.removeItem("selectedBINValue");
        }
        else{
            selectedBINText = localStorage.getItem("selectedBINText");
            selectedBINValue = localStorage.getItem("selectedBINValue");
        }
        if('{{$ctpt->type}}' != selectedValue){
            optionHtmlBIN = selectedBINValue 
                    ? `<option value=${selectedBINValue} selected=${selectedBINText}>${selectedBINText}</option>` 
                    : `<option selected=""></option>`;
        }
       else{
        optionHtmlBIN = selectedBINValue 
                ? `<option value=${selectedBINValue} selected=${selectedBINText}>${selectedBINText}</option>` 
                : '<option selected="{{ $ctpt->bin }}">{{ $ctpt->bin }}</option>';
       }

        $('.bin').prepend(optionHtmlBIN).select2({
        ajax: {
            url: "{{ route('building.get-ctpt-house-numbers') }}",
            data: function(params) {
                return {
                    toilet_type: selectedValue,
                    search: params.term,
                    page: params.page || 1
                };
            },
        },
        placeholder: '{{ __('House Number / BIN') }}',
        allowClear: true,
        closeOnSelect: true,
        width: '85%',
        });

        $('.bin').on('change', function() {
        var selectedBINValue = $(this).find('option:selected').attr('value');
        var selectedBINText = $(this).find('option:selected').text();
        localStorage.setItem("selectedBINValue", selectedBINValue);
        localStorage.setItem("selectedBINText", selectedBINText);
        });
    } }).trigger('change');
          
}); 


</script> 
@endpush
