@php
    $isEdit = isset($complaint) && $complaint;
    $initHolding = old('holding_number', $isEdit ? ($complaint->holding_number ?? '') : '');
    $initCustomerId = old('customer_id', $isEdit ? ($complaint->customer_id ?? '') : '');
    $initDateTime = old('date_time', ($isEdit && $complaint->date_time) ? $complaint->date_time->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i'));
@endphp

<div class="card-body">
    {!! Form::hidden('holding_number', old('holding_number', $initHolding), ['id' => 'holding_number']) !!}
    {!! Form::hidden('customer_id', old('customer_id', $initCustomerId), ['id' => 'customer_id']) !!}

    <div class="form-group row required">
        {!! Form::label('complaint_id', __('Complaint ID'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('complaint_id', old('complaint_id', $isEdit ? $complaint->complaint_id : null), ['class' => 'form-control', 'placeholder' => __('Complaint ID')]) !!}
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
        {!! Form::label('customer_id_select', __('Customer ID'), ['class' => 'col-sm-3 control-label']) !!}
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
    </div>

    <div class="form-group row required">
        {!! Form::label('complaint_details', __('Complaint Details'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9">
            {!! Form::textarea('complaint_details', old('complaint_details', $isEdit ? $complaint->complaint_details : null), ['class' => 'form-control', 'rows' => 4, 'placeholder' => __('Complaint Details')]) !!}
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
            placeholder: '{{ __('Search customer') }}',
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
        $('#customer_id').val('');
        $('#customer_id_select').val(null).trigger('change');
        initCustomerSelect();
    });

    $('#holding_select').on('select2:clear', function() {
        $('#holding_number').val('');
        $('#customer_id').val('');
        $('#customer_id_select').val(null).trigger('change');
        initCustomerSelect();
    });

    $('#customer_id_select').on('select2:select', function(e) {
        var data = e.params.data;
        $('#customer_id').val(data.id || '');
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
        $('#customer_id').val('');
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
            $('#customer_id').val(cid);
        }
    })();
    @endif
});
</script>
@endpush
