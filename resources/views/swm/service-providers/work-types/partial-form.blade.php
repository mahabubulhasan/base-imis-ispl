<div class="card-body">
        <div class="form-group row required">
            {!! Form::label('name', __('Work Type Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Work Type Name')]) !!}
            </div>
        </div>
</div>
<div class="card-footer">
	<a href="{{ route('swm.work-types.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
	{!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>
