 <!-- Last Modified Date: 18-04-2024
    Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
    @extends('layouts.dashboard')
    @section('title', $page_title)
    @section('content')
    @include('layouts.components.error-list')
    @include('layouts.components.success-alert')
    @include('layouts.components.error-alert')
    <div class="card card-info">
        {!! Form::open(['route' => 'nsd-setting.store', 'method' => 'POST', 'class' => 'form-horizontal']) !!}
            @include('fsm.nsd-setting.partial-form', ['submitButtonText' => 'Save'])
        {!! Form::close() !!}

    </div><!-- /.box -->

    @stop
@push('scripts')
<script>
    $('#editButton').hide();
    $('#saveButton').show();

    $('#saveButton').click(function() {
        $('input').attr('readonly', 'readonly'); 
        $('#saveButton').hide(); 
        $('#editButton').show(); 

        // Optionally, you can re-show the "Last Updated" element here
        $('.col-sm-3 small').show();
    });

    // Check for errors and update buttons accordingly
    var hasErrors = $('.alert-danger').length > 0;

    if (hasErrors) {
        $('input').removeAttr('readonly'); // Make inputs editable
        $('#editButton').hide(); // Hide "Edit" button
        $('#saveButton').show(); // Show "Save" button
    } else {
        $('#saveButton').show(); // Initially show "Save" button
        $('#editButton').hide(); // Hide "Edit" button by default
    }
</script>
@endpush