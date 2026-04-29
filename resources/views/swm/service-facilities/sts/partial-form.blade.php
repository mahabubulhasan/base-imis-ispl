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
            {!! Form::label('capacity', __('Capacity') . ' (' . __('Ton') . ')', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('capacity', null, ['class' => 'form-control', 'placeholder' => __('Capacity'), 'min' => 0, 'step' => '0.01', 'inputmode' => 'decimal']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('segregation_practiced', __('Segregation Practiced'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3 pt-2">
                <input type="hidden" name="segregation_practiced" value="0">
                {!! Form::checkbox('segregation_practiced', '1', (bool) old('segregation_practiced', optional($sts)->segregation_practiced), ['id' => 'segregation_practiced']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('destination_landfill_id', __('Destination Landfill'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('destination_landfill_id', $landfills, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Destination Landfill')]) !!}
            </div>
        </div>
</div>
<div class="card-footer">
	<a href="{{ route('swm.sts.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
	{!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>
