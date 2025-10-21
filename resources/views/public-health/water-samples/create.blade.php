
<!-- Last Modified Date: 07-05-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.layers')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::open(['url' => 'publichealth/water-samples', 'class' => 'form-horizontal']) !!}
		@include('public-health/water-samples.partial-form', ['submitButtomText' => __('Save')])
	{!! Form::close() !!}
</div><!-- /.card -->
@stop
@push('scripts')
    <script>
$(document).ready(function() {
      // Get today's date in YYYY-MM-DD format
      const today = new Date().toISOString().split('T')[0];

            document.getElementById('sample_date').setAttribute('max', today);
});
</script>
@endpush