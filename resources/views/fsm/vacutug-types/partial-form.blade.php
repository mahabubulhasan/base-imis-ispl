<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<div class="card-body">
@if(Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Municipality - Super Admin') || Auth::user()->hasRole('Municipality - IT Admin'))
    <div class="form-group row required">
        {!! Form::label('service_provider_id',__('Service Provider'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('service_provider_id', $serviceProviders, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Service Provider')]) !!}
        </div>
    </div>
@else
    <div class="form-group row required">
        {!! Form::label('service_provider_id',__('Service Provider'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label($serviceProviders[Auth::user()->service_provider_id], $serviceProviders[Auth::user()->service_provider_id], ['class' => 'form-control']) !!}
            {!! Form::text('service_provider_id', Auth::user()->service_provider_id, ['hidden' => 'true']) !!}
        </div>
    </div>
@endif
    <div class="form-group row required">
        {!! Form::label('license_plate_number',__('Vehicle License Plate Number'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('license_plate_number', null, ['class' => 'form-control', 'placeholder' => __('Vehicle License Plate Number')]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('capacity',__('Capacity (m³)'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('capacity', null, ['class' => 'form-control', 'placeholder' => __('Capacity (m³)'), 'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '');"]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('width',__('Width (m)'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('width', null, ['class' => 'form-control', 'placeholder' => __('Width (m)'), 'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '');"]) !!}
        </div>
    </div>
   <div class="form-group row required">
        {!! Form::label('comply_with_maintainance_standards',__('Comply with Maintenance Standards'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('comply_with_maintainance_standards', $complyMaintainStandard, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Comply with Maintenance Standards')]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('status',__('Status'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('status', $status, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Status')]) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('description',__('Description'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Description')]) !!}
        </div>
    </div>
</div><!-- /.card-body -->
<div class="card-footer">
    <a href="{{ action('Fsm\VacutugTypeController@index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div><!-- /.card-footer -->
