<!-- Last Modified Date: 11-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->

<div class="card-body">
<div class="form-group row ">
		{!! Form::label('code',__('Code'),['class' => 'col-sm-3 control-label']) !!}
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
	<div class="form-group row">
		{!! Form::label('cover_type',__('Cover Type'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('cover_type', $cover_type, null, ['class' => 'form-control', 'placeholder' => __('Cover Type')]);!!}
		</div>
	</div>
	<div class="form-group row">
		{!! Form::label('surface_type',__('Surface Type'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('surface_type', $surface_type, null, ['class' => 'form-control', 'placeholder' => __('Surface Type')]);!!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('size',__('Width (mm)'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('size',null,['class' => 'form-control', 'placeholder' => __('Width (mm)'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('length',__('Length (m)'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('length',null,['class' => 'form-control', 'placeholder' => __('Length (m)'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
		</div>
	</div>
	<div class="form-group row ">
		{!! Form::label('treatment_plant_id',__('Treatment Plant'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('treatment_plant_id', $treatmentPlants, null, ['class' => 'form-control', 'placeholder' => __('Treatment Plant')]);!!}
		</div>
	</div>
	
	
</div><!-- /.card-body -->
<div class="card-footer">
	<a href="{{ action('UtilityInfo\DrainController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
	{!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div><!-- /.card-footer -->
