@php
    $dumpKind = old('dumping_place_kind', isset($vehicle) && $vehicle ? $vehicle->dumping_place_kind : null);
@endphp
<div class="card-body">
        <div class="form-group row">
            {!! Form::label('vehicle_id_no', __('Vehicle ID'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                @if(isset($vehicle) && $vehicle->vehicle_id_no)
                    {!! Form::text('vehicle_id_no', null, ['class' => 'form-control', 'readonly' => true, 'id' => 'vehicle_id_no']) !!}
                @else
                    {!! Form::text('vehicle_id_no', null, ['class' => 'form-control', 'placeholder' => __('Vehicle ID'), 'id' => 'vehicle_id_no']) !!}
                    @if(isset($vehicle) && ! $vehicle->vehicle_id_no)
                        <small class="form-text text-muted">{{ __('Assigned automatically when you save if left blank.') }}</small>
                    @endif
                @endif
            </div>
            {!! Form::label('status', __('Status'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('status', ['active' => __('Active'), 'inactive' => __('Inactive')], old('status', isset($vehicle) && $vehicle ? $vehicle->status : 'active'), ['class' => 'form-control']) !!}
            </div>
        </div>

        @if(!empty($scopedOrganizationId))
        <div class="form-group row required">
            {!! Form::label('organization_display', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::label(null, $organizations[(int) $scopedOrganizationId] ?? '', ['class' => 'form-control']) !!}
            </div>
            {!! Form::label('service_area', __('Service Area'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('service_area', null, ['class' => 'form-control', 'placeholder' => __('Service Area')]) !!}
            </div>
        </div>
        {!! Form::hidden('organization_id', $scopedOrganizationId) !!}
        @else
        <div class="form-group row required">
            {!! Form::label('organization_id', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('organization_id', $organizations, null, ['class' => 'form-control chosen-select', 'id' => 'organization_id', 'placeholder' => __('Organization')]) !!}
            </div>
            {!! Form::label('service_area', __('Service Area'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('service_area', null, ['class' => 'form-control', 'placeholder' => __('Service Area')]) !!}
            </div>
        </div>
        @endif

        <div class="form-group row required">
            {!! Form::label('vehicle_type_id', __('Vehicle Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('vehicle_type_id', $vehicleTypes, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Vehicle Type')]) !!}
            </div>
            {!! Form::label('vehicle_number', __('Vehicle Number'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('vehicle_number', null, ['class' => 'form-control', 'placeholder' => __('Vehicle Number')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('capacity', __('Capacity') . ' (' . __('Ton') . ')', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('capacity', null, [
                    'class' => 'form-control',
                    'placeholder' => __('Capacity (Ton)'),
                    'min' => 0,
                    'step' => '0.01',
                    'inputmode' => 'decimal',
                ]) !!}
            </div>
            {!! Form::label('fuel_type', __('Fuel Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('fuel_type', null, ['class' => 'form-control', 'placeholder' => __('Fuel Type')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('operational_type', __('Operational Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('operational_type', null, ['class' => 'form-control', 'placeholder' => __('Operational Type')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('vehicle_registration_no', __('Vehicle Registration No.'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('vehicle_registration_no', null, ['class' => 'form-control', 'placeholder' => __('Vehicle Registration No.')]) !!}
            </div>
            {!! Form::label('engine_no', __('Engine No.'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('engine_no', null, ['class' => 'form-control', 'placeholder' => __('Engine No.')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('chassis_no', __('Chassis No.'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('chassis_no', null, ['class' => 'form-control', 'placeholder' => __('Chassis No.')]) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('driver_worker_id', __('Driver'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('driver_worker_id', $driverWorkers ?? [], null, ['class' => 'form-control chosen-select', 'id' => 'driver_worker_id', 'placeholder' => __('Driver')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('last_maintenance_year', __('Last Maintenance'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('last_maintenance_year', null, ['class' => 'form-control', 'placeholder' => __('Year'), 'min' => 1900, 'max' => 2100]) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('dumping_place_kind', __('Dumping Place Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('dumping_place_kind', [
                    'sts' => __('STS'),
                    'landfill' => __('Landfill'),
                    'other' => __('Others (specify)'),
                ], $dumpKind, ['class' => 'form-control', 'id' => 'dumping_place_kind']) !!}
            </div>
        </div>
        <div class="form-group row" id="dumping-sts-wrap" style="display:none;">
            {!! Form::label('dumping_sts_id', __('Dumping Place Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('dumping_sts_id', $stsList, null, ['class' => 'form-control chosen-select', 'placeholder' => __('STS')]) !!}
            </div>
        </div>
        <div class="form-group row" id="dumping-landfill-wrap" style="display:none;">
            {!! Form::label('dumping_landfill_id', __('Dumping Place Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('dumping_landfill_id', $landfills, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Landfill')]) !!}
            </div>
        </div>
        <div class="form-group row" id="dumping-other-wrap" style="display:none;">
            {!! Form::label('dumping_place_other', __('Specify dumping place'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::textarea('dumping_place_other', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Specify dumping place')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('remarks', __('Remarks'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::textarea('remarks', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Remarks')]) !!}
            </div>
        </div>
</div>
<div class="card-footer">
	<a href="{{ route('swm.vehicles.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
	{!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>

@push('scripts')
<script>
$(function() {
    var driversUrl = @json($driversListUrl ?? '');

    function refreshDumpingVisibility() {
        var k = $('#dumping_place_kind').val();
        $('#dumping-sts-wrap').toggle(k === 'sts');
        $('#dumping-landfill-wrap').toggle(k === 'landfill');
        $('#dumping-other-wrap').toggle(k === 'other');
        if (typeof $('#dumping_sts_id').trigger === 'function') {
            $('#dumping_sts_id').trigger('chosen:updated');
            $('#dumping_landfill_id').trigger('chosen:updated');
        }
    }

    $('#dumping_place_kind').on('change', refreshDumpingVisibility);
    refreshDumpingVisibility();

    @if(empty($scopedOrganizationId))
    $('#organization_id').on('change', function() {
        var orgId = $(this).val();
        var $drv = $('#driver_worker_id');
        $drv.empty();
        $drv.append($('<option></option>').attr('value', '').text('{{ __('Driver') }}'));
        if (!orgId || !driversUrl) {
            $drv.trigger('chosen:updated');
            return;
        }
        $.getJSON(driversUrl, { organization_id: orgId })
            .done(function(data) {
                $.each(data, function(id, name) {
                    $drv.append($('<option></option>').attr('value', id).text(name));
                });
            })
            .always(function() {
                $drv.trigger('chosen:updated');
            });
    });
    @endif
});
</script>
@endpush
