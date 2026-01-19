<div class="card-body">
	<div class="form-group row required">
		{!! Form::label('city','City',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('city',null,['class' => 'form-control', 'placeholder' => 'City']) !!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('api_post_url','URL To Send Data',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('api_post_url',null,['class' => 'form-control', 'placeholder' => 'URL To Send Data']) !!}
		</div>
	</div>
    <div class="form-group row required">
		{!! Form::label('api_login_url','URL For Authentication',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('api_login_url',null,['class' => 'form-control', 'placeholder' => 'URL For Authentication']) !!}
		</div>
	</div>
    <div class="form-group row required">
		{!! Form::label('nsd_username','Username',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('nsd_username',null,['class' => 'form-control', 'placeholder' => 'Username','autocomplete' => 'off']) !!}
		</div>
	</div>
    <div class="form-group row required">
		{!! Form::label('nsd_password','Password',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			@if(isset($nsd))
				{!! Form::hidden('current_password', $nsd->nsd_password) !!}
			@endif
			{!! Form::password('nsd_password', ['class' => 'form-control', 'placeholder' => 'Password', 'autocomplete' => 'off']) !!}
		</div>
	</div>
</div>

<div class="card-footer">
	@can('Edit NSD Setting')
	<span id="editButton" class="btn btn-info">Edit</span>
	@endcan
	@can('Save NSD Setting')
    <button type="submit" id="saveButton" class="btn btn-info" style="display: none;">Save</button>
</div><!-- /.box-footer -->
@endcan