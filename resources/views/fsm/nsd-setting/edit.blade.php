<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
    {!! Form::model($nsd, ['route' => ['nsd-setting.update', $nsd->id], 'method' => 'PUT', 'class' => 'form-horizontal']) !!}
        @include('fsm.nsd-setting.partial-form', ['submitButtonText' => 'Update NSD'])
    {!! Form::close() !!}
    
</div><!-- /.box -->

@stop

@push('scripts')

<script>
 document.querySelectorAll('.form-control').forEach(element => {
  element.readOnly = true;

  $('#editButton').click(function() {
            $('input').removeAttr('readonly');
            $('#editButton').hide();
            $('#saveButton').show();

            // Hide "Last Updated" element
            $('.col-sm-3 small').hide();
        });

        // Check for errors and update buttons accordingly
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


