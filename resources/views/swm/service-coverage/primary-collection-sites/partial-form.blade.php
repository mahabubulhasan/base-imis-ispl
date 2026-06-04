<div class="swm-primary-collection-site-form-mobile app-mobile-form">
<div class="card-body">
    <div class="form-group row required">
        {!! Form::label('customer_id', __('Customer ID'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('customer_id', null, ['class' => 'form-control', 'placeholder' => __('Customer ID')]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('customer_name', __('Customer Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('customer_name', null, ['class' => 'form-control', 'placeholder' => __('Customer Name')]) !!}
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

    <div class="form-group row required">
        {!! Form::label('bin', __('BIN'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('bin', $bins, null, ['class' => 'form-control chosen-select', 'id' => 'bin', 'placeholder' => __('Select BIN')]) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('ward', __('Ward'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('ward', null, ['class' => 'form-control', 'id' => 'ward', 'readonly']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('road_no', __('Road No.'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('road_no', null, ['class' => 'form-control', 'id' => 'road_no', 'readonly']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('road_name', __('Road Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('road_name', null, ['class' => 'form-control', 'id' => 'road_name', 'readonly']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('holding_number', __('Holding Number'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('holding_number', null, ['class' => 'form-control', 'id' => 'holding_number', 'readonly']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('tax_id', __('Tax ID'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('tax_id', null, ['class' => 'form-control', 'id' => 'tax_id']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('functional_use', __('Functional Use'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('functional_use', null, ['class' => 'form-control', 'id' => 'functional_use', 'readonly']) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('waste_charge', __('Waste Charge'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('waste_charge', null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('number_of_family_members', __('Number of Family Members'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('number_of_family_members', null, ['class' => 'form-control', 'min' => 0]) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('using_this_service_since', __('Using This Service Since'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::date('using_this_service_since', optional(old('using_this_service_since', optional($primaryCollectionSite)->using_this_service_since))->format('Y-m-d'), ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('daily_waste_volume', __('Total volume of the waste collected (daily average approx.)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('daily_waste_volume', null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('remarks', __('Remarks'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::textarea('remarks', null, ['class' => 'form-control', 'rows' => 2]) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('survey_date', __('Survey Date'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::date('survey_date', optional(old('survey_date', optional($primaryCollectionSite)->survey_date))->format('Y-m-d'), ['class' => 'form-control']) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('van_puller_id', __('Van Puller'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('van_puller_id', $vanPullers, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Select Van Puller')]) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('is_owner', __('Owner') . ' (' . __('Yes') . '/' . __('No') . ')', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 pt-2">
            <input type="hidden" name="is_owner" value="0">
            {!! Form::checkbox('is_owner', '1', (bool) old('is_owner', optional($primaryCollectionSite)->is_owner), ['id' => 'is_owner']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('is_lic', __('LIC') . ' (' . __('Yes') . '/' . __('No') . ')', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 pt-2">
            <input type="hidden" name="is_lic" value="0">
            {!! Form::checkbox('is_lic', '1', (bool) old('is_lic', optional($primaryCollectionSite)->is_lic), ['id' => 'is_lic']) !!}
        </div>
    </div>
    <div class="form-group row" id="lic-id-row">
        {!! Form::label('lic_id', __('LIC ID (if Y)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('lic_id', $licOptions, null, ['class' => 'form-control chosen-select', 'id' => 'lic_id', 'placeholder' => __('Select LIC ID')]) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('segregation_practiced', __('Segregation Practiced') . ' (' . __('Yes') . '/' . __('No') . ')', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 pt-2">
            <input type="hidden" name="segregation_practiced" value="0">
            {!! Form::checkbox('segregation_practiced', '1', (bool) old('segregation_practiced', optional($primaryCollectionSite)->segregation_practiced), ['id' => 'segregation_practiced']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('waste_bin_provided', __('Waste bin Provided') . ' (' . __('Yes') . '/' . __('No') . ')', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 pt-2">
            <input type="hidden" name="waste_bin_provided" value="0">
            {!! Form::checkbox('waste_bin_provided', '1', (bool) old('waste_bin_provided', optional($primaryCollectionSite)->waste_bin_provided), ['id' => 'waste_bin_provided']) !!}
        </div>
    </div>
</div>
<div class="card-footer">
    <a href="{{ route('swm.primary-collection-sites.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
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
        $('#lic-id-row').toggle($('#is_lic').is(':checked'));
    }

    function setSnapshotFieldState(selector, value) {
        const hasValue = value !== null && value !== undefined && String(value).trim() !== '';
        $(selector).val(hasValue ? value : '').prop('readonly', hasValue);
    }

    function applySnapshot(data) {
        setSnapshotFieldState('#ward', data.ward);
        setSnapshotFieldState('#road_no', data.road_no);
        setSnapshotFieldState('#road_name', data.road_name);
        setSnapshotFieldState('#holding_number', data.holding_number);
        if (!$('#tax_id').val()) {
            $('#tax_id').val(data.tax_id || '');
        }
        setSnapshotFieldState('#functional_use', data.functional_use);
    }

    function fetchSnapshot(bin) {
        if (!bin) {
            return;
        }
        $.get("{!! route('swm.primary-collection-sites.building-snapshot') !!}", { bin: bin })
            .done(function(response) {
                applySnapshot(response);
            });
    }

    $('#is_lic').on('change', toggleLicRow);
    toggleLicRow();

    $('#bin').on('change', function() {
        fetchSnapshot($(this).val());
    });
});
</script>
@endpush
