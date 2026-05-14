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
    $wasteTypeIdsOld = old('waste_type_ids');
    if (is_array($wasteTypeIdsOld)) {
        $wasteTypeIdsForField = array_values(array_unique(array_filter(
            array_map(static fn ($v) => (int) $v, $wasteTypeIdsOld),
            static fn ($v) => $v > 0
        )));
    } elseif ($isEdit) {
        $wasteTypeIdsForField = is_array($landfillLog->waste_type_ids) && count($landfillLog->waste_type_ids)
            ? $landfillLog->waste_type_ids
            : ($landfillLog->waste_type_id ? [$landfillLog->waste_type_id] : []);
    } else {
        $wasteTypeIdsForField = [];
    }
    $initialWasteForJs = [];
    if (count($wasteTypeIdsForField)) {
        $initialWasteForJs = \App\Models\Swm\WasteType::query()
            ->whereIn('id', $wasteTypeIdsForField)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->map(fn ($w) => ['id' => (string) $w->id, 'text' => $w->name])
            ->values()
            ->all();
    }
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
            <select name="vehicle_id" id="vehicle_id" class="form-control" style="width:100%" data-placeholder="{{ __('Search vehicle by number') }}"></select>
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
            {!! Form::select('landfill_id', ['' => __('Select Landfill')] + $landfillList, $landfillFieldVal, ['class' => 'form-control swm-landfill-select2', 'id' => 'landfill_id', 'style' => 'width:100%', 'data-placeholder' => __('Search or select landfill')]) !!}
        </div>
    </div>

    {!! Form::hidden('landfill_name', old('landfill_name', $isEdit ? $landfillLog->landfill_name : null), ['id' => 'landfill_name']) !!}

    <div class="form-group row">
        {!! Form::label('waste_type_ids', __('Waste Types'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            <select name="waste_type_ids[]" id="waste_type_ids" class="form-control" multiple style="width:100%" data-placeholder="{{ __('Search or select waste types') }}"></select>
        </div>
    </div>

    <div class="form-group row" id="effective_quantity_group">
        {!! Form::label('quantity_ton', __('Quantity (Ton)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            <input type="number" name="quantity_ton" id="quantity_ton" class="form-control" step="0.001" min="0"
                value="{{ old('quantity_ton', $isEdit && $landfillLog->quantity_ton !== null ? $landfillLog->quantity_ton : null) }}"
                placeholder="{{ __('Quantity in tons') }}" />
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
    var wasteTypesUrl = @json(route('swm.landfill-logs.suggestions.waste-types'));
    var vehicleContextUrl = @json(route('swm.landfill-logs.vehicle-context'));
    var landfillContextUrl = @json(route('swm.landfill-logs.landfill-context'));
    var csrf = @json(csrf_token());
    var initialVehicleId = @json($vehicleFieldVal ? (string) $vehicleFieldVal : null);
    var initialVehicleText = @json($isEdit && isset($landfillLog) && $landfillLog->vehicle ? ($landfillLog->vehicle->vehicle_number ?: '') : null);
    var initialWasteForJs = @json($initialWasteForJs);
    var landfillSkipWasteOnNextChange = false;

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

    function initLandfillSelect2() {
        var $l = $('#landfill_id');
        if (!$l.length || !$.fn.select2) {
            return;
        }
        if ($l.data('chosen')) {
            try {
                $l.chosen('destroy');
            } catch (e) { /* ignore */ }
        }
        if ($l.data('select2')) {
            $l.select2('destroy');
        }
        $l.select2({
            width: '100%',
            placeholder: $l.data('placeholder') || @json(__('Search or select landfill')),
            allowClear: true
        });
    }

    initLandfillSelect2();

    function destroyWasteTypesSelect2() {
        var $wt = $('#waste_type_ids');
        if ($wt.length && $wt.data('select2')) {
            $wt.select2('destroy');
        }
    }

    function initWasteTypesSelect2() {
        var $wt = $('#waste_type_ids');
        if (!$wt.length || !$.fn.select2) {
            return;
        }
        destroyWasteTypesSelect2();
        (initialWasteForJs || []).forEach(function (p) {
            $wt.append(new Option(p.text, p.id, true, true));
        });
        $wt.select2({
            placeholder: $wt.data('placeholder') || @json(__('Search or select waste types')),
            allowClear: true,
            width: '100%',
            multiple: true,
            closeOnSelect: false,
            minimumInputLength: 0,
            ajax: {
                url: wasteTypesUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term || '' };
                },
                processResults: function (data) {
                    return { results: data.results || [] };
                },
                headers: { 'X-CSRF-TOKEN': csrf }
            }
        });
    }

    initWasteTypesSelect2();

    function setWasteTypesFromContext(data) {
        var $wt = $('#waste_type_ids');
        if (!$wt.length) {
            return;
        }
        var ids = [];
        if (data && Array.isArray(data.waste_type_ids)) {
            ids = data.waste_type_ids.map(function (x) { return String(x); });
        } else if (data && data.waste_type_id != null && data.waste_type_id !== '') {
            ids = [String(data.waste_type_id)];
        }
        var pairs = data && Array.isArray(data.waste_types) ? data.waste_types : [];
        function optionExists(val) {
            return $wt.find('option').filter(function () { return String(this.value) === String(val); }).length > 0;
        }
        pairs.forEach(function (p) {
            var id = String(p.id);
            if (!optionExists(id)) {
                $wt.append(new Option(p.name, id, false, false));
            }
        });
        ids.forEach(function (id) {
            if (!optionExists(id)) {
                $wt.append(new Option(id, id, false, false));
            }
        });
        if ($wt.data('select2')) {
            $wt.val(ids.length ? ids : null).trigger('change');
        }
    }

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
        $('#effective_quantity_group').toggle(!weighbridgeVisible);
    }

    function fetchLandfillContext(syncWasteTypes, syncSourceFields) {
        if (typeof syncSourceFields === 'undefined') {
            syncSourceFields = syncWasteTypes;
        }
        var lid = $('#landfill_id').val();
        if (!lid) {
            toggleWeighbridge(false);
            if (syncWasteTypes) {
                setWasteTypesFromContext({ waste_type_ids: [], waste_types: [] });
            }
            if (syncSourceFields) {
                setSourceSts([]);
                setWardsFromArray([]);
            }
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
            toggleWeighbridge(!!data.weighbridge_facility_available);
            if (syncSourceFields) {
                if (Array.isArray(data.source_sts_ids)) {
                    setSourceSts(data.source_sts_ids);
                }
                if (Array.isArray(data.source_wards)) {
                    setWardsFromArray(data.source_wards);
                }
            }
            if (syncWasteTypes) {
                setWasteTypesFromContext(data);
            }
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
            landfillSkipWasteOnNextChange = true;
            $('#landfill_id').val(String(data.landfill_id)).trigger('change');
        }
        if (data.landfill_name) {
            $('#landfill_name').val(String(data.landfill_name));
        }
        setWasteTypesFromContext({
            waste_type_ids: Array.isArray(data.waste_type_ids) ? data.waste_type_ids : [],
            waste_types: Array.isArray(data.waste_types) ? data.waste_types : [],
        });
    }

    function fetchVehicleContext() {
        var vid = $('#vehicle_id').val();
        if (!vid) {
            return;
        }
        $.ajax({
            url: vehicleContextUrl,
            dataType: 'json',
            data: { vehicle_id: vid },
            headers: { 'X-CSRF-TOKEN': csrf }
        }).done(applyVehicleContext);
    }

    function initVehicleSelect2() {
        destroyVehicleSelect2();
        var $v = $('#vehicle_id');
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
    fetchLandfillContext(false);
    @endif
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
        if (landfillSkipWasteOnNextChange) {
            landfillSkipWasteOnNextChange = false;
            fetchLandfillContext(false, true);
            return;
        }
        fetchLandfillContext(true);
    });
});
</script>
@endpush
