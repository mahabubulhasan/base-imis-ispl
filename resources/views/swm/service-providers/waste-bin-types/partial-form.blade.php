<div class="card-body">
        <div class="form-group row required">
            {!! Form::label('name', __('Waste Bin Type Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Waste Bin Type Name')]) !!}
            </div>
        </div>
</div>
<div class="card-footer">
	<a href="{{ route('swm.waste-bin-types.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
	{!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>
