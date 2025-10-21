<div class="card-body">
	<div class="form-group row required">
		{!! Form::label('toilet_id',__('Toilet Name'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			@if(!isset($users))
				{!! Form::select('toilet_id', $name, 'Select Toilet', ['class' => 'form-control', 'placeholder' => __('Toilet Name')]) !!}
			@else
				{!! Form::text('toilet_id',$name,['class' => 'form-control','readonly']) !!}
			@endif
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('date',__('Date'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			@if(!isset($users))
				{!! Form::date('date', null,['class' => 'form-control', 'id'=>'date_create' ,'placeholder' => __('Date'),'onclick' => 'this.showPicker();' ]) !!}
			@else
				{!! Form::date('date',$users->date,['class' => 'form-control','readonly']) !!}
			@endif
		</div>
	</div>

	<div class="form-group row required">
		{!! Form::label('no_male_user',__('No. of Male Users (daily)'),['class' => 'col-sm-3 calculate control-label']) !!}
		<div class="col-sm-3">
            {!! Form::number('no_male_user',null, ['class' => 'calculate form-control', 'placeholder' => __('No. of Male Users (daily)'),'oninput' => "this.value = this.value.replace(/[^0-9]/g, '')"]) !!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('no_female_user',__('No. of Female Users (daily)'),['class' => 'col-sm-3 calculate control-label']) !!}
		<div class="col-sm-3">
			{!! Form::number('no_female_user', null,['class' => 'calculate form-control', 'placeholder' => __('No. of Female Users (daily)'),'oninput' => "this.value = this.value.replace(/[^0-9]/g, '')"]) !!}
		</div>
	</div>



</div><!-- /.box-body -->
<div class="card-footer">
	<a href="{{ action('Fsm\CtptUserController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
	{!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div><!-- /.box-footer -->

@push('scripts')
<script>
$(function() {
	   
	   const today = new Date().toISOString().split('T')[0];
            
            // Set the max attribute to today's date
            document.getElementById('date_create').setAttribute('max', today);

    $("input.calculate").keyup(function(){
        $("#total_user").val(parseInt($("#no_male_user").val())+parseInt($("#no_female_user").val()));
    });
});
</script>
@endpush
