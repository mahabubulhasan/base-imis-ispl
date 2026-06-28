@php
    $bins = $bins ?? [];
    $placedVal = old('placed_at_buildings', $wasteBin !== null ? ($wasteBin->placed_at_buildings ? '1' : '0') : '1');
    if ($placedVal === true || $placedVal === 1 || $placedVal === '1') {
        $placedVal = '1';
    } else {
        $placedVal = '0';
    }
    $placedAtBuildingsOptions = ['1' => __('Yes'), '0' => __('No')];
@endphp

<div class="swm-waste-bin-form-mobile app-mobile-form">
<div class="card-body">
    @if ($wasteBin !== null)
    <div class="form-group row">
        {!! Form::label('waste_bin_id_display', __('Waste Bin ID'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="text" class="form-control" id="waste_bin_id_display" value="{{ $wasteBin->waste_bin_id }}" readonly autocomplete="off">
        </div>
    </div>
    @endif
    <div class="form-group row required">
        {!! Form::label('waste_bin_type_id', __('Waste Bin Type'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::select('waste_bin_type_id', $wasteBinTypes, null, ['class' => 'form-control' . ($errors->has('waste_bin_type_id') ? ' is-invalid' : ''), 'id' => 'waste_bin_type_id', 'placeholder' => __('Select type')]) !!}</div>
        @error('waste_bin_type_id')
            <div class="col-sm-6"><span class="text-danger">{{ $message }}</span></div>
        @enderror
    </div>

    <div class="form-group row required">
        {!! Form::label('total_capacity_kg', __('Capacity (kg)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::number('total_capacity_kg', null, ['class' => 'form-control' . ($errors->has('total_capacity_kg') ? ' is-invalid' : ''), 'step' => '0.01', 'min' => 0]) !!}</div>
        @error('total_capacity_kg')
            <div class="col-sm-6"><span class="text-danger">{{ $message }}</span></div>
        @enderror
    </div>

    <div class="form-group row">
        {!! Form::label('placed_at_buildings', __('Placed at Buildings?'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('placed_at_buildings', $placedAtBuildingsOptions, $placedVal, ['class' => 'form-control', 'id' => 'placed_at_buildings']) !!}
        </div>
    </div>

    <div class="form-group row" id="bin-field-row" style="{{ $placedVal === '1' ? '' : 'display: none;' }}">
        {!! Form::label('bin', __('BIN'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('bin', $bins, null, ['class' => 'form-control' . ($errors->has('bin') ? ' is-invalid' : ''), 'id' => 'bin', 'placeholder' => __('Select BIN')]) !!}
            @error('bin')
                <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('sub_location', __('Location'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('sub_location', null, ['class' => 'form-control', 'id' => 'sub_location']) !!}</div>
    </div>

    <div class="form-group row">
        {!! Form::label('ward_no', __('Ward No.'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::select('ward_no', $wards, null, ['class' => 'form-control', 'id' => 'ward_no', 'placeholder' => __('Ward No.')]) !!}</div>
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
            {!! Form::number('latitude', null, ['class' => 'form-control', 'id' => 'latitude', 'step' => 'any', 'min' => -90, 'max' => 90, 'inputmode' => 'decimal', 'placeholder' => __('Decimal Degrees (WGS84)')]) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('longitude', __('Longitude'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('longitude', null, ['class' => 'form-control', 'id' => 'longitude', 'step' => 'any', 'min' => -180, 'max' => 180, 'inputmode' => 'decimal', 'placeholder' => __('Decimal Degrees (WGS84)')]) !!}
        </div>
    </div>
</div>

<div class="card-footer">
    <a href="{{ route('swm.waste-bins.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div>
</div>

@push('scripts')
<script>
(function () {
    var buildingSnapshotUrl = '{!! route('building-info.households.building-snapshot') !!}';

    function setSelectVal($el, val) {
        if (!$el.length) {
            return;
        }
        var empty = val == null || val === '';
        if ($.fn.select2 && $el.hasClass('select2-hidden-accessible')) {
            $el.val(empty ? null : String(val)).trigger('change');
            return;
        }
        $el.val(empty ? '' : String(val));
    }

    function initWasteBinSelect2() {
        if (!$.fn.select2) {
            return;
        }
        $('#waste_bin_type_id').select2({ width: '100%', placeholder: @json(__('Select type')), allowClear: true });
        $('#bin').select2({
            width: '100%',
            placeholder: @json(__('Select BIN')),
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('building-info.households.bin-options') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { search: params.term, page: params.page || 1 };
                },
                cache: true,
            }
        });
        $('#ward_no').select2({ width: '100%', placeholder: @json(__('Ward No.')), allowClear: true });
    }

    function applyBuildingSnapshot(data) {
        if (!data || typeof data !== 'object') {
            return;
        }
        if (data.ward !== undefined && data.ward !== null && data.ward !== '') {
            setSelectVal($('#ward_no'), data.ward);
        }
        $('#road_no').val(data.road_no || '');
        $('#road_name').val(data.road_name || '');
        $('#sub_location').val(data.area_mohalla_name || '');
    }

    function togglePlacedAtBuildings() {
        var on = String($('#placed_at_buildings').val()) === '1';
        $('#bin-field-row').toggle(on);
    }

    $(function () {
        initWasteBinSelect2();
        togglePlacedAtBuildings();
        $('#placed_at_buildings').on('change', togglePlacedAtBuildings);

        $('#bin').on('change', function () {
            var bin = $(this).val();
            if (!bin) {
                return;
            }
            $.getJSON(buildingSnapshotUrl, { bin: bin })
                .done(applyBuildingSnapshot)
                .fail(function () {});
        });
    });
})();
</script>
@endpush
