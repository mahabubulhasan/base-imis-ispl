<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	<div class="card-header with-border">
		<h3 class="card-title">{{__('Application ID')}}: {{ $sludgeCollection->application_id }}</h3>
	</div><!-- /.card-header -->
	{!! Form::model($sludgeCollection, ['method' => 'PATCH', 'action' => ['Fsm\SludgeCollectionController@update', $sludgeCollection->id], 'class' => 'form-horizontal']) !!}
		@include('fsm/sludge-collection.partial-form', ['submitButtomText' => 'Update'])
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