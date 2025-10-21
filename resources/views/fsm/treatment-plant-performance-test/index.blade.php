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
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card">
    <div class="card-body">
    {!! Form::model(['method' => 'PATCH', 'action' => ['Fsm\TreatmentplantPerformanceTestController@update'], 'class' => 'form-horizontal' , 'id' => 'editForm']) !!}
    <div class="form-group row">
    <div class="col-sm-3" style="color:grey">
        <small><i class="fa-regular fa-clock"></i> {{ __("Last Updated") }}: {{ $updated}}</small>
    </div>
</div>
<div class="form-group row">
    {!! Form::label('tss_standard', __('TSS Standard (mg/l)'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('tss_standard', old('tss_standard', $data->tss_standard ?? 0), [
            'class' => 'form-control',
            'placeholder' => __('TSS Standard'),
            'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/^(\d*\.?\d*)\.*$/, '$1')"
        ]) !!}
    </div>
</div>

<div class="form-group row">
    {!! Form::label('ecoli_standard', __('ECOLI Standard (CFU/100 mL)'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('ecoli_standard', old('ecoli_standard', $data->ecoli_standard ?? 0), [
            'class' => 'form-control',
            'placeholder' => __('ECOLI Standard (CFU/100 mL)'),
            'oninput' => "this.value = this.value.replace(/[^0-9]/g, '')"
        ]) !!}
    </div>
</div>

<div class="form-group row">
    {!! Form::label('ph_min', __('pH Minimum'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('ph_min', old('ph_min', $data->ph_min ?? 0), [
            'class' => 'form-control',
            'placeholder' => __('pH Minimum'),
            'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')"
        ]) !!}
    </div>
</div>

<div class="form-group row">
    {!! Form::label('ph_max', __('pH Maximum'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('ph_max', old('ph_max', $data->ph_max ?? 0), [
            'class' => 'form-control',
            'placeholder' => __('pH Maximum'),
            'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')"
        ]) !!}
    </div>
</div>

<div class="form-group row">
    {!! Form::label('bod_standard', __('BOD Standard (mg/l)'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::text('bod_standard', old('bod_standard', $data->bod_standard ?? 0), [
            'class' => 'form-control',
            'placeholder' => __('BOD Standard'),
            'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')"
        ]) !!}
    </div>
</div>

@can('Edit Treatment Plant Efficiency Standard')
<div class="card-footer">
        <span id="editButton" class="btn btn-info">{{ __("Edit") }}</span>
        <button type="submit" id="saveButton" class="btn btn-info" style="display: none;">{{ __("Save") }}</button>
    </div><!-- /.box-footer -->
  </div>
  {!! Form::close() !!}
</div>
@endcan

</div><!-- /.box -->
@stop
@push('scripts')
<script>
$(document).ready(function () {
    function toggleReadOnly(readonly) {
        $('input').prop('readonly', readonly);
    }

    toggleReadOnly(true);

    $('#editButton').click(function () {
        $('input').removeAttr('readonly');
        $('#editButton').hide();
        $('#saveButton').show();

        // Hide the "Last Updated" information
        $('.form-group .col-sm-3 small').hide();
    });

    var hasErrors = $('.alert-danger').length > 0;

    if (hasErrors) {
        $('input').removeAttr('readonly');
        $('#editButton').hide();
        $('#saveButton').show();
    } else {
        $('#saveButton').hide();
        $('#editButton').show();
    }
});


</script>

@endpush
