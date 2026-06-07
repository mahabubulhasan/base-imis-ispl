<div class="swm-worker-form-mobile app-mobile-form">
<div class="card-body">
        @isset($worker)
        <div class="form-group row required">
            <label class="col-sm-3 control-label" for="worker_id_no">{{ __('ID') }}</label>
            <div class="col-sm-3">
                {!! Form::text('worker_id_no', null, ['class' => 'form-control', 'readonly' => true, 'id' => 'worker_id_no']) !!}
            </div>
        </div>
        @endisset
        <div class="form-group row required">
            {!! Form::label('name', __('Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Name')]) !!}
            </div>
        </div>
        <div class="form-group row">
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
        </div>
        <div class="form-group row required">
            {!! Form::label('mobile', __('Contact'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('mobile', null, ['class' => 'form-control', 'placeholder' => __('Contact')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('department', __('Department'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('department', null, ['class' => 'form-control', 'id' => 'department', 'placeholder' => __('Department'), 'autocomplete' => 'off']) !!}
                {{-- <small class="form-text text-muted">{{ __('Optional. Saved on this worker record.') }}</small> --}}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('work_type_id', __('Worker Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('work_type_id', $workTypes, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Worker Type')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('service_wards', __('Service Wards'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select(
                    'service_wards[]',
                    $wards,
                    old('service_wards', isset($worker) && $worker ? $worker->service_wards : []),
                    ['class' => 'form-control', 'id' => 'service_wards', 'multiple' => true, 'data-placeholder' => __('Service Wards')]
                ) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('employment_type', __('Employment Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('employment_type', ['permanent' => __('Permanent'), 'daily' => __('Daily'), 'contract' => __('Contract')], null, ['class' => 'form-control chosen-select', 'placeholder' => __('Employment Type')]) !!}
            </div>
        </div>
        
        @if(!empty($scopedOrganizationId))
        {!! Form::hidden('organization_id', $scopedOrganizationId) !!}
        <div class="form-group row">
            {!! Form::label('organization_display', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::label(null, $organizations[(int) $scopedOrganizationId] ?? '', ['class' => 'form-control']) !!}
            </div>
        </div>
        @else
        <div class="form-group row required">
            {!! Form::label('organization_id', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('organization_id', $organizations, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Organization'), 'id' => 'organization_id']) !!}
            </div>
        </div>
        @endif
        <div class="form-group row">
            {!! Form::label('supervisor_name', __('Supervisor\'s Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('supervisor_name', null, ['class' => 'form-control', 'placeholder' => __('Supervisor\'s Name')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('total_work_experience_years', __('Total Work Experience (Years)'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('total_work_experience_years', null, ['class' => 'form-control', 'placeholder' => __('Total Work Experience (Years)'), 'min' => 0, 'step' => '0.01']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('organization_work_experience_years', __('Work Experience in This Organization (Years)'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('organization_work_experience_years', null, ['class' => 'form-control', 'placeholder' => __('Work Experience in This Organization (Years)'), 'min' => 0, 'step' => '0.01']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('education_level', __('Education Level'), ['class' => 'col-sm-3 control-label', 'title' => __('Primary, Secondary (Below SSC), SSC, HSC, Bachelor, Master, Others (specify)')]) !!}
            <div class="col-sm-3">
                {!! Form::select('education_level', [
                    'primary' => __('Primary'),
                    'secondary' => __('Secondary (Below SSC)'),
                    'ssc' => __('SSC'),
                    'hsc' => __('HSC'),
                    'bachelor' => __('Bachelor'),
                    'master' => __('Master'),
                    'others' => __('Others (specify)'),
                ], null, ['class' => 'form-control chosen-select', 'placeholder' => __('Education Level'), 'id' => 'education_level']) !!}
                {{-- <small class="form-text text-muted">{{ __('Primary, Secondary (Below SSC), SSC, HSC, Bachelor, Master, Others (specify)') }}</small> --}}
            </div>
        </div>
        <div class="form-group row" id="education_level_other_group" style="display: none;">
            {!! Form::label('education_level_other', __('Education'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('education_level_other', null, ['class' => 'form-control', 'placeholder' => __('Education (Others specify)')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('employee_id', __('Employee ID (Current Organization)'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('employee_id', null, ['class' => 'form-control', 'placeholder' => __('Employee ID (Current Organization)')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('national_id_no', __('National ID'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('national_id_no', null, ['class' => 'form-control', 'placeholder' => __('National ID')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('status', __('Status'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('status', ['active' => __('Active'), 'inactive' => __('Inactive')], null, ['class' => 'form-control chosen-select', 'placeholder' => __('Status')]) !!}
            </div>
        </div>
        <!-- <div class="form-group row">
            {!! Form::label('email', __('Email'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('Email')]) !!}
            </div>
        </div> -->
</div>
<div class="card-footer">
	<a href="{{ route('swm.workers.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
	{!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>
</div>
@push('scripts')
<script>
$(function () {
    var $edu = $('#education_level');
    var $otherGroup = $('#education_level_other_group');
    var $serviceWardsSelect = $('#service_wards');

    if ($.fn.select2 && $serviceWardsSelect.length) {
        $serviceWardsSelect.select2({
            placeholder: $serviceWardsSelect.data('placeholder') || '{{ __("Service Wards") }}',
            width: '100%',
            closeOnSelect: false
        });
    }

    if (!$edu.length || !$otherGroup.length) {
        return;
    }

    function syncEducationOther() {
        var v = $edu.val();
        var show = v === 'others';
        $otherGroup.toggle(show);
    }
    $edu.on('change', syncEducationOther);
    syncEducationOther();
});
</script>
@endpush
