<div class="card-body">
        <div class="form-group row required">
            {!! Form::label('name', __('Landfill Type Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Landfill Type Name')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('description', __('Description'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Description')]) !!}
            </div>
        </div>
</div>
<div class="card-footer">
	<a href="{{ route('swm.landfill-types.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
	{!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>
