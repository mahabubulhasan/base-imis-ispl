<div class="card-body">
	@if(empty($info))
         <div class="form-group row required" id="treatment_plant">
                {!! Form::label('treatment_plant_id','Treatment Plant',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::select('treatment_plant_id', $treatmentPlants, null, ['class' => 'form-control ', 'placeholder' => '--- Choose treatment plant ---']) !!}
                </div>
        </div>
	<div class="form-group row required">
		{!! Form::label('year','Year',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
                {!! Form::text('year',null, ['class' => 'form-control', 'placeholder' => 'Enter year']) !!}
		</div>
	</div>
	@else
	<div class="form-group row required" id="treatment_plant">
                {!! Form::label('treatment_plant_id','Treatment Plant',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::select('treatment_plant_id', $treatmentPlants, null, ['class' => 'form-control ', 'placeholder' => '--- Choose treatment plant ---','readonly' => 'readonly']) !!}
                </div>
        </div>
	<div class="form-group row required">
		{!! Form::label('year','Year',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
                {!! Form::text('year',null, ['class' => 'form-control', 'placeholder' => 'Enter year', 'readonly'=>'readonly']) !!}
		</div>
	</div>
	@endif
	<div class="form-group row required">
		{!! Form::label('tp_effectiveness','% of Effectiveness of FS/WW treatment in meeting prescribed discharge standards for water and biosolids',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
                {!! Form::text('tp_effectiveness',null, ['class' => 'form-control', 'placeholder' => 'Enter % of Effectiveness of FS/WW treatment']) !!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('recovered_operational_cost','% of operational cost recovered for STPs/WWTPs and FSTPs',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
                {!! Form::text('recovered_operational_cost',null, ['class' => 'form-control', 'placeholder' => 'Enter Operationak Cost Recovered']) !!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('treated_fecal_sludge','% of treated FS and wastewater that is reused',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
                {!! Form::text('treated_fecal_sludge', null,['class' => 'form-control', 'placeholder' => 'Enter Treated Fecal Sludge']) !!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('water_contamination_compliance','% of water contamination compliance (on fecal coliform)',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
                {!! Form::text('water_contamination_compliance',null,['class' => 'form-control', 'placeholder' => 'Enter % of Water Contamination Compliance']) !!}
		</div>
	</div>

</div><!-- /.card-body -->
<div class="card-footer">
	<a href="{{ action('Fsm\TreatmentPlantEffectivenessController@index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
	{!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div><!-- /.card-footer -->
