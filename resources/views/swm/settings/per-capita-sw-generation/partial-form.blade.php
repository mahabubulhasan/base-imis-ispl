@php
    $readOnly = ! auth()->user()?->can('Edit SW Per Capita Generation Setting');
@endphp
<div class="swm-per-capita-form-mobile app-mobile-form">
<div class="card-body">
    <div class="form-group row {{ $readOnly ? '' : 'required' }}">
        {!! Form::label('per_capita_sw_generation_kg_per_day', __('Per Capita Waste Generation (Kg/day)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('per_capita_sw_generation_kg_per_day', null, [
                'class' => 'form-control',
                'step' => '0.01',
                'min' => '0',
                'placeholder' => __('Kg/day'),
                'readonly' => $readOnly,
                'required' => ! $readOnly,
            ]) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('description', __('Description'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::textarea('description', null, [
                'class' => 'form-control',
                'rows' => 3,
                'placeholder' => __('Description'),
                'readonly' => $readOnly,
            ]) !!}
        </div>
    </div>
</div>
@can('Edit SW Per Capita Generation Setting')
<div class="card-footer">
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div>
@endcan
</div>
