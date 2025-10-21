<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->
<div class="card-body">
<div class="form-group row ">
		{!! Form::label('code',__('Code'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('code',null,['class' => 'form-control', 'disabled' => 'true', 'placeholder' => __('Code')]) !!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('name',__('Road Name'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('name',null,['class' => 'form-control', 'placeholder' => __('Road Name')]) !!}
		</div>
	</div>

        <div class="form-group row ">
		{!! Form::label('hierarchy',__('Hierarchy'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('hierarchy', $roadHierarchy, null, ['class' => 'form-control', 'placeholder' => __('Hierarchy')]);!!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('right_of_way',__('Right of Way (m)'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('right_of_way',null,['class' => 'form-control', 'placeholder' => __('Right of Way (m)'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('carrying_width',__('Carrying Width (m)'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('carrying_width',null,['class' => 'form-control', 'placeholder' => __('Carrying Width (m)'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
		</div>
	</div>
	<div class="form-group row ">
		{!! Form::label('surface_type',__('Surface Type'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('surface_type', $roadSurfaceTypes, null, ['class' => 'form-control', 'placeholder' => __('Surface Type')]);!!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('length',__('Length (m)'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('length',null,['class' => 'form-control', 'placeholder' => __('Length (m)'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
		</div>
	</div>

</div><!-- /.card-body -->
<div class="card-footer">
	<a href="{{ action('UtilityInfo\RoadlineController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
	{!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div><!-- /.card-footer -->
