<div class="card-body">
        <div class="form-group row">
            <label class="col-sm-3 control-label" for="worker_id_no_preview">{{ __('Worker ID') }}</label>
            <div class="col-sm-3">
                @isset($worker)
                    {!! Form::text('worker_id_no', null, ['class' => 'form-control', 'readonly' => true, 'id' => 'worker_id_no']) !!}
                @else
                    <input type="text" class="form-control" id="worker_id_no_preview" value="{{ $suggestedWorkerIdNo ?? '' }}" readonly autocomplete="off">
                    <small class="form-text text-muted">{{ __('Assigned automatically when you save.') }}</small>
                @endisset
            </div>
        </div>
        @if(!empty($scopedOrganizationId))
        <div class="form-group row required">
            {!! Form::label('organization_display', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::label(null, $organizations[(int) $scopedOrganizationId] ?? '', ['class' => 'form-control']) !!}
            </div>
            {!! Form::label('employee_id', __('Employee ID (Current Organization)'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('employee_id', null, ['class' => 'form-control', 'placeholder' => __('Employee ID (Current Organization)')]) !!}
            </div>
        </div>
        {!! Form::hidden('organization_id', $scopedOrganizationId) !!}
        @else
        <div class="form-group row required">
            {!! Form::label('organization_id', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('organization_id', $organizations, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Organization'), 'id' => 'organization_id']) !!}
            </div>
            {!! Form::label('employee_id', __('Employee ID (Current Organization)'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('employee_id', null, ['class' => 'form-control', 'placeholder' => __('Employee ID (Current Organization)')]) !!}
            </div>
        </div>
        @endif
        <div class="form-group row required">
            {!! Form::label('name', __('Worker Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Worker Name')]) !!}
            </div>
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
            {!! Form::label('age', __('Age (Years)'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('age', null, ['class' => 'form-control', 'placeholder' => __('Age (Years)'), 'min' => 0, 'max' => 120]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('gender', __('Gender'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('gender', ['male' => __('Male'), 'female' => __('Female'), 'others' => __('Others')], null, ['class' => 'form-control chosen-select', 'placeholder' => __('Gender')]) !!}
            </div>
            {!! Form::label('national_id_no', __('National ID No'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('national_id_no', null, ['class' => 'form-control', 'placeholder' => __('National ID No')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('service_area', __('Service Area'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('service_area', null, ['class' => 'form-control', 'placeholder' => __('Service Area')]) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('work_type_id', __('Work Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('work_type_id', $workTypes, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Work Type')]) !!}
            </div>
            {!! Form::label('employment_type', __('Employment Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('employment_type', ['permanent' => __('Permanent'), 'daily' => __('Daily'), 'contract' => __('Contract')], null, ['class' => 'form-control chosen-select', 'placeholder' => __('Employment Type')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('department', __('Department'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('department', null, ['class' => 'form-control', 'placeholder' => __('Department')]) !!}
            </div>
            {!! Form::label('supervisor_name', __('Supervisor Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('supervisor_name', null, ['class' => 'form-control', 'placeholder' => __('Supervisor Name')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('total_work_experience_years', __('Total Work Experience (Years)'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('total_work_experience_years', null, ['class' => 'form-control', 'placeholder' => __('Total Work Experience (Years)'), 'min' => 0, 'step' => '0.01']) !!}
            </div>
            {!! Form::label('organization_work_experience_years', __('Work Experience in This Organization (Years)'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('organization_work_experience_years', null, ['class' => 'form-control', 'placeholder' => __('Work Experience in This Organization (Years)'), 'min' => 0, 'step' => '0.01']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('education_level', __('Education Level'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('education_level', ['primary' => __('Primary'), 'secondary' => __('Secondary'), 'below_ssc' => __('Below SSC'), 'ssc' => __('SSC'), 'hsc' => __('HSC'), 'bachelor' => __('Bachelor'), 'master' => __('Master'), 'others' => __('Others')], null, ['class' => 'form-control chosen-select', 'placeholder' => __('Education Level')]) !!}
            </div>
            {!! Form::label('education_level_other', __('Education (Others specify)'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('education_level_other', null, ['class' => 'form-control', 'placeholder' => __('Education (Others specify)')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('status', __('Status'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('status', ['active' => __('Active'), 'inactive' => __('Inactive')], null, ['class' => 'form-control chosen-select', 'placeholder' => __('Status')]) !!}
            </div>
        </div>
</div>
<div class="card-footer">
	<a href="{{ route('swm.workers.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
	{!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>
