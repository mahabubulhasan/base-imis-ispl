<div class="swm-organization-form-mobile app-mobile-form">
<div class="card-body">
        <div class="form-group row required">
            {!! Form::label('name', __('Organization Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Organization Name')]) !!}
            </div>
        </div>

        <div class="form-group row required">
            {!! Form::label('email', __('Email'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('Email')]) !!}
            </div>
        </div>

        <div class="form-group row required">
            {!! Form::label('address', __('Address'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::textarea('address', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Address')]) !!}
            </div>
        </div>

        <div class="form-group row required">
            {!! Form::label('contact_person_name', __('Contact Person Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('contact_person_name', null, ['class' => 'form-control', 'placeholder' => __('Contact Person Name')]) !!}
            </div>
        </div>

        <div class="form-group row required">
            {!! Form::label('contact_number', __('Contact Number'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('contact_number', null, ['class' => 'form-control', 'placeholder' => __('Contact Number'), 'oninput' => "validateOwnerContactInput(this)"]) !!}
            </div>
        </div>

        <div class="form-group row required">
            {!! Form::label('organization_type_id', __('Organization Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('organization_type_id', $organizationTypes, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Organization Type'), 'id' => 'organization_type_id']) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('service_wards', __('Service Wards'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('service_wards[]', $wards, optional($organization)->service_wards, ['class' => 'form-control', 'id' => 'service_wards', 'multiple' => true, 'data-placeholder' => __('Service Wards')]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('remarks', __('Remarks'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::textarea('remarks', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Remarks')]) !!}
            </div>
        </div>

        <div class="form-group row required">
            {!! Form::label('status', __('Status'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('status', $organizationStatus, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Status')]) !!}
            </div>
        </div>

    @php
        $showCreateUserFields = ! $organization || ($canCreateUserOnEdit ?? false);
    @endphp
	@if($showCreateUserFields)
	@php
		$createUserOptions = ['' => __('Select'), '1' => __('Yes'), '0' => __('No')];
	@endphp
	<div class="form-group row">
		{!! Form::label('create_user', __('Create User?'), ['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('create_user', $createUserOptions, old('create_user', '0'), ['class' => 'form-control', 'id' => 'create_user']) !!}
		</div>
	</div>
	<div id="user-password">
	<div class="form-group row ">
    {!! Form::label('password',  __('Password'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        <input type="password"
               class="form-control"
               name="password"
               id="password"
               placeholder="{{ __('Password')}}">

        <div id="password-error" class="mt-1" style="display: none; color: red;">
            <ul style="margin-bottom: 0; padding-left: 1rem;">
                <li id="char-count">{{__('The Password must be at least 8 characters.')}}</li>
                <li id="uppercase-lowercase">{{__('The Password must contain at least one uppercase and one lowercase letter.')}}</li>
                <li id="symbol">{{__('The Password must contain at least one symbol.')}}</li>
                <li id="number">{{__('The Password must contain at least one number.')}}</li>
            </ul>
        </div>
    </div>
</div>
<div class="form-group row">
    {!! Form::label('password_confirmation',  __('Confirm Password'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        <input type="password"
               class="form-control"
               name="password_confirmation"
               id="password_confirmation"
               placeholder="{{ __('Confirm Password')}}">

        <div id="confirm-password-error" class="mt-1" style="display: none; color: red;">
            <ul style="margin-bottom: 0; padding-left: 1rem;">
                <li id="confirm-char-count">{{__('The Password must be at least 8 characters.')}}</li>
                <li id="confirm-uppercase-lowercase">{{__('The Password must contain at least one uppercase and one lowercase letter.')}}</li>
                <li id="confirm-symbol">{{__('The Password must contain at least one symbol.')}}</li>
                <li id="confirm-number">{{__('The Password must contain at least one number.')}}</li>
                <li id="confirm-match">{{__('Passwords must match.')}}</li>
            </ul>
        </div>
    </div>
</div>
</div>

	@endif
</div>
<div class="card-footer">
	<a href="{{ route('swm.organizations.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
	{!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>
</div>
@push('scripts')
<script>
$(function() {
    var $serviceWardsSelect = $('#service_wards');

    if ($.fn.select2 && $serviceWardsSelect.length) {
        $serviceWardsSelect.select2({
            placeholder: $serviceWardsSelect.data('placeholder') || '{{ __("Service Wards") }}',
            width: '100%',
            closeOnSelect: false
        });
    }
});
</script>
@endpush
