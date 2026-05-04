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
            {!! Form::label('name', __('Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Name')]) !!}
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
            {!! Form::label('contact_number', __('Contact Number'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('contact_number', null, ['class' => 'form-control', 'placeholder' => __('Contact Number')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('capacity', __('Capacity') . ' (' . __('Ton') . ')', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('capacity', null, ['class' => 'form-control', 'placeholder' => __('Capacity'), 'min' => 0, 'step' => '0.01', 'inputmode' => 'decimal']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('area', __('Area') . ' (' . __('Decimal') . ')', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('area', null, ['class' => 'form-control', 'placeholder' => __('Area'), 'min' => 0, 'step' => '0.01', 'inputmode' => 'decimal']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('source_sts_ids', __('Source STSs'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('source_sts_ids[]', $stsOptions, optional($landfill)->source_sts_ids, ['class' => 'form-control', 'id' => 'source_sts_ids', 'multiple' => true, 'data-placeholder' => __('Source STS')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('source_wards', __('Source Wards'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('source_wards[]', $wards ?? [], old('source_wards', optional($landfill)->source_wards), ['class' => 'form-control', 'id' => 'source_wards', 'multiple' => true, 'data-placeholder' => __('Source Wards')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('waste_type_ids', __('Waste Types'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('waste_type_ids[]', $wasteTypes, optional($landfill)->waste_type_ids, ['class' => 'form-control', 'id' => 'waste_type_ids', 'multiple' => true, 'data-placeholder' => __('Waste Types')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('segregation_practiced', __('Segregation Practiced?'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('segregation_practiced', [1 => __('Yes'), 0 => __('No')], old('segregation_practiced', optional($landfill)->segregation_practiced ? 1 : 0), ['class' => 'form-control', 'id' => 'segregation_practiced']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('reuse_practiced', __('Reuse Practiced?'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('reuse_practiced', [1 => __('Yes'), 0 => __('No')], old('reuse_practiced', optional($landfill)->reuse_practiced ? 1 : 0), ['class' => 'form-control', 'id' => 'reuse_practiced']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('treatment', __('Treatment Practiced?'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('treatment', [1 => __('Yes'), 0 => __('No')], old('treatment', optional($landfill)->treatment ? 1 : 0), ['class' => 'form-control', 'id' => 'treatment']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('monthly_waste_for_composting', __('Monthly Waste for Composting') . ' (' . __('Ton') . ')', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('monthly_waste_for_composting', null, [
                    'class' => 'form-control',
                    'placeholder' => __('Monthly Waste for Composting') . ' (' . __('Ton') . ')',
                    'min' => 0,
                    'step' => '0.01',
                    'inputmode' => 'decimal',
                ]) !!}
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
@push('scripts')
<script>
$(function () {
    var $sourceStsSelect = $('#source_sts_ids');
    var $sourceWardsSelect = $('#source_wards');
    var $wasteTypesSelect = $('#waste_type_ids');
    var wardsForStsUrl = '{!! route("swm.landfills.wards-for-sts") !!}';

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

    function syncWardsFromSelectedSts() {
        var selectedStsIds = $sourceStsSelect.val() || [];
        if (!selectedStsIds.length) {
            $sourceWardsSelect.val([]).trigger('change');
            return;
        }

        $.getJSON(wardsForStsUrl, { source_sts_ids: selectedStsIds }, function (resp) {
            var wards = Array.isArray(resp) ? resp : [];
            var wardValues = wards.map(function (w) { return String(w); });
            $sourceWardsSelect.val(wardValues).trigger('change');
        });
    }

    initSelect2($sourceStsSelect, '{{ __("Source STS") }}');
    initSelect2($sourceWardsSelect, '{{ __("Source Wards") }}');
    initSelect2($wasteTypesSelect, '{{ __("Waste Types") }}');

    $sourceStsSelect.on('change', syncWardsFromSelectedSts);
});
</script>
@endpush
