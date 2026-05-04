@php
    $othersTypeId = $othersWasteBinTypeId ?? null;
    $placedVal = old('placed_at_buildings', $wasteBin !== null ? ($wasteBin->placed_at_buildings ? '1' : '0') : '1');
    if ($placedVal === true || $placedVal === 1 || $placedVal === '1') {
        $placedVal = '1';
    } else {
        $placedVal = '0';
    }
    $placedAtBuildingsOptions = ['1' => __('Yes'), '0' => __('No')];
@endphp
<div class="card-body">
    <div class="form-group row required">
        {!! Form::label('waste_bin_type_id', __('Waste Bin Type'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::select('waste_bin_type_id', $wasteBinTypes, null, ['class' => 'form-control chosen-select', 'id' => 'waste_bin_type_id', 'placeholder' => __('Select type')]) !!}</div>
    </div>
    <div class="form-group row required">
        {!! Form::label('total_capacity_kg', __('Capacity (kg)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::number('total_capacity_kg', null, ['class' => 'form-control', 'step' => '0.01', 'min' => 0]) !!}</div>
    </div>
    <div class="form-group row" id="type-other-detail-row" style="display: none;">
        {!! Form::label('type_other_detail', __('Others (specify)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('type_other_detail', null, ['class' => 'form-control', 'id' => 'type_other_detail']) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('placed_at_buildings', __('Placed at Buildings?'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('placed_at_buildings', $placedAtBuildingsOptions, $placedVal, ['class' => 'form-control', 'id' => 'placed_at_buildings']) !!}
        </div>
    </div>
    <div class="form-group row" id="bin-field-row" style="{{ $placedVal === '1' ? '' : 'display: none;' }}">
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
            
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('longitude', __('Longitude'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('longitude', null, ['class' => 'form-control', 'id' => 'longitude', 'step' => 'any', 'min' => -180, 'max' => 180, 'inputmode' => 'decimal', 'placeholder' => __('Decimal degrees (WGS84)')]) !!}
            
        </div>
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

    function toggleTypeOther() {
        if (!othersTypeId) return;
        var v = $('#waste_bin_type_id').val();
        var show = String(v) === String(othersTypeId);
        $('#type-other-detail-row').toggle(show);
        if (!show) $('#type_other_detail').val('');
    }

    function togglePlacedAtBuildings() {
        var on = String($('#placed_at_buildings').val()) === '1';
        $('#bin-field-row').toggle(on);
    }

    $(function () {
        toggleTypeOther();
        togglePlacedAtBuildings();
        $('#waste_bin_type_id').on('change', toggleTypeOther);
        $('#placed_at_buildings').on('change', togglePlacedAtBuildings);
    });
})();
</script>
@endpush
