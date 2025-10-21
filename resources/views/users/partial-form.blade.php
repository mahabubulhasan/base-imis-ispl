<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<div class="card-body">
    <div class="form-group row required">
        {!! Form::label('name',__('Full Name'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('name',null,['class' => 'form-control', 'placeholder' => __('Full Name')]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label(__('Gender'),null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('gender', ["Male"=>"Male", "Female"=>"Female","Others"=>"Others"], null, ['class' => 'form-control', 'placeholder' => __('Gender')]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label(__('Username'),null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text(__('username'),null,['class' => 'form-control', 'placeholder' => __('Username')]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label(__('Email'),null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text(__('email'),null,['class' => 'form-control', 'placeholder' => __('Email')]) !!}
        </div>
    </div>
    <div class="form-group row required">
    {!! Form::label('password', __('Password'), ['class' => 'col-sm-3 control-label']) !!}
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
                <li id="char-count">{{__("The password must be at least 8 characters.")}}</li>
                <li id="uppercase-lowercase">{{__("The password must contain at least one uppercase and one lowercase letter.")}}</li>
                <li id="symbol">{{__("The password must contain at least one symbol.")}}</li>
                <li id="number">{{__("The password must contain at least one number.")}}</li>
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
                <li id="confirm-char-count">{{__("The password must be at least 8 characters.")}}</li>
                <li id="confirm-uppercase-lowercase">{{__("The password must contain at least one uppercase and one lowercase letter.")}}</li>
                <li id="confirm-symbol">{{__("The password must contain at least one symbol.")}}</li>
                <li id="confirm-number">{{__("The password must contain at least one number.")}}</li>
                <li id="confirm-match">{{__("Passwords must match.")}}</li>
            </ul>

        </div>
    </div>
</div>
    @if(Auth::user()->hasRole('Municipality - Sanitation Department'))
        <div class="form-group row">
            {!! Form::label(__('User Type'),null,['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('user_type', ["Service Provider"=>"Service Provider", "Treatment Plant"=>"Treatment Plant", "Help Desk"=>"Help Desk"], null, ['class' => 'form-control userType', 'placeholder' => __('User Type')]) !!}
            </div>
        </div>
    @else
        <div class="form-group row required">
            {!! Form::label(__('User Type'),null,['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('user_type', ["Municipality"=>"Municipality", "Service Provider"=>"Service Provider", "Treatment Plant"=>"Treatment Plant", "Help Desk"=>"Help Desk", "Guest"=>"Guest"], null, ['class' => 'form-control userType', 'placeholder' => __('User Type')]) !!}
            </div>
        </div>
    @endif
    <div class="form-group row required">
        {!! Form::label(__('Roles'),null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 roles-select">
            
        </div>
    </div>

    <div class="form-group row" id="treatment_plant">
        {!! Form::label('treatment_plant_id',__('Treatment Plant'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('treatment_plant_id', $treatmentPlants, null, ['class' => 'form-control ', 'placeholder' => __('Treatment Plant')]) !!}
        </div>
    </div>
    <div class="form-group row required" id="service_provider">
        {!! Form::label('service_provider_id',__('Service Provider'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('service_provider_id', $serviceProviders, null, ['class' => 'form-control', 'placeholder' => __('Service Provider')]) !!}
        
  
          </div>
    </div>
    @if (!$isEdit)
    <div class="form-group row required" id="service_help_desk">
    {!! Form::label('help_desk_id_1',__('Help Desk'),['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        <select id="helpDeskDropdown" name="help_desk_id_1" class="form-control">
            <option value="">Help Desk</option>
        </select>
    </div>
</div>
<div class="form-group row required" id="help_desk">
    {!! Form::label('help_desk_id_2',__('Help Desk'),['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::select('help_desk_id_2', $munhelpDesks, null, ['class' => 'form-control ', 'placeholder' => __('Help Desk')]) !!}
    </div>
</div>
@endif
@if ($isEdit)
    <!-- Additional input only for edit page -->
    <div class="form-group row required" id="edit_help_desk">
        {!! Form::label('help_desk_id',__('Help Desk'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('help_desk_id', $helpDesks, null, ['class' => 'form-control ', 'placeholder' => __('Help Desk')]) !!}
        </div>
    </div>
@endif
  
    <div class="form-group row required">
        {!! Form::label('status',__('Status'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('status', $status, null, ['class' => 'form-control ', 'placeholder' => __('Status')]) !!}
        </div>
    </div>
</div><!-- /.box-body -->
<div class="card-footer">
    <a href="{{ action('Auth\UserController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div><!-- /.card-footer -->
@push('scripts')
<script type="text/javascript">
  $(document).ready(function () {
    // Handle change event for service provider selection
    $('#service_provider_id').on('change', function () {
      const serviceProviderId = $(this).val();

      if (serviceProviderId) {
        $.ajax({
          url: `/auth/users/helpdesk/${serviceProviderId}`,
          type: 'GET',
          success: function (data) {
            const helpDeskDropdown = $('#helpDeskDropdown');
            helpDeskDropdown.empty().append('<option value="">Select Help Desk</option>');

            $.each(data, function (id, name) {
              helpDeskDropdown.append(`<option value="${id}">${name}</option>`);
            });
          },
          error: function () {
            alert('Error fetching Help Desk data.');
          },
        });
      } else {
        $('#helpDeskDropdown').empty().append('<option value="">Select Help Desk</option>');
      }
    });

    // Role selection logic
    function onRoleSelect(e) {
      const selected = $('.chosen-select').select2('data') || [];

      if (Array.isArray(selected)) {
        if (selected.some(e => e.text === 'Municipality - Help Desk')) {
          $('#help_desk').show();
          $('#service_provider').hide();
          $('#service_help_desk').hide();
        } else if (selected.some(e => e.text === 'Service Provider - Help Desk')) {
          $('#service_provider').show();
          $('#help_desk').hide();
          $('#service_help_desk').show();
        } else if (selected.some(e => e.text === 'Service Provider - Admin' || e.text === 'Service Provider - Emptying Operator')) {
          $('#service_provider').show();
          $('#help_desk').hide();
        } else {
          $('#service_provider').hide();
          $('#help_desk').hide();
          $('#service_help_desk').hide();
        }
      }
    }

    // Initializing roles and handling role selection
    let user_type = $('.userType').val();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $.ajax({
      url: '{!! url("auth/roles/list-roles") !!}',
      type: "GET",
      data: {
        user_type: user_type,
        @if(old("roles"))
          roles: '<?php echo json_encode(old("roles")); ?>'
        @elseif(isset($user))
          roles: '<?php echo json_encode($user->roles); ?>'
        @endif
      },
      cache: true,
      success: function (html) {
        $('.roles-select').html(html);
        $('.roles-select select').select2({
          placeholder: "Roles",
          allowClear: true,
          width: '100%'
        }).on('change', function (e) {
          onRoleSelect(e);
        });
        onRoleSelect(); // Ensure visibility is updated after roles load
      }
    });

    // Handling user type-based field visibility
    function updateFieldsBasedOnUserType() {
      const userType = $(".userType option:selected").val();

      switch (userType) {
        case 'Service Provider':
          $('#service_provider').show().prop("required", true);
          $('#treatment_plant, #help_desk').hide();
          $('#edit_help_desk').hide();
          break;
        case 'Municipality':
        case 'Guest':
          $('#service_provider, #treatment_plant, #help_desk').hide();
          $('#edit_help_desk').hide();
          break;
        case 'Treatment Plant':
          $('#service_provider, #help_desk').hide();
          $('#treatment_plant').show();
          $('#edit_help_desk').hide();
          break;
          case'Help Desk':
            $('#edit_help_desk').show();
            $('#treatment_plant').hide();
        break;
        default:
          $('#service_provider, #treatment_plant, #help_desk, #service_help_desk').hide();
          $('#edit_help_desk').hide();
          break;
      }
    }

    updateFieldsBasedOnUserType();

    $('.userType').change(function () {
      const user_type = $('.userType').val();

      $.ajax({
        url: '{!! url("auth/roles/list-roles") !!}',
        type: "GET",
        data: {
          user_type: user_type
        },
        cache: true,
        success: function (html) {
          $('.roles-select').html(html);
          $('.chosen-select').select2().on('change', function (e) {
            onRoleSelect(e);
          });
        }
      });

      updateFieldsBasedOnUserType();
    });

    // Password validation logic
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('password_confirmation');
    const passwordError = document.getElementById('password-error');
    const confirmPasswordError = document.getElementById('confirm-password-error');

    passwordField.addEventListener('focus', () => passwordError.style.display = 'block');
    confirmPasswordField.addEventListener('focus', () => confirmPasswordError.style.display = 'block');
    passwordField.addEventListener('blur', () => passwordError.style.display = 'none');
    confirmPasswordField.addEventListener('blur', () => confirmPasswordError.style.display = 'none');

    // Password validation
    passwordField.addEventListener('input', validatePassword);
    confirmPasswordField.addEventListener('input', validateConfirmPassword);

    function validatePassword() {
      const password = passwordField.value;
      const hasUpperCase = /[A-Z]/.test(password);
      const hasLowerCase = /[a-z]/.test(password);
      const hasNumber = /\d/.test(password);
      const hasSymbol = /[\W_]/.test(password);
      const hasMinLength = password.length >= 8;

      document.getElementById('char-count').style.color = hasMinLength ? 'green' : 'red';
      document.getElementById('uppercase-lowercase').style.color = (hasUpperCase && hasLowerCase) ? 'green' : 'red';
      document.getElementById('symbol').style.color = hasSymbol ? 'green' : 'red';
      document.getElementById('number').style.color = hasNumber ? 'green' : 'red';

      validateConfirmPassword();
    }

    function validateConfirmPassword() {
      const confirmPassword = confirmPasswordField.value;
      const password = passwordField.value;

      const hasUpperCase = /[A-Z]/.test(confirmPassword);
      const hasLowerCase = /[a-z]/.test(confirmPassword);
      const hasNumber = /\d/.test(confirmPassword);
      const hasSymbol = /[\W_]/.test(confirmPassword);
      const hasMinLength = confirmPassword.length >= 8;
      const passwordsMatch = confirmPassword === password;

      document.getElementById('confirm-char-count').style.color = hasMinLength ? 'green' : 'red';
      document.getElementById('confirm-uppercase-lowercase').style.color = (hasUpperCase && hasLowerCase) ? 'green' : 'red';
      document.getElementById('confirm-symbol').style.color = hasSymbol ? 'green' : 'red';
      document.getElementById('confirm-number').style.color = hasNumber ? 'green' : 'red';
      document.getElementById('confirm-match').style.color = passwordsMatch ? 'green' : 'red';
    }

    // Initialize Select2
    $('.chosen-select').select2().on('select2:select', function (e) {
      onRoleSelect(e);
    });
  });

$(document).ready(function () {
  function onRoleSelectAgain() {
    const selected = $('.chosen-select').select2('data') || [];
    if (Array.isArray(selected)) {
      if (selected.some(e => e.text === 'Municipality - Help Desk')) {
        $('#help_desk').show();
        $('#service_provider').hide();
        $('#service_help_desk').hide();
      } else if (selected.some(e => e.text === 'Service Provider - Help Desk')) {
        $('#service_provider').show();
        $('#help_desk').hide();
        $('#service_help_desk').show();
      } else if (selected.some(e => ['Service Provider - Admin', 'Service Provider - Emptying Operator'].includes(e.text))) {
        $('#service_provider').show();
        $('#help_desk').hide();
      } else {
        $('#service_provider').hide();
        $('#help_desk').hide();
        $('#service_help_desk').hide();
      }
    }
  }
  // Call function on page load
  onRoleSelect();
  onRoleSelectAgain();
  // Call function on change event
  $('.chosen-select').on('change', onRoleSelect);
});
</script>




@endpush
