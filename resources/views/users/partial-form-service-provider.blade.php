<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<div class="card-body">
    <div class="form-group required">
        {!! Form::label('name',__('Full Name'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('name',null,['class' => 'form-control', 'placeholder' => __('Full Name')]) !!}
        </div>
    </div>
    <div class="form-group required">
        {!! Form::label(__('Gender'),null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('gender', ["M"=>"Male", "F"=>"Female"], null, ['class' => 'form-control', 'placeholder' => '--- Select Gender ---']) !!}
        </div>
    </div>
    <div class="form-group required">
        {!! Form::label('username',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('username',null,['class' => 'form-control', 'placeholder' => __('Username')]) !!}
        </div>
    </div>
    <div class="form-group required">
        {!! Form::label('email',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('email',null,['class' => 'form-control', 'placeholder' => __('Email')]) !!}
        </div>
    </div>
    <div class="form-group required">
        {!! Form::label('password',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="password" class="form-control" name="password" id="password" , placeholder= {{__("password")}}>
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('password_confirmation',__('Confirm Password'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="{{__('Confirm Password')}}">
        </div>
    </div>
    <div class="form-group"> 
    {!! Form::label(__('User Type'), null, ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        <select id="user_type" name="user_type" class="form-control">
            <option value="Service Provider" @if(old('user_type', isset($user) ? $user->user_type : null) == 'Service Provider') selected @endif>{{__('Service Provider')}}</option>
            <option value="Help Desk" @if(old('user_type', isset($user) ? $user->user_type : null) == 'Help Desk') selected @endif>{{__('Help Desk')}}</option>
        </select>
    </div>
</div>


    <div class="form-group required">
        {!! Form::label('roles',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 roles-select">
        </div>
    </div>
    {{--    <div class="form-group">
            {!! Form::label('ward',null,['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('ward',null,['class' => 'form-control', 'placeholder' => 'Ward']) !!}
            </div>
        </div>  --}}
    
    <div class="form-group required" id="service_provider">
        {!! Form::label('service_provider_id',__('Service Provider'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$serviceProviders[Auth::user()->service_provider_id],['class' => 'form-control']) !!}
            {!! Form::text('service_provider_id', Auth::user()->service_provider_id, ['hidden' => 'true']) !!}
        </div>
    </div>
    <div class="form-group" id="help_desk" style="display: none;">
    {!! Form::label('help_desk_id_1', __('Help Desk'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-3">
        {!! Form::select('help_desk_id_1', $helpDesks, old('help_desk_id_1', isset($user) ? $user->help_desk_id : null), ['class' => 'form-control', 'placeholder' => '--- Choose help desk ---']) !!}
    </div>
</div>


    <div class="form-group required">
        {!! Form::label('status',__('Status'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('status', $status, null, ['class' => 'form-control ', 'placeholder' => '--- Status ---']) !!}
        </div>
    </div>
</div><!-- /.box-body -->
<div class="card-footer">
    <a href="{{ action('Auth\UserController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div><!-- /.box-footer -->

@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        // Function to fetch roles based on selected user type
        function fetchRoles(userType) {
            $.ajax({
                url: '{!! url("auth/roles/list-servroles") !!}',
                type: "GET",
                data: {
                    user_type: userType, // Pass user type
                    @if(old("roles"))
                        roles: '<?php echo json_encode(old("roles"));?>',
                    @elseif(isset($user))
                        roles: '<?php echo json_encode($user->roles);?>',
                    @endif
                },
                success: function (html) {
                    $('.roles-select').html(html); // Update dropdown
                    $('.chosen-select').select2(); // Reinitialize select2
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error); // Log AJAX errors
                }
            });
        }

        // Initial roles loading based on existing user type (either from old or user data)
        const userType = $('#user_type').val() || 'Service Provider'; // Default to Service Provider if no user_type exists
        fetchRoles(userType);

        // Trigger AJAX when user_type changes
        $('#user_type').on('change', function () {
            const selectedUserType = $(this).val(); // Get selected user type
            fetchRoles(selectedUserType); // Fetch roles based on the selected user type

            // Show/Hide the help_desk field based on the selected user_type
            if (selectedUserType === 'Help Desk') {
                $('#help_desk').show();
            } else {
                $('#help_desk').hide();
                $('#help_desk_id').val(''); // Reset help_desk_id when not Help Desk
            }
        });

        // Handle role selection on dropdown change
        $(document).on('change', '#roles', function () {
            if ($(this).val().includes("Service Provider - Help Desk")) {
                $("#help_desk").show();
            } else {
                $("#help_desk").hide();
                $("#help_desk_id").val(''); // Reset when not selected
            }
        });

        // Check if the user type is Help Desk on page load
        if ($('#user_type').val() === 'Help Desk') {
            $('#help_desk').show();
        } else {
            $('#help_desk').hide();
        }
    });
</script>



@endpush