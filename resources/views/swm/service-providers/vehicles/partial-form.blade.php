@php
    $dumpKind = old('dumping_place_kind', isset($vehicle) && $vehicle ? $vehicle->dumping_place_kind : null);
@endphp
<div class="card-body">
        @if(!empty($scopedOrganizationId))
        <div class="form-group row required">
            {!! Form::label('organization_display', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::label(null, $organizations[(int) $scopedOrganizationId] ?? '', ['class' => 'form-control']) !!}
            </div>
        </div>
        {!! Form::hidden('organization_id', $scopedOrganizationId) !!}
        @else
        <div class="form-group row required">
            {!! Form::label('organization_id', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('organization_id', $organizations, null, ['class' => 'form-control chosen-select', 'id' => 'organization_id', 'placeholder' => __('Organization')]) !!}
            </div>
        </div>
        @endif
        <div class="form-group row required">
            {!! Form::label('vehicle_type_id', __('Vehicle Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('vehicle_type_id', $vehicleTypes, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Vehicle Type')]) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('vehicle_number', __('Vehicle Number'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('vehicle_number', null, ['class' => 'form-control', 'placeholder' => __('Vehicle Number')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('capacity', __('Capacity'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('capacity', null, ['class' => 'form-control', 'placeholder' => __('Capacity')]) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('driver_worker_id', __('Driver'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('driver_worker_id', $driverWorkers ?? [], null, ['class' => 'form-control chosen-select', 'id' => 'driver_worker_id', 'placeholder' => __('Driver')]) !!}
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
