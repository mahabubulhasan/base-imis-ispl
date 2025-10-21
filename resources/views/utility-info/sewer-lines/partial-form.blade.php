<!-- Last Modified Date: 11-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->
<div class="card-body">
<div class="form-group row ">
       {!! Form::label('code', __('Code'), ['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('code',null,['class' => 'form-control', 'disabled' => 'true', 'placeholder' => __('Code')]) !!}
		</div>
	</div>
	<div class="form-group row ">
		{!! Form::label('road_code',__('Road Code'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('road_code',null,['class' => 'form-control', 'disabled' => 'true', 'placeholder' => __('Road Code')]) !!}
		</div>
	</div>	
	<div class="form-group row required">
		{!! Form::label('location',__('Location'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('location', ['' => __('Location'),'middle' => 'middle','side' => 'side'], null, ['class' => 'form-control']) !!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('length',__('Length (m)'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('length',null,['class' => 'form-control', 'placeholder' => __('Length (m)'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
		</div>
	</div>
 <div class="form-group row ">
		{!! Form::label('diameter',__('Diameter (mm)'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('diameter',null,['class' => 'form-control', 'placeholder' => __('Diameter (mm)'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
		</div>
	</div>
  <div class="form-group row ">
		{!! Form::label('treatment_plant_id',__('Treatment Plant'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('treatment_plant_id',$treatdrp,null,['class' => 'form-control', 'placeholder' => __('Treatment Plant')]) !!}
		</div>
	</div>

</div><!-- /.card-body -->
<div class="card-footer">
	<a href="{{ action('UtilityInfo\SewerLineController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
	{!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div><!-- /.card-footer -->
