@php
    $dumpKind = old('dumping_place_kind', isset($vehicle) && $vehicle ? $vehicle->dumping_place_kind : null);
    $organizationChoices = isset($organizations) && is_array($organizations) ? ['' => ''] + $organizations : ['' => ''];
@endphp
<div class="app-mobile-form">
<div class="card-body">
        @if(isset($vehicle) && $vehicle)
        <div class="form-group row">
            {!! Form::label('vehicle_id_no', __('Vehicle ID'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::text('vehicle_id_no', null, ['class' => 'form-control', 'readonly' => true, 'id' => 'vehicle_id_no']) !!}
            </div>
        </div>
        @endif
        <div class="form-group row required">
            {!! Form::label('vehicle_type_id', __('Vehicle Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::select('vehicle_type_id', $vehicleTypes, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Vehicle Type')]) !!}
            </div>
        </div>

        <div class="form-group row required">
            {!! Form::label('vehicle_number', __('Vehicle Number'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::text('vehicle_number', null, ['class' => 'form-control', 'placeholder' => __('Vehicle Number')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('capacity', __('Capacity') . ' (' . __('Ton') . ')', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::number('capacity', null, [
                    'class' => 'form-control',
                    'placeholder' => __('Capacity (Ton)'),
                    'min' => 0,
                    'step' => '0.01',
                    'inputmode' => 'decimal',
                ]) !!}
            </div>
        </div>

        @if(!empty($scopedOrganizationId))
        <div class="form-group row">
            {!! Form::label('organization_display', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::label(null, $organizations[(int) $scopedOrganizationId] ?? '', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('driver_worker_id', __('Driver Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::select('driver_worker_id', $driverWorkers ?? [], null, ['class' => 'form-control chosen-select', 'id' => 'driver_worker_id', 'placeholder' => __('Driver Name')]) !!}
            </div>
        </div>
        {!! Form::hidden('organization_id', $scopedOrganizationId) !!}
        @else
        <div class="form-group row">
            {!! Form::label('organization_id', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::select('organization_id', $organizationChoices, null, ['class' => 'form-control chosen-select', 'id' => 'organization_id', 'data-placeholder' => __('Organization')]) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('driver_worker_id', __('Driver Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::select('driver_worker_id', $driverWorkers ?? [], null, ['class' => 'form-control chosen-select', 'id' => 'driver_worker_id', 'placeholder' => __('Driver Name')]) !!}
            </div>
        </div>
        @endif

        <div class="form-group row">
            {!! Form::label('service_wards', __('Service Wards'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::select(
                    'service_wards[]',
                    $wards,
                    old('service_wards', isset($vehicle) && $vehicle ? $vehicle->service_wards : []),
                    ['class' => 'form-control', 'id' => 'service_wards', 'multiple' => true, 'data-placeholder' => __('Service Wards')]
                ) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('dumping_place_kind', __('Dumping Place Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::select('dumping_place_kind', [
                    'sts' => __('STS'),
                    'landfill' => __('Landfill'),
                    'other' => __('Others (specify)'),
                ], $dumpKind, ['class' => 'form-control', 'id' => 'dumping_place_kind']) !!}
            </div>
        </div>
        <div class="form-group row" id="dumping-sts-wrap" style="display:none;">
            {!! Form::label('dumping_sts_id', __('Dumping Place Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::select('dumping_sts_id', $stsList, null, ['class' => 'form-control chosen-select', 'placeholder' => __('STS')]) !!}
            </div>
        </div>
        <div class="form-group row" id="dumping-landfill-wrap" style="display:none;">
            {!! Form::label('dumping_landfill_id', __('Dumping Place Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::select('dumping_landfill_id', $landfills, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Landfill')]) !!}
            </div>
        </div>
        <div class="form-group row" id="dumping-other-wrap" style="display:none;">
            {!! Form::label('dumping_place_other', __('Specify Dumping Place'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::textarea('dumping_place_other', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Specify Dumping Place')]) !!}
            </div>
        </div>

        <div class="form-group row">
            {!! Form::label('fuel_type', __('Fuel Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::text('fuel_type', null, ['class' => 'form-control', 'placeholder' => __('Fuel Type')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('operational_type', __('Operational Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::select('operational_type', [
                    'day' => __('Day'),
                    'night' => __('Night'),
                    'mobile' => __('Mobile'),
                    'other' => __('Others (specify)'),
                ], null, ['class' => 'form-control', 'id' => 'operational_type', 'placeholder' => __('Operational Type')]) !!}
            </div>
        </div>
        <div class="form-group row" id="operational_type_other_group" style="display: none;">
            {!! Form::label('operational_type_other', __('Specify Operational Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::text('operational_type_other', null, ['class' => 'form-control', 'placeholder' => __('Specify Operational Type')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('engine_no', __('Engine No.'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::text('engine_no', null, ['class' => 'form-control', 'placeholder' => __('Engine No.')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('chassis_no', __('Chassis No.'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::text('chassis_no', null, ['class' => 'form-control', 'placeholder' => __('Chassis No.')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('status', __('Status'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::select('status', ['active' => __('Active'), 'inactive' => __('Inactive')], old('status', isset($vehicle) && $vehicle ? $vehicle->status : 'active'), ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('last_maintenance_year', __('Last Maintenance Year'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::number('last_maintenance_year', null, ['class' => 'form-control', 'placeholder' => __('Last Maintenance Year'), 'min' => 1900, 'max' => 2100]) !!}
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
</div>

@push('scripts')
<script>
$(function() {
    var driversUrl = @json($driversListUrl ?? '');
    var $serviceWardsSelect = $('#service_wards');

    if ($.fn.select2 && $serviceWardsSelect.length) {
        $serviceWardsSelect.select2({
            placeholder: $serviceWardsSelect.data('placeholder') || '{{ __("Service Wards") }}',
            width: '100%',
            closeOnSelect: false
        });
    }

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

    var $operationalType = $('#operational_type');
    var $operationalTypeOtherGroup = $('#operational_type_other_group');

    function syncOperationalTypeOther() {
        if (!$operationalType.length || !$operationalTypeOtherGroup.length) {
            return;
        }
        $operationalTypeOtherGroup.toggle($operationalType.val() === 'other');
    }

    $operationalType.on('change', syncOperationalTypeOther);
    syncOperationalTypeOther();

    @if(empty($scopedOrganizationId))
    function loadDrivers(orgId) {
        var $drv = $('#driver_worker_id');
        $drv.empty();
        $drv.append($('<option></option>').attr('value', '').text('{{ __('Driver Name') }}'));
        if (!driversUrl) {
            $drv.trigger('chosen:updated');
            return;
        }
        var params = {};
        if (orgId) {
            params.organization_id = orgId;
        }
        $.getJSON(driversUrl, params)
            .done(function(data) {
                $.each(data, function(id, name) {
                    $drv.append($('<option></option>').attr('value', id).text(name));
                });
            })
            .always(function() {
                $drv.trigger('chosen:updated');
            });
    }

    $('#organization_id').on('change', function() {
        loadDrivers($(this).val());
    });
    @endif
});
</script>
@endpush
