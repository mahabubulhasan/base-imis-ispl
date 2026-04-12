<div class="card-body">
        @if(!empty($scopedOrganizationId))
        <div class="form-group row required">
            {!! Form::label('organization_display', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::label(null, $organizations[(int) $scopedOrganizationId] ?? '', ['class' => 'form-control']) !!}
            </div>
        </div>
        {!! Form::hidden('organization_id', $scopedOrganizationId) !!}
        @else
        <div class="form-group row required">
            {!! Form::label('organization_id', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('organization_id', $organizations, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Organization')]) !!}
            </div>
        </div>
        @endif
        <div class="form-group row required">
            {!! Form::label('work_type_id', __('Work Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('work_type_id', $workTypes, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Work Type')]) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('name', __('Worker Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Worker Name')]) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('mobile', __('Mobile'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('mobile', null, ['class' => 'form-control', 'placeholder' => __('Mobile')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('email', __('Email'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('Email')]) !!}
            </div>
        </div>
</div>
<div class="card-footer">
	<a href="{{ route('swm.workers.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
	{!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>
