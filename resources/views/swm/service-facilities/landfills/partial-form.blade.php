<div class="card-body">
        <div class="form-group row required">
            {!! Form::label('name', __('Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Name')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('location', __('Location'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('location', null, ['class' => 'form-control', 'placeholder' => __('Location')]) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('operator_name', __('Operator Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('operator_name', null, ['class' => 'form-control', 'placeholder' => __('Operator Name')]) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('contact_number', __('Contact Number'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('contact_number', null, ['class' => 'form-control', 'placeholder' => __('Contact Number')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('capacity', __('Capacity'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('capacity', null, ['class' => 'form-control', 'placeholder' => __('Capacity')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('segregation_practiced', __('Segregation Practiced'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3 pt-2">
                <input type="hidden" name="segregation_practiced" value="0">
                {!! Form::checkbox('segregation_practiced', '1', (bool) old('segregation_practiced', optional($landfill)->segregation_practiced), ['id' => 'segregation_practiced']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('reuse_practiced', __('Reuse Practiced'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3 pt-2">
                <input type="hidden" name="reuse_practiced" value="0">
                {!! Form::checkbox('reuse_practiced', '1', (bool) old('reuse_practiced', optional($landfill)->reuse_practiced), ['id' => 'reuse_practiced']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('monthly_waste_for_composting', __('Monthly Waste for Composting'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('monthly_waste_for_composting', null, ['class' => 'form-control', 'placeholder' => __('Monthly Waste for Composting')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('treatment', __('Treatment'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3 pt-2">
                <input type="hidden" name="treatment" value="0">
                {!! Form::checkbox('treatment', '1', (bool) old('treatment', optional($landfill)->treatment), ['id' => 'treatment']) !!}
            </div>
        </div>
</div>
<div class="card-footer">
	<a href="{{ route('swm.landfills.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
	{!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>
