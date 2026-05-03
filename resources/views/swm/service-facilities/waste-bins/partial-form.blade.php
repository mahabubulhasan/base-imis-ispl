@php
    $othersTypeId = $othersWasteBinTypeId ?? null;
    $placedDefault = old('placed_at_buildings', $wasteBin ? $wasteBin->placed_at_buildings : true);
    if (is_string($placedDefault)) {
        $placedDefault = $placedDefault === '1' || $placedDefault === 'true';
    }
@endphp
<div class="card-body">
    <div class="form-group row required">
        {!! Form::label('household_id', __('Household'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::select('household_id', $households, null, ['class' => 'form-control chosen-select', 'id' => 'household_id', 'placeholder' => __('Select Household')]) !!}</div>
    </div>
    <div class="form-group row required">
        {!! Form::label('waste_bin_type_id', __('Type of Waste Bin'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::select('waste_bin_type_id', $wasteBinTypes, null, ['class' => 'form-control chosen-select', 'id' => 'waste_bin_type_id', 'placeholder' => __('Select type')]) !!}</div>
    </div>
    <div class="form-group row" id="type-other-detail-row" style="display: none;">
        {!! Form::label('type_other_detail', __('Others (specify)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('type_other_detail', null, ['class' => 'form-control', 'id' => 'type_other_detail']) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('placed_at_buildings', __('Placed at Buildings') . ' (' . __('Yes') . '/' . __('No') . ')', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 pt-2">
            <input type="hidden" name="placed_at_buildings" value="0">
            {!! Form::checkbox('placed_at_buildings', '1', (bool) $placedDefault, ['id' => 'placed_at_buildings']) !!}
        </div>
    </div>
    <div class="form-group row" id="bin-field-row">
        {!! Form::label('bin', __('BIN'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('bin', null, ['class' => 'form-control', 'id' => 'bin']) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('sub_location', __('Sub Location'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('sub_location', null, ['class' => 'form-control', 'id' => 'sub_location']) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('ward_no', __('Ward No.'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::select('ward_no', $wards, null, ['class' => 'form-control chosen-select', 'id' => 'ward_no', 'placeholder' => __('Ward No.')]) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('road_no', __('Road No.'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('road_no', null, ['class' => 'form-control', 'id' => 'road_no']) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('road_name', __('Road Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('road_name', null, ['class' => 'form-control', 'id' => 'road_name']) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('latitude', __('Latitude'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('latitude', null, ['class' => 'form-control', 'id' => 'latitude', 'step' => 'any', 'min' => -90, 'max' => 90, 'inputmode' => 'decimal', 'placeholder' => __('Decimal degrees (WGS84)')]) !!}
            <small class="form-text text-muted">{{ __('Enter decimal degrees (WGS84).') }}</small>
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('longitude', __('Longitude'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('longitude', null, ['class' => 'form-control', 'id' => 'longitude', 'step' => 'any', 'min' => -180, 'max' => 180, 'inputmode' => 'decimal', 'placeholder' => __('Decimal degrees (WGS84)')]) !!}
            <small class="form-text text-muted">{{ __('Enter decimal degrees (WGS84).') }}</small>
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('total_capacity_kg', __('Capacity (kg)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::number('total_capacity_kg', null, ['class' => 'form-control', 'step' => '0.01', 'min' => 0]) !!}</div>
    </div>
</div>
<div class="card-footer">
    <a href="{{ route('swm.waste-bins.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div>
@push('scripts')
<script>
(function () {
    var othersTypeId = @json($othersTypeId);
    var householdFieldsUrl = @json(route('swm.waste-bins.household-fields'));

    function toggleTypeOther() {
        if (!othersTypeId) return;
        var v = $('#waste_bin_type_id').val();
        var show = String(v) === String(othersTypeId);
        $('#type-other-detail-row').toggle(show);
        if (!show) $('#type_other_detail').val('');
    }

    function togglePlacedAtBuildings() {
        var on = $('#placed_at_buildings').is(':checked');
        $('#bin-field-row').toggleClass('required', on);
    }

    function refreshFromHousehold() {
        var id = $('#household_id').val();
        if (!id) return;
        $.getJSON(householdFieldsUrl, { household_id: id })
            .done(function (d) {
                if (!d) return;
                $('#sub_location').val(d.sub_location || '');
                if (d.ward_no !== null && d.ward_no !== undefined && d.ward_no !== '') {
                    $('#ward_no').val(String(d.ward_no)).trigger('chosen:updated');
                }
                $('#road_name').val(d.road_name || '');
                $('#road_no').val(d.road_no || '');
                if ($('#placed_at_buildings').is(':checked')) {
                    $('#bin').val(d.bin || '');
                }
            });
    }

    $(function () {
        toggleTypeOther();
        togglePlacedAtBuildings();
        $('#waste_bin_type_id').on('change', toggleTypeOther);
        $('#placed_at_buildings').on('change', togglePlacedAtBuildings);
        $('#household_id').on('change', refreshFromHousehold);
    });
})();
</script>
@endpush
