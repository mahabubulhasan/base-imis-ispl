<div class="app-mobile-form">
<div class="card-body">
        @if(optional($sts)->id)
        <div class="form-group row">
            {!! Form::label('sts_id_display', __('STS ID'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('sts_id_display', $sts->sts_id, ['class' => 'form-control', 'readonly' => true]) !!}
            </div>
        </div>
        @endif
        <div class="form-group row required">
            {!! Form::label('name', __('STS Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('STS Name')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('location', __('Location'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('location', null, ['class' => 'form-control', 'placeholder' => __('Location')]) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('ward_no', __('Ward No.'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('ward_no', $wards, null, ['class' => 'form-control chosen-select', 'id' => 'ward_no', 'placeholder' => __('Ward No.')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('road_id', __('Road ID'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('road_id', optional($sts)->road_id ? [$sts->road_id => $sts->road_id] : [], null, ['class' => 'form-control chosen-select', 'id' => 'road_id', 'placeholder' => __('Road ID')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('road_name', __('Road Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::text('road_name', null, ['class' => 'form-control', 'id' => 'road_name', 'placeholder' => __('Road Name')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('latitude', __('Latitude'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('latitude', null, ['class' => 'form-control', 'placeholder' => __('Latitude'), 'step' => 'any', 'min' => -90, 'max' => 90, 'inputmode' => 'decimal']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('longitude', __('Longitude'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('longitude', null, ['class' => 'form-control', 'placeholder' => __('Longitude'), 'step' => 'any', 'min' => -180, 'max' => 180, 'inputmode' => 'decimal']) !!}
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
            {!! Form::label('area', __('Area') . ' (' . __('Decimal') . ')', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::number('area', null, ['class' => 'form-control', 'placeholder' => __('Area'), 'min' => 0, 'step' => '0.01', 'inputmode' => 'decimal']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('source_wards', __('Source Wards'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('source_wards[]', $wards, optional($sts)->source_wards, ['class' => 'form-control', 'id' => 'source_wards', 'multiple' => true, 'data-placeholder' => __('Source Wards')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('waste_type_ids', __('Waste Types'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('waste_type_ids[]', $wasteTypes, optional($sts)->waste_type_ids, ['class' => 'form-control', 'id' => 'waste_type_ids', 'multiple' => true, 'data-placeholder' => __('Waste Types')]) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('segregation_practiced', __('Segregation Practiced?'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('segregation_practiced', [1 => __('Yes'), 0 => __('No')], old('segregation_practiced', optional($sts)->segregation_practiced ? 1 : 0), ['class' => 'form-control', 'id' => 'segregation_practiced']) !!}
            </div>
        </div>
        <div class="form-group row">
            {!! Form::label('destination_landfill_id', __('Destination Landfill'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('destination_landfill_id', $landfills, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Destination Landfill')]) !!}
            </div>
        </div>
        <div class="form-group row required">
            {!! Form::label('operational_status', __('Operational Status'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::select('operational_status', ['active' => __('Active'), 'inactive' => __('Inactive')], null, ['class' => 'form-control', 'placeholder' => __('Operational Status')]) !!}
            </div>
        </div>
</div>
<div class="card-footer">
	<a href="{{ route('swm.sts.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
	{!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>
</div>
@push('scripts')
<script>
$(function() {
    var roadsForWardUrl = '{!! route("swm.sts.roads-for-ward") !!}';
    var $wardSelect = $('#ward_no');
    var $roadSelect = $('#road_id');
    var $roadName = $('#road_name');
    var $sourceWardsSelect = $('#source_wards');
    var $wasteTypesSelect = $('#waste_type_ids');
    var roadsCache = {};

    if ($.fn.select2 && $sourceWardsSelect.length) {
        $sourceWardsSelect.select2({
            placeholder: $sourceWardsSelect.data('placeholder') || '{{ __("Source Wards") }}',
            width: '100%',
            closeOnSelect: false
        });
    }

    if ($.fn.select2 && $wasteTypesSelect.length) {
        $wasteTypesSelect.select2({
            placeholder: $wasteTypesSelect.data('placeholder') || '{{ __("Waste Types") }}',
            width: '100%',
            closeOnSelect: false
        });
    }

    function refreshRoads(ward, selectedCode) {
        $roadSelect.empty();
        $roadSelect.append($('<option/>', { value: '', text: '{{ __("Road ID") }}' }));
        if (!ward) {
            $roadSelect.trigger('chosen:updated');
            return;
        }
        var apply = function (roads) {
            roadsCache[ward] = roads;
            $.each(roads, function (_, r) {
                var opt = $('<option/>', { value: r.code, text: r.code + (r.name ? ' - ' + r.name : '') });
                opt.data('name', r.name || '');
                if (selectedCode && String(r.code) === String(selectedCode)) {
                    opt.attr('selected', 'selected');
                }
                $roadSelect.append(opt);
            });
            if (selectedCode && !$roadSelect.find('option[value="' + selectedCode + '"]').length) {
                $roadSelect.append($('<option/>', { value: selectedCode, text: selectedCode, selected: true }));
            }
            $roadSelect.trigger('chosen:updated');
        };
        if (roadsCache[ward]) {
            apply(roadsCache[ward]);
            return;
        }
        $.getJSON(roadsForWardUrl, { ward_no: ward }, function (resp) {
            apply(Array.isArray(resp) ? resp : []);
        });
    }

    var initialWard = $wardSelect.val();
    var initialRoad = $roadSelect.val();
    if (initialWard) {
        refreshRoads(initialWard, initialRoad);
    }

    $wardSelect.on('change', function () {
        refreshRoads($(this).val(), null);
    });

    $roadSelect.on('change', function () {
        var name = $roadSelect.find('option:selected').data('name') || '';
        if (name) {
            $roadName.val(name);
        }
    });
});
</script>
@endpush
