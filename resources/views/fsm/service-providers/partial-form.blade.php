<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<div class="card-body">
        <div class="form-group row required">
            {!! Form::label('company_name', __('Company Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('company_name', null, ['class' => 'form-control', 'placeholder' => __('Company Name')]) !!}
            </div>
        </div>

        <div class="form-group row required">
            {!! Form::label('email', __('Email'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => __('Email')]) !!}
            </div>
        </div>

        <div class="form-group row required">
            {!! Form::label('ward', __('Ward Number'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('ward', $wards, null, ['class' => 'form-control', 'placeholder' => __('Ward Number')]) !!}
            </div>
        </div>

        <div class="form-group row required">
            {!! Form::label('company_location', __('Address'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('company_location', null, ['class' => 'form-control', 'placeholder' => __('Address')]) !!}
            </div>
        </div>

        <div class="form-group row required">
            {!! Form::label('contact_person', __('Contact Person Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('contact_person', null, ['class' => 'form-control', 'placeholder' => __('Contact Person Name')]) !!}
            </div>
        </div>

        <div class="form-group row required">
            {!! Form::label('contact_gender', __('Contact Person Gender'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('contact_gender', ['Male' => 'Male', 'Female' => 'Female', 'Others' => 'Others'], null, ['class' => 'form-control', 'placeholder' => __('Contact Person Gender')]) !!}
            </div>
        </div>

        <div class="form-group row required">
            {!! Form::label('contact_number', __('Contact Person Number'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('contact_number', null, ['class' => 'form-control', 'placeholder' => __('Contact Person Number'), 'oninput' => "validateOwnerContactInput(this)"]) !!}
            </div>
        </div>

        <div class="form-group row required">
            {!! Form::label('status', __('Status'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('status', $serviceProviderStatus, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Status')]) !!}
            </div>
        </div>


	@if(!$serviceProvider)
	<div class="form-group row">
		{!! Form::label('create_user', __('Create User?'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
		<input type="checkbox" 
       name="create_user" 
       id="create_user" 
       class="create_user" 
       value="on" 
       {{ old('create_user') ? 'checked' : '' }}>
       

		</div>
	</div>
	<div id="user-password">
	<div class="form-group row ">
    {!! Form::label('password',  __('Password'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        <!-- Password Input -->
        <input type="password" 
               class="form-control" 
               name="password" 
               id="password" 
               placeholder="{{ __('Password')}}">

        <!-- Error/Requirement Message Container -->
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

        <!-- Confirm Password Requirements -->
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
</div><!-- /.box-body -->
<div class="card-footer">
	<a href="{{ action('Fsm\ServiceProviderController@index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
	{!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div><!-- /.box-footer -->

