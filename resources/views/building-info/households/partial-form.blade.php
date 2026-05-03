@push('style')
<style>
.household-form-mobile .chosen-container,
.household-form-mobile .select2-container {
    width: 100% !important;
    max-width: 100%;
}
@media (max-width: 767.98px) {
    .household-form-mobile .form-group.row {
        margin-bottom: 0.85rem;
    }
    .household-form-mobile .control-label {
        text-align: left !important;
        margin-bottom: 0.35rem;
    }
    .household-form-mobile .col-sm-3 {
        max-width: 100%;
        flex: 0 0 100%;
    }
    .household-form-mobile .pt-2 {
        padding-top: 0 !important;
    }
}
</style>
@endpush
<div class="card-body household-form-mobile">
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
    <div class="form-group row required">
        {!! Form::label('contact_number', __('Contact Number'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('contact_number', null, ['class' => 'form-control', 'placeholder' => __('Contact Number')]) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('area_mohalla_name', __('Area / Mohalla Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('area_mohalla_name', null, ['class' => 'form-control', 'placeholder' => __('Area / Mohalla Name')]) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('sub_location', __('Sub Location'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('sub_location', null, ['class' => 'form-control', 'placeholder' => __('Sub Location')]) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('bin', __('BIN (Optional)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('bin', $bins, null, ['class' => 'form-control chosen-select', 'id' => 'bin', 'placeholder' => __('Select BIN')]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('ward', __('Ward'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('ward', null, ['class' => 'form-control', 'id' => 'ward']) !!}</div>
    </div>
    <div class="form-group row required">
        {!! Form::label('road_no_name', __('Road No./Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::text('road_no_name', null, ['class' => 'form-control', 'id' => 'road_no_name']) !!}</div>
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
        {!! Form::label('is_owner', __('Building Owner') . ' (' . __('Yes') . '/' . __('No') . ')', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 pt-2"><input type="hidden" name="is_owner" value="0">{!! Form::checkbox('is_owner', '1', (bool) old('is_owner', optional($household)->is_owner), ['id' => 'is_owner']) !!}</div>
    </div>
    <div class="form-group row" id="waste-bin-provided-row">
        {!! Form::label('waste_bin_provided', __('Waste Bin Provided') . ' (' . __('Yes') . '/' . __('No') . ')', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 pt-2"><input type="hidden" name="waste_bin_provided" value="0">{!! Form::checkbox('waste_bin_provided', '1', (bool) old('waste_bin_provided', optional($household)->waste_bin_provided), ['id' => 'waste_bin_provided']) !!}</div>
    </div>
    <div id="waste-bin-fields">
        <div class="form-group row">
            {!! Form::label('number_of_waste_bins', __('Number of Waste Bins'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">{!! Form::number('number_of_waste_bins', old('number_of_waste_bins', optional(optional($household)->wasteBin)->number_of_waste_bins), ['class' => 'form-control', 'min' => 1]) !!}</div>
        </div>
        <div class="form-group row">
            {!! Form::label('total_capacity_kg', __('Total Capacity of Waste Bins') . ' (' . __('Kg') . ')', ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">{!! Form::number('total_capacity_kg', old('total_capacity_kg', optional(optional($household)->wasteBin)->total_capacity_kg), ['class' => 'form-control', 'step' => '0.01', 'min' => 0]) !!}</div>
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('is_lic', __('LIC') . ' (' . __('Yes') . '/' . __('No') . ')', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 pt-2"><input type="hidden" name="is_lic" value="0">{!! Form::checkbox('is_lic', '1', (bool) old('is_lic', optional($household)->is_lic), ['id' => 'is_lic']) !!}</div>
    </div>
    <div class="form-group row" id="lic-id-row">
        {!! Form::label('lic_id', __('LIC ID (if Y)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">{!! Form::select('lic_id', $licOptions, null, ['class' => 'form-control chosen-select', 'id' => 'lic_id', 'placeholder' => __('Select LIC')]) !!}</div>
    </div>
    <div class="form-group row">
        {!! Form::label('segregation_practiced', __('Segregation Practiced') . ' (' . __('Yes') . '/' . __('No') . ')', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 pt-2"><input type="hidden" name="segregation_practiced" value="0">{!! Form::checkbox('segregation_practiced', '1', (bool) old('segregation_practiced', optional($household)->segregation_practiced), ['id' => 'segregation_practiced']) !!}</div>
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
        $('#lic-id-row').toggle($('#is_lic').is(':checked'));
    }

    function applySnapshot(data) {
        $('#ward').val(data.ward || '');
        $('#road_no_name').val(data.road_no_name || '');
        $('#holding_number').val(data.holding_number || '');
        if (!$('#tax_id').val()) {
            $('#tax_id').val(data.tax_id || '');
        }
        if (data.functional_use) {
            $('#functional_use').val(data.functional_use).trigger('chosen:updated');
        }
        if (data.lic_id) {
            $('#is_lic').prop('checked', true);
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

    function toggleWasteBinControls() {
        var isOwner = $('#is_owner').is(':checked');
        $('#waste-bin-provided-row').toggle(isOwner);
        if (!isOwner) {
            $('#waste_bin_provided').prop('checked', false);
        }
        $('#waste-bin-fields').toggle(isOwner && $('#waste_bin_provided').is(':checked'));
    }

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
