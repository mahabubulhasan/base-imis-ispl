@php
    $isEdit = isset($complaint) && $complaint;
    $initHolding = old('holding_number', $isEdit ? ($complaint->holding_number ?? '') : '');
    $initCustomerId = old('household_id', $isEdit ? ($complaint->customer_id ?? '') : '');
    $initDateTime = old('date_time', ($isEdit && $complaint->date_time) ? $complaint->date_time->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i'));
    $initIncidentDate = old('incident_date', ($isEdit && $complaint->incident_date) ? $complaint->incident_date->format('Y-m-d') : '');
@endphp
@push('style')
<style>
.swm-complaint-form-mobile .select2-container {
    width: 100% !important;
    max-width: 100%;
}
@media (max-width: 767.98px) {
    .swm-complaint-form-mobile .form-group.row {
        margin-bottom: 0.85rem;
    }
    .swm-complaint-form-mobile .control-label {
        text-align: left !important;
        margin-bottom: 0.35rem;
    }
    .swm-complaint-form-mobile .col-sm-3,
    .swm-complaint-form-mobile .col-sm-9 {
        max-width: 100%;
        flex: 0 0 100%;
    }
}
</style>
@endpush
<div class="card-body swm-complaint-form-mobile">
    {!! Form::hidden('holding_number', old('holding_number', $initHolding), ['id' => 'holding_number']) !!}
    {!! Form::hidden('household_id', old('household_id', $initCustomerId), ['id' => 'household_id']) !!}

    <div class="form-group row required">
        {!! Form::label('complaint_id', __('Complaint ID'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            @if($isEdit)
                {!! Form::text('complaint_id', old('complaint_id', $complaint->complaint_id), ['class' => 'form-control', 'placeholder' => __('Complaint ID'), 'readonly' => true]) !!}
            @else
                <input type="text" class="form-control" value="{{ __('Auto generated on save') }}" readonly />
            @endif
        </div>
        {!! Form::label('date_time', __('Date and Time'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="datetime-local" name="date_time" id="date_time" class="form-control" value="{{ $initDateTime }}" />
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('holding_select', __('Holding number'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <select class="form-control" id="holding_select" style="width:100%"></select>
        </div>
        {!! Form::label('customer_id_select', __('Household ID'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <select class="form-control" id="customer_id_select" style="width:100%"></select>
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('name', __('Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('name', old('name', $isEdit ? $complaint->name : null), ['class' => 'form-control', 'placeholder' => __('Name')]) !!}
        </div>
        {!! Form::label('contact_number', __('Contact number'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('contact_number', old('contact_number', $isEdit ? $complaint->contact_number : null), ['class' => 'form-control', 'placeholder' => __('Contact number')]) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('ward_no', __('Ward No.'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('ward_no', old('ward_no', $isEdit ? $complaint->ward_no : null), ['class' => 'form-control', 'placeholder' => __('Ward No.')]) !!}
        </div>
        {!! Form::label('incident_date', __('Incident Date'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="date" name="incident_date" id="incident_date" class="form-control" value="{{ $initIncidentDate }}" />
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('complaint_type', __('Complaint Type'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('complaint_type', collect($complaintTypes)->mapWithKeys(fn ($label, $key) => [$key => __($label)])->all(), old('complaint_type', $isEdit ? $complaint->complaint_type : null), ['class' => 'form-control', 'placeholder' => __('Select Complaint Type')]) !!}
        </div>
        {!! Form::label('submitted_through', __('Complaint Submitted through'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('submitted_through', collect($submittedThroughOptions)->mapWithKeys(fn ($label, $key) => [$key => __($label)])->all(), old('submitted_through', $isEdit ? $complaint->submitted_through : null), ['class' => 'form-control', 'placeholder' => __('Select')]) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('complaint_status', __('Complaint Status'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('complaint_status', collect($complaintStatuses)->mapWithKeys(fn ($label, $key) => [$key => __($label)])->all(), old('complaint_status', $isEdit ? $complaint->complaint_status : 'pending'), ['class' => 'form-control', 'placeholder' => __('Select Complaint Status')]) !!}
        </div>
        {!! Form::label('resolution_time_days', __('Resolution Time (days)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('resolution_time_days', old('resolution_time_days', $isEdit ? $complaint->resolution_time_days : null), ['class' => 'form-control', 'placeholder' => __('Resolution Time (days)'), 'min' => 0]) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('duplicate_complaint', __('Duplicate Complaint'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('duplicate_complaint', $duplicateOptions, old('duplicate_complaint', $isEdit ? (int) ($complaint->duplicate_complaint ?? 0) : 0), ['class' => 'form-control']) !!}
        </div>
        {!! Form::label('duplicate_reference', __('Duplicate Complaint'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('duplicate_reference', old('duplicate_reference', $isEdit ? $complaint->duplicate_reference : null), ['class' => 'form-control', 'placeholder' => __('Linked Complaint ID (optional)')]) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('priority_level', __('Priority Level (1-5)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('priority_level', ['' => __('Select Priority')] + $priorityLevels, old('priority_level', $isEdit ? $complaint->priority_level : null), ['class' => 'form-control']) !!}
        </div>
        {!! Form::label('assigned_to', __('Assigned To'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('assigned_to', old('assigned_to', $isEdit ? $complaint->assigned_to : null), ['class' => 'form-control', 'placeholder' => __('Assigned worker/driver')]) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('complaint_details', __('Complaint Details'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::textarea('complaint_details', old('complaint_details', $isEdit ? $complaint->complaint_details : null), ['class' => 'form-control', 'rows' => 4, 'placeholder' => __('Complaint Details')]) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('photo_attachment', __('Photo Attachment'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            <input type="file" name="photo_attachment" id="photo_attachment" class="form-control" accept=".jpg,.jpeg,.png,.webp" />
            @if($isEdit && !empty($complaint->photo_attachment_path))
                <small class="text-muted d-block mt-2">
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($complaint->photo_attachment_path) }}" target="_blank">
                        {{ __('View current attachment') }}
                    </a>
                </small>
            @endif
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('notes', __('Notes'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::textarea('notes', old('notes', $isEdit ? $complaint->notes : null), ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Notes')]) !!}
        </div>
    </div>
</div>
<div class="card-footer">
    <a href="{{ route('swm.complaints.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div>

@push('scripts')
<script>
$(function() {
    var holdingsUrl = @json(route('swm.complaints.holdings-search'));
    var customersUrl = @json(route('swm.complaints.customers-search'));
    var csrf = @json(csrf_token());

    $('#holding_select').select2({
        placeholder: '{{ __('Search holding') }}',
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: holdingsUrl,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(data) {
                return { results: data.results || [] };
            },
            headers: { 'X-CSRF-TOKEN': csrf }
        }
    });

    function selectedHoldingNumbers() {
        var v = $('#holding_number').val();
        return v ? [v] : [];
    }

    function initCustomerSelect() {
        if ($('#customer_id_select').data('select2')) {
            $('#customer_id_select').select2('destroy');
        }
        $('#customer_id_select').select2({
            placeholder: '{{ __('Search household') }}',
            allowClear: true,
            minimumInputLength: selectedHoldingNumbers().length > 0 ? 0 : 2,
            ajax: {
                url: customersUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        holding_numbers: selectedHoldingNumbers(),
                        q: params.term
                    };
                },
                traditional: true,
                processResults: function(data) {
                    return { results: data.results || [] };
                },
                headers: { 'X-CSRF-TOKEN': csrf }
            }
        });
    }

    initCustomerSelect();

    $('#holding_select').on('select2:select', function(e) {
        var data = e.params.data;
        $('#holding_number').val(data.id);
        $('#household_id').val('');
        $('#customer_id_select').val(null).trigger('change');
        initCustomerSelect();
    });

    $('#holding_select').on('select2:clear', function() {
        $('#holding_number').val('');
        $('#household_id').val('');
        $('#customer_id_select').val(null).trigger('change');
        initCustomerSelect();
    });

    $('#customer_id_select').on('select2:select', function(e) {
        var data = e.params.data;
        $('#household_id').val(data.id || '');
        if (data.holding_number) {
            $('#holding_number').val(data.holding_number);
            if (!$('#holding_select').find("option[value='" + data.holding_number + "']").length) {
                var opt = new Option(data.holding_number, data.holding_number, true, true);
                $('#holding_select').append(opt);
            }
            $('#holding_select').val(data.holding_number).trigger('change.select2');
        }
    });

    $('#customer_id_select').on('select2:clear', function() {
        $('#household_id').val('');
    });

    @if($initHolding !== '')
    (function initFromExisting() {
        var hn = @json($initHolding);
        var cid = @json($initCustomerId);
        var holdingOpt = new Option(hn, hn, true, true);
        $('#holding_select').append(holdingOpt).trigger('change');
        $('#holding_number').val(hn);
        initCustomerSelect();
        if (cid) {
            var customerLabel = cid + (hn ? (' (' + hn + ')') : '');
            var customerOpt = new Option(customerLabel, cid, true, true);
            $('#customer_id_select').append(customerOpt).trigger('change');
            $('#household_id').val(cid);
        }
    })();
    @endif
});
</script>
@endpush
