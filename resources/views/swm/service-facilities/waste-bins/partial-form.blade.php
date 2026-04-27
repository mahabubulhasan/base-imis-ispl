<div class="card-body">
    <div class="form-group row required">
        {!! Form::label('household_id', __('Household'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::select('household_id', $households, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Select Household')]) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('bin', __('BIN'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('bin', null, ['class' => 'form-control']) !!}</div>
    </div>
    <div class="form-group row required">
        {!! Form::label('number_of_waste_bins', __('Number of waste bins'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::number('number_of_waste_bins', null, ['class' => 'form-control', 'min' => 1]) !!}</div>
    </div>
    <div class="form-group row required">
        {!! Form::label('total_capacity_kg', __('Total capacity of waste bins (kg)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::number('total_capacity_kg', null, ['class' => 'form-control', 'step' => '0.01', 'min' => 0]) !!}</div>
    </div>
</div>
<div class="card-footer">
    <a href="{{ route('swm.waste-bins.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div>
