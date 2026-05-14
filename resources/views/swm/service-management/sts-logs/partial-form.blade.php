@push('style')
<style>
@media (max-width: 991.98px) {
    .swm-sts-log-form-mobile.app-mobile-form .select2-container {
        width: 100% !important;
    }
}
</style>
@endpush
@php
    $isEdit = isset($stsLog) && $stsLog;
    $vehicleFieldVal = old('vehicle_id', $isEdit ? $stsLog->vehicle_id : null);
    $entryVal = old('entry_at');
    if ($entryVal === null && $isEdit && $stsLog->entry_at) {
        $entryVal = $stsLog->entry_at->format('Y-m-d\TH:i');
    }
    if ($entryVal === null && ! $isEdit) {
        $entryVal = \Carbon\Carbon::now()->format('Y-m-d\TH:i');
    }
    $opDateVal = old('operation_date', $isEdit && $stsLog->operation_date ? $stsLog->operation_date->format('Y-m-d') : \Carbon\Carbon::now()->format('Y-m-d'));
    $statusVal = old('operation_status', $isEdit ? $stsLog->operation_status : \App\Models\Swm\StsLog::STATUS_PENDING);
    $wasteTypeIdsOld = old('waste_type_ids');
    if (is_array($wasteTypeIdsOld)) {
        $wasteTypeIdsForField = array_values(array_unique(array_filter(
            array_map(static fn ($v) => (int) $v, $wasteTypeIdsOld),
            static fn ($v) => $v > 0
        )));
    } elseif ($isEdit) {
        $wasteTypeIdsForField = is_array($stsLog->waste_type_ids) && count($stsLog->waste_type_ids)
            ? $stsLog->waste_type_ids
            : ($stsLog->waste_type_id ? [$stsLog->waste_type_id] : []);
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
    $wardsArr = old('source_wards', $isEdit && is_array($stsLog->source_wards) ? $stsLog->source_wards : []);
    $wardsArr = array_values(array_unique(array_filter(
        array_map(static fn ($v) => is_scalar($v) ? (string) $v : null, (array) $wardsArr),
        static fn ($v) => $v !== null && $v !== ''
    )));
@endphp
<div class="swm-sts-log-form-mobile app-mobile-form">
<div class="card-body">
    @if($isEdit)
        <div class="form-group row">
            <label class="col-sm-3 control-label">{{ __('STS Log ID') }}</label>
            <div class="col-sm-9">
                <p class="form-control-plaintext">{{ $stsLog->id }}</p>
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
            {!! Form::text('vehicle_type_name', old('vehicle_type_name', $isEdit ? $stsLog->vehicle_type_name : null), ['class' => 'form-control', 'id' => 'vehicle_type_name', 'placeholder' => __('Vehicle Type'), 'autocomplete' => 'off']) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('driver_name', __('Driver Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::text('driver_name', old('driver_name', $isEdit ? $stsLog->driver_name : null), ['class' => 'form-control', 'id' => 'driver_name', 'placeholder' => __('Driver Name'), 'autocomplete' => 'off']) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('sts_id', __('STS Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::select('sts_id', ['' => __('Select STS')] + $stsList, old('sts_id', $isEdit ? $stsLog->sts_id : null), ['class' => 'form-control swm-sts-select2', 'id' => 'sts_id', 'style' => 'width:100%', 'data-placeholder' => __('Search or select STS')]) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('waste_type_ids', __('Waste Types'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            <select name="waste_type_ids[]" id="waste_type_ids" class="form-control" multiple style="width:100%" data-placeholder="{{ __('Search or select waste types') }}"></select>
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('quantity_ton', __('Quantity (Ton)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            <input type="number" name="quantity_ton" id="quantity_ton" class="form-control" step="0.001" min="0"
                value="{{ old('quantity_ton', $isEdit && $stsLog->quantity_ton !== null ? $stsLog->quantity_ton : null) }}"
                placeholder="{{ __('Quantity in tons') }}" />
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('source_wards', __('Source Wards'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::select('source_wards[]', $wardOptions, $wardsArr, ['class' => 'form-control', 'id' => 'source_wards', 'multiple' => true, 'data-placeholder' => __('Source Wards')]) !!}
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
            {!! Form::textarea('remarks', old('remarks', $isEdit ? $stsLog->remarks : null), ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Remarks')]) !!}
        </div>
    </div>
</div>
<div class="card-footer">
    <a href="{{ route('swm.sts-logs.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div>
</div>

@push('scripts')
<script>
$(function () {
    var vehiclesUrl = @json(route('swm.sts-logs.suggestions.vehicles'));
    var wasteTypesUrl = @json(route('swm.sts-logs.suggestions.waste-types'));
    var vehicleContextUrl = @json(route('swm.sts-logs.vehicle-context'));
    var stsContextUrl = @json(route('swm.sts-logs.sts-context'));
    var csrf = @json(csrf_token());
    var initialVehicleId = @json($vehicleFieldVal ? (string) $vehicleFieldVal : null);
    var initialVehicleText = @json($isEdit && isset($stsLog) && $stsLog->vehicle ? ($stsLog->vehicle->vehicle_number ?: '') : null);
    var initialWasteForJs = @json($initialWasteForJs);
    var stsSkipContextOneShot = false;

    function setWardsFromArray(arr) {
        var clean = Array.isArray(arr) ? arr.map(function (v) { return String(v).trim(); }).filter(function (v) { return v.length > 0; }) : [];
        var $sw = $('#source_wards');
        $sw.val(clean).trigger('change');
    }

    function initSourceWardsSelect2() {
        var $sw = $('#source_wards');
        if (!$sw.length || !$.fn.select2) {
            return;
        }
        if ($sw.data('select2')) {
            $sw.select2('destroy');
        }
        $sw.select2({
            placeholder: $sw.data('placeholder') || @json(__('Source Wards')),
            width: '100%',
            closeOnSelect: false,
            allowClear: true
        });
    }

    initSourceWardsSelect2();

    function initStsSelect2() {
        var $s = $('#sts_id');
        if (!$s.length || !$.fn.select2) {
            return;
        }
        if ($s.data('chosen')) {
            try {
                $s.chosen('destroy');
            } catch (e) { /* ignore */ }
        }
        if ($s.data('select2')) {
            $s.select2('destroy');
        }
        $s.select2({
            width: '100%',
            placeholder: $s.data('placeholder') || @json(__('Search or select STS')),
            allowClear: true
        });
    }

    initStsSelect2();

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

    function applyVehicleContext(data) {
        if (!data || data.error) {
            return;
        }
        $('#vehicle_type_name').val(data.vehicle_type_name != null ? String(data.vehicle_type_name) : '');
        $('#driver_name').val(data.driver_name != null ? String(data.driver_name) : '');
        if (data.capacity != null && data.capacity !== '') {
            $('#quantity_ton').val(data.capacity);
        }
        if (data.sts_id) {
            stsSkipContextOneShot = true;
            $('#sts_id').val(String(data.sts_id)).trigger('change');
        }
        if (Array.isArray(data.source_wards)) {
            setWardsFromArray(data.source_wards);
        }
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
        }).done(applyVehicleContext).fail(function (xhr) {
            if (window.console && console.warn) {
                console.warn('vehicle-context failed', xhr.status, xhr.responseJSON || xhr.responseText);
            }
        });
    }

    function fetchStsContext() {
        var sid = $('#sts_id').val();
        if (!sid) {
            return;
        }
        $.ajax({
            url: stsContextUrl,
            dataType: 'json',
            data: { sts_id: sid },
            headers: { 'X-CSRF-TOKEN': csrf }
        }).done(function (data) {
            if (!data || data.error) {
                return;
            }
            setWasteTypesFromContext(data);
            if (Array.isArray(data.source_wards)) {
                setWardsFromArray(data.source_wards);
            }
        }).fail(function (xhr) {
            if (window.console && console.warn) {
                console.warn('sts-context failed', xhr.status, xhr.responseJSON || xhr.responseText);
            }
        });
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
            $v.append(opt).trigger('change.skip-context');
            initialVehicleId = null;
            initialVehicleText = null;
        }
    }

    initVehicleSelect2();

    $('#vehicle_id').on('change', function (e, extra) {
        if (e.namespace === 'skip-context') {
            return;
        }
        fetchVehicleContext();
    });

    $('#sts_id').on('change', function () {
        if (stsSkipContextOneShot) {
            stsSkipContextOneShot = false;
            return;
        }
        var sid = $('#sts_id').val();
        if (!sid) {
            setWasteTypesFromContext({ waste_type_ids: [], waste_types: [] });
            return;
        }
        fetchStsContext();
    });
});
</script>
@endpush
