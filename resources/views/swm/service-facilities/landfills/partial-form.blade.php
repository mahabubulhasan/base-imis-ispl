<div class="app-mobile-form">
<div class="card-body">
        @if(optional($landfill)->landfill_id)
        <div class="form-group row">
            {!! Form::label('landfill_id', __('Landfill ID'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('landfill_id_display', $landfill->landfill_id, ['class' => 'form-control', 'readonly' => true]) !!}
            </div>
        </div>
        @endif
        <div class="form-group row required">
            {!! Form::label('name', __('Landfill Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Landfill Name')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('location', __('Location'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('location', null, ['class' => 'form-control', 'placeholder' => __('Location')]) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('operator_name', __('Operator Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('operator_name', null, ['class' => 'form-control', 'placeholder' => __('Operator Name')]) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('contact_number', __('Operator\'s Contact Number'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('contact_number', null, ['class' => 'form-control', 'placeholder' => __('Operator\'s Contact Number')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('capacity', __('Capacity') . ' (' . __('Ton') . ')', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('capacity', null, ['class' => 'form-control', 'placeholder' => __('Capacity'), 'min' => 0, 'step' => '0.01', 'inputmode' => 'decimal']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('area', __('Area') . ' (' . __('Acres') . ')', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('area', null, ['class' => 'form-control', 'placeholder' => __('Area'), 'min' => 0, 'step' => '0.01', 'inputmode' => 'decimal']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('landfill_type_id', __('Landfill Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('landfill_type_id', ['' => __('Select Landfill Type')] + ($landfillTypes ?? []), old('landfill_type_id', optional($landfill)->landfill_type_id), ['class' => 'form-control', 'id' => 'landfill_type_id']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('source_sts_ids', __('Source STSs'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('source_sts_ids[]', $stsOptions, optional($landfill)->source_sts_ids, ['class' => 'form-control', 'id' => 'source_sts_ids', 'multiple' => true, 'data-placeholder' => __('Source STS')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('sts_source_wards_display', __('STS Source Wards'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                <span id="sts_source_wards_display" class="form-control bg-light" style="min-height: 38px; height: auto;">—</span>
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('source_wards', __('Other Source Wards'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('source_wards[]', $wards ?? [], old('source_wards', optional($landfill)->source_wards), ['class' => 'form-control', 'id' => 'source_wards', 'multiple' => true, 'data-placeholder' => __('Other Source Wards')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('waste_type_ids', __('Waste Type'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('waste_type_ids[]', $wasteTypes, optional($landfill)->waste_type_ids, ['class' => 'form-control', 'id' => 'waste_type_ids', 'multiple' => true, 'data-placeholder' => __('Waste Type')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('segregation_practiced', __('Segregation Practiced?'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('segregation_practiced', ['' => __('Select')] + [1 => __('Yes'), 0 => __('No')], old('segregation_practiced', optional($landfill)->segregation_practiced), ['class' => 'form-control', 'id' => 'segregation_practiced']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('weighbridge_facility_available', __('Weighbridge Facility Available?'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('weighbridge_facility_available', ['' => __('Select')] + [1 => __('Yes'), 0 => __('No')], old('weighbridge_facility_available', optional($landfill)->weighbridge_facility_available), ['class' => 'form-control', 'id' => 'weighbridge_facility_available']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('boundary_wall_available', __('Boundary Wall Around the Landfill Area Available?'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('boundary_wall_available', ['' => __('Select')] + [1 => __('Yes'), 0 => __('No')], old('boundary_wall_available', optional($landfill)->boundary_wall_available), ['class' => 'form-control', 'id' => 'boundary_wall_available']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('lighting_arrangement_available', __('Lighting Arrangement at the Landfill Site Available?'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('lighting_arrangement_available', ['' => __('Select')] + [1 => __('Yes'), 0 => __('No')], old('lighting_arrangement_available', optional($landfill)->lighting_arrangement_available), ['class' => 'form-control', 'id' => 'lighting_arrangement_available']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('manpower_deployed', __('Number of Manpower Deployed at the Landfill Site'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('manpower_deployed', old('manpower_deployed', optional($landfill)->manpower_deployed), ['class' => 'form-control', 'placeholder' => __('Number of Manpower Deployed at the Landfill Site'), 'min' => 0, 'step' => '1', 'inputmode' => 'numeric']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('adequate_covering_arrangement_available', __('Adequate Covering Arrangement at the Landfill Site Available?'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('adequate_covering_arrangement_available', ['' => __('Select')] + [1 => __('Yes'), 0 => __('No')], old('adequate_covering_arrangement_available', optional($landfill)->adequate_covering_arrangement_available), ['class' => 'form-control', 'id' => 'adequate_covering_arrangement_available']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('gas_control_system_available', __('System for Gas Control from the Filled Landfill Available?'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('gas_control_system_available', ['' => __('Select')] + [1 => __('Yes'), 0 => __('No')], old('gas_control_system_available', optional($landfill)->gas_control_system_available), ['class' => 'form-control', 'id' => 'gas_control_system_available']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('leachate_collection_system_available', __('Leachate Collection System Available?'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('leachate_collection_system_available', ['' => __('Select')] + [1 => __('Yes'), 0 => __('No')], old('leachate_collection_system_available', optional($landfill)->leachate_collection_system_available), ['class' => 'form-control', 'id' => 'leachate_collection_system_available']) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('operational_status', __('Operational Status'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('operational_status', ['active' => __('Active'), 'inactive' => __('Inactive')], old('operational_status', optional($landfill)->operational_status ?? 'active'), ['class' => 'form-control', 'placeholder' => __('Operational Status')]) !!}
            </div>
        </div>
</div>
<div class="card-footer">
	<a href="{{ route('swm.landfills.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
	{!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>
</div>
@push('scripts')
<script>
$(function () {
    var $sourceStsSelect = $('#source_sts_ids');
    var $sourceWardsSelect = $('#source_wards');
    var $wasteTypesSelect = $('#waste_type_ids');
    var stsSourceWardsMap = @json($stsSourceWardsMap ?? []);

    function initSelect2($el, fallbackPlaceholder) {
        if (!$.fn.select2 || !$el.length) {
            return;
        }
        $el.select2({
            placeholder: $el.data('placeholder') || fallbackPlaceholder,
            width: '100%',
            closeOnSelect: false
        });
    }

    /** Union of source_wards across all selected STS ids (deduplicated). */
    function unionWardsFromStsIds(stsIds) {
        var wardSet = {};
        stsIds.forEach(function (id) {
            var wards = stsSourceWardsMap[String(id)] || stsSourceWardsMap[id] || [];
            wards.forEach(function (ward) {
                var key = String(ward).trim();
                if (key !== '') {
                    wardSet[key] = true;
                }
            });
        });
        return Object.keys(wardSet).sort(function (a, b) {
            return parseInt(a, 10) - parseInt(b, 10);
        });
    }

    var $stsSourceWardsDisplay = $('#sts_source_wards_display');

    function updateStsSourceWardsDisplay() {
        var wards = unionWardsFromStsIds($sourceStsSelect.val() || []);
        $stsSourceWardsDisplay.text(wards.length ? wards.join(', ') : '—');
    }

    initSelect2($sourceStsSelect, '{{ __("Source STS") }}');
    initSelect2($sourceWardsSelect, '{{ __("Other Source Wards") }}');
    initSelect2($wasteTypesSelect, '{{ __("Waste Type") }}');

    $sourceStsSelect.on('change', updateStsSourceWardsDisplay);
    updateStsSourceWardsDisplay();
});
</script>
@endpush
