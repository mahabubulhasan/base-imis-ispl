<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<div class="card-body">
    <div class="form-group row required">
        {!! Form::label('name', __('Help Desk Name') ,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('name',null,['class' => 'form-control', 'placeholder' => __('Help Desk Name')]) !!}
        </div>
    </div>
    
    <div class="form-group row required">
        {!! Form::label('description',__('Description'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::textarea('description',null,['class' => 'form-control', 'placeholder' => __('Description')]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('contact_number',__('Contact Number'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('contact_number',null,['class' => 'form-control', 'placeholder' => __('Contact Number'), 'oninput' => "validateOwnerContactInput(this)"]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('email',__('Email Address'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('email',null,['class' => 'form-control', 'placeholder' => __('Email Address')]) !!}
        </div>
    </div>
    @if(Auth::user()->service_provider_id)
        <div class="form-group row required" id="service_provider">
        {!! Form::label('service_provider_id',__('Service Provider'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$serviceProviders[Auth::user()->service_provider_id],['class' => 'form-control']) !!}
            {!! Form::text('service_provider_id', Auth::user()->service_provider_id, ['hidden' => 'true']) !!}
        </div>
    </div>

    {{-- @else
    <div class="form-group row" id="service_provider">
        {!! Form::label('service_provider_id',__('Service Provider'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('service_provider_id', $serviceProviders, null, ['class' => 'form-control', 'placeholder' => __('Service Provider')]) !!}
        </div>
    </div> --}}
    @endif
    @if(!$helpDesk)
	<div class="form-group row">
		{!! Form::label('create_user',__('Create User?'),['class' => 'col-sm-3 control-label']) !!}
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
    {!! Form::label('password',__('Password'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        <!-- Password Input -->
        <input type="password" 
               class="form-control" 
               name="password" 
               id="password" 
               placeholder="{{__('Password')}}">

        <!-- Error/Requirement Message Container -->
        <div id="password-error" class="mt-1" style="display: none; color: red;">
            <ul style="margin-bottom: 0; padding-left: 1rem;">
                <li id="char-count">{{ __('The Password must be at least 8 characters.') }}</li>
                <li id="uppercase-lowercase">{{__('The Password must contain at least one uppercase and one lowercase letter.')}}</li>
                <li id="symbol">{{ __('The Password must contain at least one symbol.') }}</li>
                <li id="number">{{ __('The Password must contain at least one number.') }}</li>
            </ul>
        </div>
    </div>
</div>
<div class="form-group row">
    {!! Form::label('password_confirmation', __('Confirm Password'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        <input type="password" 
               class="form-control" 
               name="password_confirmation" 
               id="password_confirmation" 
               placeholder="{{__('Confirm Password')}}">

        <!-- Confirm Password Requirements -->
        <div id="confirm-password-error" class="mt-1" style="display: none; color: red;">
            <ul style="margin-bottom: 0; padding-left: 1rem;">
                <li id="confirm-char-count">{{ __('The Password must be at least 8 characters.') }}</li>
                <li id="confirm-uppercase-lowercase">{{__('The Password must contain at least one uppercase and one lowercase letter.')}}</li>
                <li id="confirm-symbol">{{ __('The Password must contain at least one symbol.') }}</li>
                <li id="confirm-number">{{ __('The Password must contain at least one number.') }}</li>
                <li id="confirm-match">{{ __('Confirm password must match the password.') }}</li>
            </ul>
        </div>
    </div>
</div>
</div>
	@endif
</div><!-- /.box-body -->
<div class="card-footer">
    <a href="{{ action('Fsm\HelpDeskController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div><!-- /.box-footer -->