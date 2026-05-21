<div class="card-body">
        <div class="form-group row required">
            {!! Form::label('name', __('Organization Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Organization Type Name')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('code', __('Code'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                @if($organizationType && in_array($organizationType->code, \App\Models\Swm\OrganizationType::seededCodes(), true))
                    {!! Form::label(null, $organizationType->code, ['class' => 'form-control']) !!}
                @else
                    {!! Form::select('code', ['' => __('None')] + \App\Models\Swm\OrganizationType::codeOptions(), null, ['class' => 'form-control chosen-select', 'placeholder' => __('Code'), 'id' => 'code']) !!}
                @endif
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
	<a href="{{ route('swm.organization-types.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
	{!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>
