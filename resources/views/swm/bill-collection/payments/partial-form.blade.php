@php
    $isEdit = isset($payment) && $payment;
    $excludePaymentId = $isEdit ? $payment->id : null;
    $defaultMonth = old(
        'payment_for_month',
        ($isEdit && $payment->payment_for_month)
            ? $payment->payment_for_month->format('Y-m')
            : now()->format('Y-m')
    );
    $defaultPaymentTime = old(
        'payment_time',
        ($isEdit && $payment->payment_time)
            ? $payment->payment_time->format('Y-m-d\TH:i')
            : now()->format('Y-m-d\TH:i')
    );
    $recvUsers = ['' => __('Default (logged-in user)')] + $users->all();
    $initHolding = old('holding_number', $isEdit ? ($payment->holding_number ?? '') : '');
    $initCustomerName = $isEdit ? optional($payment->primaryCollectionSite)->household_owner_name : null;
@endphp
@push('style')
<style>
/* Bill collection payment form: one control column width; Select2 must fill it */
.bcp-payment-field-col .select2-container {
    width: 100% !important;
    max-width: 100%;
}
.bcp-payment-field-col .select2-selection--single {
    height: calc(1.5em + 0.75rem + 2px);
    padding: 0.375rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}
.bcp-payment-field-col .select2-selection__rendered {
    line-height: 1.5;
    padding-left: 0;
}
.bcp-payment-field-col .select2-selection__arrow {
    height: 100%;
}
#balance-panel {
    position: relative;
    min-height: 5.5rem;
}
#balance-panel.is-loading .bcp-balance-loading-overlay {
    display: flex;
}
#balance-panel.is-loading #balance-panel-body {
    opacity: 0.45;
    pointer-events: none;
}
.bcp-balance-loading-overlay {
    display: none;
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    align-items: center;
    justify-content: center;
    background: rgba(248, 249, 250, 0.88);
    z-index: 2;
    border-radius: inherit;
    font-size: 0.95rem;
    color: #495057;
}
.bcp-balance-loading-overlay .fa-spinner {
    margin-right: 0.5rem;
}
</style>
@endpush
<div class="card-body">
    {!! Form::hidden('household_id', old('household_id', $isEdit ? $payment->household_id : ''), ['id' => 'household_id']) !!}
    {!! Form::hidden('holding_number', old('holding_number', $isEdit ? ($payment->holding_number ?? '') : ''), ['id' => 'holding_number']) !!}
    {!! Form::hidden('household_code', old('household_code', $isEdit ? $payment->customer_id : ''), ['id' => 'household_code']) !!}

    <div class="form-group row required">
        <label class="col-sm-3 control-label" for="holding_select">{{ __('Holding') }}</label>
        <div class="col-sm-9 bcp-payment-field-col">
            <select class="form-control" id="holding_select" style="width:100%"></select>
            <small class="form-text text-muted">{{ __('Search by holding number (min. 2 characters).') }}</small>
        </div>
    </div>

    <div class="form-group row required">
        <label class="col-sm-3 control-label" for="customer_site_select">{{ __('Customer / Site') }}</label>
        <div class="col-sm-9 bcp-payment-field-col">
            <select class="form-control" id="customer_site_select" style="width:100%" @if($initHolding === '') disabled @endif></select>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-sm-3 control-label">{{ __('Billing summary') }}</label>
        <div class="col-sm-9 bcp-payment-field-col">
            <div class="border rounded p-3 bg-light w-100" id="balance-panel">
                <div class="bcp-balance-loading-overlay" id="bcp-balance-loading" aria-live="polite" aria-busy="false">
                    <span><i class="fas fa-spinner fa-spin" aria-hidden="true"></i> {{ __('Loading…') }}</span>
                </div>
                <div id="balance-panel-body">
                    <div><strong>{{ __('Charge (per month)') }}:</strong> <span id="bcp-waste-charge">—</span></div>
                    <div><strong>{{ __('Total due through selected month') }}:</strong> <span id="bcp-due">—</span></div>
                    <div class="small text-muted mt-2">{{ __('Due is based on billing from service start or survey date through the selected month, minus all payments recorded for months up to and including that month.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('payment_for_month', __('Payment for the month of'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9 bcp-payment-field-col">
            <input type="month" name="payment_for_month" id="payment_for_month" class="form-control w-100" value="{{ $defaultMonth }}" />
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('payment_time', __('Payment time'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9 bcp-payment-field-col">
            <input type="datetime-local" name="payment_time" id="payment_time" class="form-control w-100" value="{{ $defaultPaymentTime }}" />
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('amount', __('Amount'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9 bcp-payment-field-col">
            {!! Form::number('amount', old('amount', $isEdit ? $payment->amount : null), ['class' => 'form-control w-100', 'step' => '0.01', 'min' => '0.01']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('payment_method', __('Payment method'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9 bcp-payment-field-col">
            {!! Form::select('payment_method', $paymentMethods, old('payment_method', $isEdit ? $payment->payment_method : null), ['class' => 'form-control w-100', 'placeholder' => __('Select')]) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('received_by_user_id', __('Payment received by'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9 bcp-payment-field-col">
            @if(!empty($canChooseReceivedBy))
                {!! Form::select('received_by_user_id', $recvUsers, old('received_by_user_id', $isEdit ? $payment->received_by_user_id : ''), ['class' => 'form-control chosen-select w-100']) !!}
            @else
                @php
                    $lockedReceivedById = $isEdit ? (int) $payment->received_by_user_id : (int) Auth::id();
                    $lockedReceivedByName = $isEdit
                        ? (optional($payment->receivedBy)->name ?? optional(\App\Models\User::find($lockedReceivedById))->name ?? '')
                        : (Auth::user()->name ?? '');
                @endphp
                <input type="hidden" name="received_by_user_id" value="{{ $lockedReceivedById }}" />
                <input type="text" class="form-control w-100" readonly value="{{ $lockedReceivedByName }}" />
            @endif
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('receipt_copy', __('Payment receipt copy'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-9 bcp-payment-field-col">
            <input type="file" name="receipt_copy" id="receipt_copy" class="form-control w-100" accept=".jpg,.jpeg,.png,.pdf" />
            <small class="form-text text-muted">{{ __('Allowed file types: JPG, PNG, PDF. Max size 10 MB.') }}</small>
            @if($isEdit && !empty($payment->receipt_copy_url))
                <a href="{{ $payment->receipt_copy_url }}" target="_blank" rel="noopener">{{ __('View current receipt') }}</a>
            @endif
        </div>
    </div>
</div>
<div class="card-footer">
    <a href="{{ route('swm.bill-collection-payments.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div>

@push('scripts')
<script>
(function() {
    var holdingsUrl = @json(route('swm.bill-collection.holdings-search'));
    var customersUrl = @json(route('swm.bill-collection.customers-by-holding'));
    var balanceUrl = @json(route('swm.bill-collection-payments.balance-through-month'));
    var excludePaymentId = @json($excludePaymentId);
    var csrf = @json(csrf_token());
    var balanceRequestSeq = 0;

    function setBalanceLoading(isLoading) {
        var $panel = $('#balance-panel');
        var $live = $('#bcp-balance-loading');
        if (isLoading) {
            $panel.addClass('is-loading');
            $live.attr('aria-busy', 'true');
        } else {
            $panel.removeClass('is-loading');
            $live.attr('aria-busy', 'false');
        }
    }

    function monthFirstDay(ym) {
        if (!ym || ym.length < 7) return null;
        return ym + '-01';
    }

    function refreshBalance() {
        var siteId = $('#household_id').val();
        var ym = $('#payment_for_month').val();
        var pm = monthFirstDay(ym);
        if (!siteId || !pm) {
            setBalanceLoading(false);
            $('#bcp-waste-charge').text('—');
            $('#bcp-due').text('—');
            return;
        }
        var seq = ++balanceRequestSeq;
        setBalanceLoading(true);
        var url = balanceUrl + '?household_id=' + encodeURIComponent(siteId)
            + '&payment_for_month=' + encodeURIComponent(pm);
        if (excludePaymentId) {
            url += '&exclude_payment_id=' + encodeURIComponent(excludePaymentId);
        }
        $.getJSON(url).done(function(data) {
            if (seq !== balanceRequestSeq) {
                return;
            }
            if (data.waste_charge === null || data.waste_charge === undefined) {
                $('#bcp-waste-charge').text('{{ __('Not set') }}');
            } else {
                $('#bcp-waste-charge').text(data.waste_charge);
            }
            if (data.due === null || data.due === undefined) {
                $('#bcp-due').text('—');
            } else {
                $('#bcp-due').text(data.due);
            }
        }).fail(function() {
            if (seq !== balanceRequestSeq) {
                return;
            }
            $('#bcp-waste-charge').text('—');
            $('#bcp-due').text('—');
        }).always(function() {
            if (seq === balanceRequestSeq) {
                setBalanceLoading(false);
            }
        });
    }

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

    $('#customer_site_select').select2({
        placeholder: '{{ __('Select customer') }}',
        allowClear: true,
        ajax: {
            url: customersUrl,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    holding_number: $('#holding_number').val(),
                    q: params.term
                };
            },
            processResults: function(data) {
                return { results: data.results || [] };
            },
            headers: { 'X-CSRF-TOKEN': csrf }
        }
    });

    $('#holding_select').on('select2:select', function(e) {
        var data = e.params.data;
        $('#holding_number').val(data.id);
        $('#household_id').val('');
        $('#household_code').val('');
        $('#customer_site_select').prop('disabled', false).val(null).trigger('change');
        refreshBalance();
    });

    $('#holding_select').on('select2:clear', function() {
        $('#holding_number').val('');
        $('#household_id').val('');
        $('#household_code').val('');
        $('#customer_site_select').prop('disabled', true).val(null).trigger('change');
        refreshBalance();
    });

    $('#customer_site_select').on('select2:select', function(e) {
        var d = e.params.data;
        $('#household_id').val(d.id);
        $('#household_code').val(d.household_id || '');
        if (d.holding_number) {
            $('#holding_number').val(d.holding_number);
        }
        refreshBalance();
    });

    $('#customer_site_select').on('select2:clear', function() {
        $('#household_id').val('');
        $('#household_code').val('');
        refreshBalance();
    });

    $('#payment_for_month').on('change', refreshBalance);

    @if($initHolding !== '')
    (function initFromExisting() {
        var hn = @json($initHolding);
        var opt = new Option(hn, hn, true, true);
        $('#holding_select').append(opt).trigger('change');
        $('#customer_site_select').prop('disabled', false);
        var sid = @json(old('household_id', $isEdit ? $payment->household_id : ''));
        var cid = @json(old('household_code', $isEdit ? $payment->customer_id : ''));
        var cname = @json($initCustomerName ?? '');
        var label = cid + (cname ? (' — ' + cname) : '');
        var copt = new Option(label, sid, true, true);
        $(copt).data('data', { id: sid, text: label, household_id: cid });
        $('#customer_site_select').append(copt).trigger('change');
        $('#household_id').val(sid);
        $('#household_code').val(cid);
        $('#holding_number').val(hn);
        refreshBalance();
    })();
    @endif
})();
</script>
@endpush
