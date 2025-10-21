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

        <div class="form-group row required">
		{!! Form::label('project_name',__('Project Name'),['class' => 'col-sm-3 control-label']) !!}

		<div class="col-sm-3">
			{!! Form::text('project_name',null,['class' => 'form-control', 'placeholder' => __('Project Name')]) !!}
		</div>
	</div>

        <div class="form-group row ">
		{!! Form::label('type',__('Type'),['class' => 'col-sm-3 control-label']) !!}

		<div class="col-sm-3">
			{!! Form::select('type', ['Main' => 'Main', 'Secondary' => 'Secondary'], null, ['class' => 'form-control', 'placeholder' => __('Type')]);!!}
		</div>
	</div>

        <div class="form-group row ">
		{!! Form::label('material_type',__('Material Type'),['class' => 'col-sm-3 control-label']) !!}

		<div class="col-sm-3">
			{!! Form::select('material_type', ['HDPE' => 'HDPE', 'GI' => 'GI'], null, ['class' => 'form-control', 'placeholder' => __('Material Type')]);!!}
		</div>
	</div>
  
   <div class="form-group row required">
		{!! Form::label('diameter',__('Diameter (mm)'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('diameter',null,['class' => 'form-control', 'placeholder' => __('Diameter (mm)'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",]) !!}
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
	<a href="{{ action('UtilityInfo\WaterSupplysController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
	{!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div><!-- /.card-footer -->
