@push('style')
<style>
@media (max-width: 991.98px) {
    .swm-landfill-log-form-mobile.app-mobile-form .select2-container {
        width: 100% !important;
    }
}
</style>
@endpush
@php
    $isEdit = isset($landfillLog) && $landfillLog;
    $orgFieldVal = old('organization_id', $isEdit ? $landfillLog->organization_id : ($scopedOrganizationId ?? null));
    $vehicleFieldVal = old('vehicle_id', $isEdit ? $landfillLog->vehicle_id : null);
    $landfillFieldVal = old('landfill_id', $isEdit ? $landfillLog->landfill_id : null);
    $entryVal = old('entry_at');
    if ($entryVal === null && $isEdit && $landfillLog->entry_at) {
        $entryVal = $landfillLog->entry_at->format('Y-m-d\TH:i');
    }
    if ($entryVal === null && ! $isEdit) {
        $entryVal = \Carbon\Carbon::now()->format('Y-m-d\TH:i');
    }
    $opDateVal = old('operation_date', $isEdit && $landfillLog->operation_date ? $landfillLog->operation_date->format('Y-m-d') : \Carbon\Carbon::now()->format('Y-m-d'));
    $statusVal = old('operation_status', $isEdit ? $landfillLog->operation_status : \App\Models\Swm\LandfillLog::STATUS_PENDING);
    $wasteTypeFieldVal = old('waste_type_id', $isEdit ? $landfillLog->waste_type_id : null);
    $sourceStsArr = old('source_sts_ids', $isEdit && is_array($landfillLog->source_sts_ids) ? $landfillLog->source_sts_ids : []);
    $sourceWardsArr = old('source_wards', $isEdit && is_array($landfillLog->source_wards) ? $landfillLog->source_wards : []);
@endphp
<div class="swm-landfill-log-form-mobile app-mobile-form">
<div class="card-body">
    @if($isEdit)
        <div class="form-group row">
            <label class="col-sm-3 control-label">{{ __('Landfill Log ID') }}</label>
            <div class="col-sm-9">
                <p class="form-control-plaintext">{{ $landfillLog->id }}</p>
            </div>
        </div>
    @endif

    @if($scopedOrganizationId)
        {!! Form::hidden('organization_id', $scopedOrganizationId) !!}
        <div class="form-group row">
            {!! Form::label('organization_display', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                <p class="form-control-plaintext">{{ $organizations[$scopedOrganizationId] ?? '' }}</p>
            </div>
        </div>
    @else
        <div class="form-group row required">
            {!! Form::label('organization_id', __('Organization'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-9">
                {!! Form::select('organization_id', $organizations, $orgFieldVal, ['class' => 'form-control chosen-select', 'id' => 'organization_id', 'placeholder' => __('Select Organization')]) !!}
            </div>
        </div>
    @endif

    <div class="form-group row required">
        {!! Form::label('entry_at', __('Entry Date and Time'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            <input type="datetime-local" name="entry_at" id="entry_at" class="form-control" value="{{ $entryVal }}" />
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('operation_date', __('Operation Date'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            <input type="date" name="operation_date" id="operation_date" class="form-control" value="{{ $opDateVal }}" />
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('vehicle_id', __('Vehicle Number'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            <select name="vehicle_id" id="vehicle_id" class="form-control" style="width:100%" data-placeholder="{{ __('Search vehicle by number') }}" @if(!$orgFieldVal) disabled @endif></select>
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('vehicle_type_name', __('Vehicle Type'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::text('vehicle_type_name', old('vehicle_type_name', $isEdit ? $landfillLog->vehicle_type_name : null), ['class' => 'form-control', 'id' => 'vehicle_type_name', 'placeholder' => __('Vehicle Type'), 'autocomplete' => 'off']) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('driver_name', __('Driver Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::text('driver_name', old('driver_name', $isEdit ? $landfillLog->driver_name : null), ['class' => 'form-control', 'id' => 'driver_name', 'placeholder' => __('Driver Name'), 'autocomplete' => 'off']) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('landfill_id', __('Landfill Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::select('landfill_id', ['' => __('Select Landfill')] + $landfillList, $landfillFieldVal, ['class' => 'form-control chosen-select', 'id' => 'landfill_id']) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('landfill_name', __('Landfill Name (Text)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::text('landfill_name', old('landfill_name', $isEdit ? $landfillLog->landfill_name : null), ['class' => 'form-control', 'id' => 'landfill_name', 'placeholder' => __('Landfill Name'), 'autocomplete' => 'off']) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('waste_type_id', __('Waste Type'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::select('waste_type_id', ['' => __('Select Waste Type')] + $wasteTypeList, $wasteTypeFieldVal, ['class' => 'form-control chosen-select', 'id' => 'waste_type_id']) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('quantity_ton', __('Quantity (Ton)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            <input type="number" name="quantity_ton" id="quantity_ton" class="form-control" step="0.001" min="0"
                value="{{ old('quantity_ton', $isEdit && $landfillLog->quantity_ton !== null ? $landfillLog->quantity_ton : null) }}"
                placeholder="{{ __('Quantity in tons') }}" />
        </div>
    </div>

    <div class="form-group row" id="effective_quantity_group">
        {!! Form::label('effective_quantity_ton_display', __('Effective Quantity (Ton)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            <input type="text" id="effective_quantity_ton_display" class="form-control"
                value="{{ old('quantity_ton', $isEdit && $landfillLog->quantity_ton !== null ? $landfillLog->quantity_ton : null) }}"
                readonly />
        </div>
    </div>

    <div class="form-group row" id="weighbridge_group" style="display:none;">
        {!! Form::label('weighbridge_weight_ton', __('Weighbridge Weight (Ton)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            <input type="number" name="weighbridge_weight_ton" id="weighbridge_weight_ton" class="form-control" step="0.001" min="0"
                value="{{ old('weighbridge_weight_ton', $isEdit && $landfillLog->weighbridge_weight_ton !== null ? $landfillLog->weighbridge_weight_ton : null) }}"
                placeholder="{{ __('Weighbridge weight in tons') }}" />
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('source_sts_ids', __('Source STSs'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::select('source_sts_ids[]', $stsList, $sourceStsArr, ['class' => 'form-control', 'id' => 'source_sts_ids', 'multiple' => true, 'data-placeholder' => __('Source STSs')]) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('source_wards', __('Source Wards'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::select('source_wards[]', $wardOptions, $sourceWardsArr, ['class' => 'form-control', 'id' => 'source_wards', 'multiple' => true, 'data-placeholder' => __('Source Wards')]) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('operation_status', __('Operation Status'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::select('operation_status', $statusOptions, $statusVal, ['class' => 'form-control', 'id' => 'operation_status']) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('remarks', __('Remarks'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::textarea('remarks', old('remarks', $isEdit ? $landfillLog->remarks : null), ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Remarks')]) !!}
        </div>
    </div>
</div>
<div class="card-footer">
    <a href="{{ route('swm.landfill-logs.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div>
</div>

@push('scripts')
<script>
$(function () {
    var vehiclesUrl = @json(route('swm.landfill-logs.suggestions.vehicles'));
    var vehicleContextUrl = @json(route('swm.landfill-logs.vehicle-context'));
    var landfillContextUrl = @json(route('swm.landfill-logs.landfill-context'));
    var csrf = @json(csrf_token());
    var initialVehicleId = @json($vehicleFieldVal ? (string) $vehicleFieldVal : null);
    var initialVehicleText = @json($isEdit && isset($landfillLog) && $landfillLog->vehicle ? ($landfillLog->vehicle->vehicle_number ?: '') : null);

    function orgId() {
        @if($scopedOrganizationId)
        return String(@json((string) $scopedOrganizationId));
        @else
        var v = $('#organization_id').val();
        return v ? String(v) : '';
        @endif
    }

    function setWardsFromArray(arr) {
        var clean = Array.isArray(arr) ? arr.map(function (v) { return String(v).trim(); }).filter(function (v) { return v.length > 0; }) : [];
        var $sw = $('#source_wards');
        $sw.val(clean).trigger('change');
    }

    function setSourceSts(ids) {
        var clean = Array.isArray(ids) ? ids.map(function (v) { return String(v); }) : [];
        var $ss = $('#source_sts_ids');
        $ss.val(clean).trigger('change');
    }

    function initMultiSelect($el) {
        if (!$el.length || !$.fn.select2) {
            return;
        }
        if ($el.data('select2')) {
            $el.select2('destroy');
        }
        $el.select2({
            placeholder: $el.data('placeholder') || '',
            width: '100%',
            closeOnSelect: false,
            allowClear: true
        });
    }

    initMultiSelect($('#source_wards'));
    initMultiSelect($('#source_sts_ids'));

    function destroyVehicleSelect2() {
        var $v = $('#vehicle_id');
        if ($v.data('select2')) {
            $v.select2('destroy');
        }
    }

    function toggleWeighbridge(show) {
        $('#weighbridge_group').toggle(!!show);
        refreshEffectiveVisibility();
    }

    function syncEffectiveQuantity() {
        $('#effective_quantity_ton_display').val($('#quantity_ton').val());
    }

    function refreshEffectiveVisibility() {
        var weighbridgeVisible = $('#weighbridge_group').is(':visible');
        var hasWeighbridgeWeight = $.trim($('#weighbridge_weight_ton').val() || '') !== '';
        $('#effective_quantity_group').toggle(!(weighbridgeVisible && hasWeighbridgeWeight));
    }

    function setWasteTypeFromContext(wasteTypeId) {
        var $wt = $('#waste_type_id');
        if (wasteTypeId != null && wasteTypeId !== '') {
            var sid = String(wasteTypeId);
            if ($wt.find('option[value="' + sid + '"]').length) {
                $wt.val(sid);
            } else {
                $wt.val('');
            }
        } else {
            $wt.val('');
        }
        $wt.trigger('chosen:updated');
    }

    function fetchLandfillContext() {
        var lid = $('#landfill_id').val();
        if (!lid) {
            toggleWeighbridge(false);
            return;
        }
        $.ajax({
            url: landfillContextUrl,
            dataType: 'json',
            data: { landfill_id: lid },
            headers: { 'X-CSRF-TOKEN': csrf }
        }).done(function (data) {
            if (!data || data.error) {
                return;
            }
            if (data.landfill_name) {
                $('#landfill_name').val(String(data.landfill_name));
            }
            setWasteTypeFromContext(data.waste_type_id);
            if (Array.isArray(data.source_sts_ids)) {
                setSourceSts(data.source_sts_ids);
            }
            if (Array.isArray(data.source_wards)) {
                setWardsFromArray(data.source_wards);
            }
            toggleWeighbridge(!!data.weighbridge_facility_available);
        }).fail(function () {
            toggleWeighbridge(false);
        });
    }

    function applyVehicleContext(data) {
        if (!data || data.error) {
            return;
        }
        $('#vehicle_type_name').val(data.vehicle_type_name != null ? String(data.vehicle_type_name) : '');
        $('#driver_name').val(data.driver_name != null ? String(data.driver_name) : '');
        if (data.capacity != null && data.capacity !== '') {
            $('#quantity_ton').val(data.capacity);
        }
        if (data.landfill_id) {
            $('#landfill_id').val(String(data.landfill_id)).trigger('chosen:updated').trigger('change');
        }
        if (data.landfill_name) {
            $('#landfill_name').val(String(data.landfill_name));
        }
    }

    function fetchVehicleContext() {
        var oid = orgId();
        var vid = $('#vehicle_id').val();
        if (!oid || !vid) {
            return;
        }
        $.ajax({
            url: vehicleContextUrl,
            dataType: 'json',
            data: { organization_id: oid, vehicle_id: vid },
            headers: { 'X-CSRF-TOKEN': csrf }
        }).done(applyVehicleContext);
    }

    function initVehicleSelect2() {
        destroyVehicleSelect2();
        var oid = orgId();
        var $v = $('#vehicle_id');
        $v.prop('disabled', !oid);
        if (!oid) {
            return;
        }
        $v.select2({
            placeholder: $v.data('placeholder'),
            allowClear: true,
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: vehiclesUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        organization_id: oid,
                        q: params.term || ''
                    };
                },
                processResults: function (data) {
                    return { results: data.results || [] };
                },
                headers: { 'X-CSRF-TOKEN': csrf }
            }
        });

        if (initialVehicleId && initialVehicleText) {
            var opt = new Option(initialVehicleText, initialVehicleId, true, true);
            $v.append(opt).trigger('change');
            initialVehicleId = null;
            initialVehicleText = null;
        }
    }

    initVehicleSelect2();
    @if($landfillFieldVal)
    toggleWeighbridge(true);
    @endif
    fetchLandfillContext();
    syncEffectiveQuantity();
    refreshEffectiveVisibility();

    $('#vehicle_id').on('change', function () {
        fetchVehicleContext();
    });

    $('#quantity_ton').on('input change', function () {
        syncEffectiveQuantity();
        refreshEffectiveVisibility();
    });

    $('#weighbridge_weight_ton').on('input change', function () {
        refreshEffectiveVisibility();
    });

    $('#landfill_id').on('change', function () {
        fetchLandfillContext();
    });

    @if(!$scopedOrganizationId)
    $('#organization_id').on('change', function () {
        $('#vehicle_id').val(null).trigger('change');
        $('#vehicle_type_name').val('');
        $('#driver_name').val('');
        $('#landfill_id').val('').trigger('chosen:updated');
        $('#landfill_name').val('');
        $('#waste_type_id').val('').trigger('chosen:updated');
        $('#quantity_ton').val('');
        $('#weighbridge_weight_ton').val('');
        setSourceSts([]);
        setWardsFromArray([]);
        toggleWeighbridge(false);
        initVehicleSelect2();
    });
    @endif
});
</script>
@endpush
