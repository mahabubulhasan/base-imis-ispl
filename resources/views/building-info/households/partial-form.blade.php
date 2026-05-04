@push('style')
<style>
@media (max-width: 991.98px) {
    .household-form-mobile.app-mobile-form .pt-2 {
        padding-top: 0 !important;
    }

    .household-form-mobile #waste-bins-table th,
    .household-form-mobile #waste-bins-table td {
        white-space: nowrap;
    }

    .household-form-mobile #waste-bins-table .waste-bin-remove {
        min-height: 38px;
    }
}
</style>
@endpush
<div class="household-form-mobile app-mobile-form">
<div class="card-body">
    @php
        $yesNoOptions = [0 => __('No'), 1 => __('Yes')];
    @endphp
    <div class="form-group row required">
        {!! Form::label('household_id', __('Household ID'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('household_id', null, ['class' => 'form-control', 'placeholder' => __('Household ID')]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('household_owner_name', __('Household Owner Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('household_owner_name', null, ['class' => 'form-control', 'placeholder' => __('Household Owner Name')]) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('father_or_husband_name', __("Father's/Husband's Name"), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('father_or_husband_name', null, ['class' => 'form-control', 'placeholder' => __("Father's/Husband's Name")]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('contact_number', __('Contact Number'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('contact_number', null, ['class' => 'form-control', 'placeholder' => __('Contact Number')]) !!}
        </div>
    </div>
    <!-- <div class="form-group row">
        {!! Form::label('area_mohalla_name', __('Area / Mohalla Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('area_mohalla_name', null, ['class' => 'form-control', 'placeholder' => __('Area / Mohalla Name')]) !!}
        </div>
    </div> -->
    <div class="form-group row">
        {!! Form::label('sub_location', __('Sub Location'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('sub_location', null, ['class' => 'form-control', 'placeholder' => __('Sub Location')]) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('bin', __('BIN'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('bin', $bins, null, ['class' => 'form-control chosen-select', 'id' => 'bin', 'placeholder' => __('Select BIN')]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('ward', __('Ward'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('ward', null, ['class' => 'form-control', 'id' => 'ward']) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('road_no', __('Road No.'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('road_no', null, ['class' => 'form-control', 'id' => 'road_no']) !!}</div>
    </div>
    <div class="form-group row required">
        {!! Form::label('road_name', __('Road Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('road_name', null, ['class' => 'form-control', 'id' => 'road_name']) !!}</div>
    </div>
    <div class="form-group row required">
        {!! Form::label('holding_number', __('Holding Number'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('holding_number', null, ['class' => 'form-control', 'id' => 'holding_number']) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('tax_id', __('Tax ID'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('tax_id', null, ['class' => 'form-control', 'id' => 'tax_id']) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('functional_use', __('Functional Use'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('functional_use', $functionalUses, null, ['class' => 'form-control chosen-select', 'id' => 'functional_use', 'placeholder' => __('Select Functional Use')]) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('waste_charge', __('Waste Collection Fee') . ' (' . __('Taka') . '/' . __('Month') . ')', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::number('waste_charge', null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0']) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('number_of_family_members', __('Number of Family Members'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::number('number_of_family_members', null, ['class' => 'form-control', 'min' => 0]) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('using_this_service_since', __('Using This Service Since'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::date('using_this_service_since', optional(old('using_this_service_since', optional($household)->using_this_service_since))->format('Y-m-d'), ['class' => 'form-control']) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('daily_waste_volume', __('Avg Waste Collected') . ' (' . __('Kg') . '/' . __('Day') . ')', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::number('daily_waste_volume', null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0']) !!}</div>
    </div>
    
    <div class="form-group row">
        {!! Form::label('van_puller_id', __('Van Puller'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::select('van_puller_id', $vanPullers, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Select Van Puller')]) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('is_owner', __('Building Owner?'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::select('is_owner', $yesNoOptions, old('is_owner', optional($household)->is_owner ? 1 : 0), ['class' => 'form-control', 'id' => 'is_owner']) !!}</div>
    </div>
    <div class="form-group row" id="waste-bin-provided-row">
        {!! Form::label('waste_bin_provided', __('Waste Bin Provided?'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::select('waste_bin_provided', $yesNoOptions, old('waste_bin_provided', optional($household)->waste_bin_provided ? 1 : 0), ['class' => 'form-control', 'id' => 'waste_bin_provided']) !!}</div>
    </div>
    @php
        $wasteBinTypes = $wasteBinTypes ?? [];
        $wasteBinRows = old('waste_bins');
        if ($wasteBinRows === null && isset($household) && $household) {
            $wasteBinRows = $household->wasteBins->map(function ($b) {
                return [
                    'id' => $b->id,
                    'waste_bin_type_id' => $b->waste_bin_type_id,
                    'total_capacity_kg' => $b->total_capacity_kg,
                ];
            })->values()->all();
        }
        if (! is_array($wasteBinRows) || count($wasteBinRows) === 0) {
            $wasteBinRows = [[
                'id' => null,
                'waste_bin_type_id' => null,
                'total_capacity_kg' => null,
            ]];
        }
    @endphp
    <div id="waste-bin-fields" style="display: none;">
        <div class="form-group row">
            <label class="col-sm-3 control-label">{{ __('Waste Bins') }}</label>
            <div class="col-sm-9">
                <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0" id="waste-bins-table">
                    <thead>
                        <tr>
                            <th style="min-width: 12rem;">{{ __('Waste Bin Type') }}</th>
                            <th style="min-width: 8rem;">{{ __('Capacity (kg)') }}</th>
                            <th class="text-nowrap" style="width: 6rem;">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="waste-bins-tbody">
                    @foreach ($wasteBinRows as $i => $wbRow)
                        <tr class="waste-bin-row">
                            <td class="align-middle">
                                @if (! empty($wbRow['id']))
                                    <input type="hidden" name="waste_bins[{{ $i }}][id]" value="{{ $wbRow['id'] }}">
                                @endif
                                <select name="waste_bins[{{ $i }}][waste_bin_type_id]" class="form-control waste-bin-type-select">
                                    <option value="">{{ __('Select type') }}</option>
                                    @foreach ($wasteBinTypes as $tid => $tname)
                                        <option value="{{ $tid }}" {{ (string) old('waste_bins.'.$i.'.waste_bin_type_id', $wbRow['waste_bin_type_id'] ?? '') === (string) $tid ? 'selected' : '' }}>{{ $tname }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="align-middle">
                                <input type="number" name="waste_bins[{{ $i }}][total_capacity_kg]" class="form-control waste-bin-capacity-input" step="0.01" min="0.01" value="{{ old('waste_bins.'.$i.'.total_capacity_kg', $wbRow['total_capacity_kg'] ?? '') }}">
                            </td>
                            <td class="align-middle text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger waste-bin-remove">{{ __('Remove') }}</button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
                <button type="button" class="btn btn-sm btn-info mt-2" id="waste-bin-add-row">{{ __('Add waste bin') }}</button>
            </div>
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('is_lic', __('LIC?'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::select('is_lic', $yesNoOptions, old('is_lic', optional($household)->is_lic ? 1 : 0), ['class' => 'form-control', 'id' => 'is_lic']) !!}</div>
    </div>
    <div class="form-group row" id="lic-id-row">
        {!! Form::label('lic_id', __('LIC ID'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::select('lic_id', $licOptions, null, ['class' => 'form-control chosen-select', 'id' => 'lic_id', 'placeholder' => __('Select LIC')]) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('segregation_practiced', __('Segregation Practiced?'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::select('segregation_practiced', $yesNoOptions, old('segregation_practiced', optional($household)->segregation_practiced ? 1 : 0), ['class' => 'form-control', 'id' => 'segregation_practiced']) !!}</div>
    </div>
    <div class="form-group row required">
        {!! Form::label('status', __('Household Status'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('status', \App\Models\BuildingInfo\Household::statusOptions(), old('status', optional($household)->status ?? \App\Models\BuildingInfo\Household::STATUS_ACTIVE), ['class' => 'form-control', 'id' => 'status']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('remarks', __('Remarks'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::textarea('remarks', null, ['class' => 'form-control', 'rows' => 2]) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('survey_date', __('Survey Date'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::date('survey_date', optional(old('survey_date', optional($household)->survey_date))->format('Y-m-d'), ['class' => 'form-control']) !!}</div>
    </div>
</div>
<div class="card-footer">
    <a href="{{ route('building-info.households.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
    {!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>
</div>

@push('scripts')
<script>
$(function() {
    if ($.fn.select2) {
        $('#bin').select2({
            width: '100%',
            placeholder: "{{ __('Select BIN') }}",
            allowClear: true
        });
    }

    function toggleLicRow() {
        $('#lic-id-row').toggle($('#is_lic').val() === '1');
    }

    function applySnapshot(data) {
        $('#ward').val(data.ward || '');
        $('#road_no').val(data.road_no || '');
        $('#road_name').val(data.road_name || '');
        $('#holding_number').val(data.holding_number || '');
        if (!$('#tax_id').val()) {
            $('#tax_id').val(data.tax_id || '');
        }
        if (data.functional_use) {
            $('#functional_use').val(data.functional_use).trigger('chosen:updated');
        }
        if (data.lic_id) {
            $('#is_lic').val('1');
            $('#lic_id').val(String(data.lic_id)).trigger('chosen:updated');
        }
        toggleLicRow();
    }

    function fetchSnapshot(bin) {
        if (!bin) {
            return;
        }
        $.get("{!! route('building-info.households.building-snapshot') !!}", { bin: bin })
            .done(function(response) {
                applySnapshot(response);
            });
    }

    function reindexWasteBinRows() {
        $('#waste-bins-tbody tr.waste-bin-row').each(function (idx) {
            $(this).find('[name]').each(function () {
                var el = $(this);
                var n = el.attr('name');
                if (!n) return;
                el.attr('name', n.replace(/waste_bins\[\d+]/, 'waste_bins[' + idx + ']'));
            });
        });
    }

    function toggleWasteBinControls() {
        var isOwner = $('#is_owner').val() === '1';
        $('#waste-bin-provided-row').toggle(isOwner);
        if (!isOwner) {
            $('#waste_bin_provided').val('0');
        }
        $('#waste-bin-fields').toggle(isOwner && $('#waste_bin_provided').val() === '1');
    }

    $('#waste-bin-add-row').on('click', function () {
        var $tbody = $('#waste-bins-tbody');
        var $first = $tbody.find('tr.waste-bin-row').first();
        var $clone = $first.clone();
        $clone.find('input[type=hidden]').remove();
        $clone.find('input.waste-bin-capacity-input').val('');
        $clone.find('select.waste-bin-type-select').val('');
        $tbody.append($clone);
        reindexWasteBinRows();
    });

    $(document).on('click', '.waste-bin-remove', function () {
        var $tbody = $('#waste-bins-tbody');
        if ($tbody.find('tr.waste-bin-row').length <= 1) {
            return;
        }
        $(this).closest('tr.waste-bin-row').remove();
        reindexWasteBinRows();
    });

    $('#is_lic').on('change', toggleLicRow);
    $('#is_owner').on('change', toggleWasteBinControls);
    $('#waste_bin_provided').on('change', toggleWasteBinControls);
    toggleLicRow();
    toggleWasteBinControls();

    $('#bin').on('change', function() {
        fetchSnapshot($(this).val());
    });
});
</script>
@endpush
