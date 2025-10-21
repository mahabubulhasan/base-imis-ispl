<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.layers')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::open(['url' => 'fsm/sludge-collection', 'class' => 'form-horizontal']) !!}
		@include('fsm/sludge-collection.partial-form', ['submitButtomText' => __('Save')])
	{!! Form::close() !!}
</div><!-- /.card -->
@stop
@push('scripts')
<script type="text/javascript">
  $(document).ready(function(){

             // Get today's date in YYYY-MM-DD format
            const today = new Date().toISOString().split('T')[0];
            
            // Set the max attribute to today's date
            document.getElementById('sludge_collection_date').setAttribute('min', today);

     });
</script>
@endpush