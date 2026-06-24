@php
    $isEdit = isset($payment) && $payment;
    $excludePaymentId = $isEdit ? $payment->id : null;
    $defaultMonth = now()->format('Y-m');
    $dueThroughMonthLabel = \Carbon\Carbon::createFromFormat('Y-m', $defaultMonth)->subMonth()->format('F Y');
    $defaultPaymentTime = old(
        'payment_time',
        ($isEdit && $payment->payment_time)
            ? $payment->payment_time->format('Y-m-d\TH:i')
            : now()->format('Y-m-d\TH:i')
    );
    $recvUsers = ['' => __('Default (Logged-in User)')] + $users->all();
    $initHolding = old('holding_number', $isEdit ? ($payment->holding_number ?? '') : '');
    $initCustomerName = $isEdit ? optional($payment->primaryCollectionSite)->household_owner_name : null;
    $initFatherOrHusbandName = $isEdit ? optional($payment->primaryCollectionSite)->father_or_husband_name : null;
    $initSite = $isEdit ? optional($payment->primaryCollectionSite) : null;
    $initialHouseholdDetail = null;
    if ($initSite) {
        $initialHouseholdDetail = [
            'contact_number' => $initSite->contact_number,
            'sub_location' => $initSite->sub_location,
            'ward' => $initSite->ward !== null && $initSite->ward !== '' ? (string) $initSite->ward : null,
            'road_no' => $initSite->road_no,
            'road_name' => $initSite->road_name,
            'area_mohalla_name' => $initSite->area_mohalla_name,
        ];
    }
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
@media (max-width: 991.98px) {
    .bcp-payment-form-mobile #balance-panel {
        min-height: 0;
    }
}
.bcp-no-due-box {
    border: 1px solid #b8daff;
    background: #f0f7ff;
    border-radius: 0.375rem;
    padding: 0.9rem 1rem;
}
.bcp-no-due-box .bcp-no-due-title {
    font-weight: 600;
    color: #0b4f94;
    margin-bottom: 0.35rem;
}
.bcp-no-due-box .bcp-no-due-message {
    margin-bottom: 0.5rem;
    color: #0b4f94;
}
.bcp-no-due-box .bcp-no-due-meta {
    margin: 0;
    padding-left: 1rem;
    color: #2f4f6f;
}
</style>
@endpush
<div class="app-mobile-form bcp-payment-form-mobile">
<div class="card-body">
    {!! Form::hidden('household_id', old('household_id', $isEdit ? $payment->household_id : ''), ['id' => 'household_id']) !!}
    {!! Form::hidden('holding_number', old('holding_number', $isEdit ? ($payment->holding_number ?? '') : ''), ['id' => 'holding_number']) !!}
    {!! Form::hidden('household_code', old('household_code', $isEdit ? $payment->customer_id : ''), ['id' => 'household_code']) !!}

    <div class="form-group row required">
        <label class="col-sm-3 control-label" for="holding_select">{{ __('Holding') }}</label>
        <div class="col-sm-3 bcp-payment-field-col">
            <select class="form-control" id="holding_select" style="width:100%"></select>
            <small class="form-text text-muted">{{ __('Search by Holding Number (Min. 2 Characters).') }}</small>
        </div>
    </div>

    <div class="form-group row required">
        <label class="col-sm-3 control-label" for="customer_site_select">{{ __('Household') }}</label>
        <div class="col-sm-3 bcp-payment-field-col">
            <select class="form-control" id="customer_site_select" style="width:100%" @if($initHolding === '') disabled @endif></select>
        </div>
    </div>

    <div id="bcp-household-info-wrap" class="form-group row @if(!$initialHouseholdDetail) d-none @endif">
        <label class="col-sm-3 control-label">{{ __('Household Details') }}</label>
        <div class="col-sm-3 bcp-payment-field-col">
            <div class="border rounded p-3 bg-light w-100" id="bcp-household-info-panel">
                <div><strong>{{ __('Contact Number') }}:</strong> <span id="bcp-hi-contact">{{ $initialHouseholdDetail ? ($initialHouseholdDetail['contact_number'] ?? '—') : '—' }}</span></div>
                <div><strong>{{ __('Location') }}:</strong> <span id="bcp-hi-sub-location">{{ $initialHouseholdDetail ? ($initialHouseholdDetail['sub_location'] ?? '—') : '—' }}</span></div>
                <div><strong>{{ __('Ward') }}:</strong> <span id="bcp-hi-ward">{{ $initialHouseholdDetail ? ($initialHouseholdDetail['ward'] ?? '—') : '—' }}</span></div>
                <div><strong>{{ __('Road No.') }}:</strong> <span id="bcp-hi-road-no">{{ $initialHouseholdDetail ? ($initialHouseholdDetail['road_no'] ?? '—') : '—' }}</span></div>
                <div><strong>{{ __('Road Name') }}:</strong> <span id="bcp-hi-road-name">{{ $initialHouseholdDetail ? ($initialHouseholdDetail['road_name'] ?? '—') : '—' }}</span></div>
            </div>
        </div>
    </div>

    <div class="form-group row required bcp-due-dependent-row">
        {!! Form::label('payment_for_month', __('Transaction Month'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 bcp-payment-field-col">
            <input type="hidden" name="payment_for_month" id="payment_for_month" value="{{ $defaultMonth }}" />
            <input type="text" class="form-control w-100" value="{{ \Carbon\Carbon::createFromFormat('Y-m', $defaultMonth)->format('F Y') }}" readonly />
            <small class="form-text text-muted">{{ __('Transaction Month Is Auto-Selected as Current Month.') }}</small>
        </div>
    </div>

    <div class="form-group row bcp-due-dependent-row">
        <label class="col-sm-3 control-label">{{ __('Billing Summary') }}</label>
        <div class="col-sm-3 bcp-payment-field-col">
            <div class="border rounded p-3 bg-light w-100" id="balance-panel">
                <div class="bcp-balance-loading-overlay" id="bcp-balance-loading" aria-live="polite" aria-busy="false">
                    <span><i class="fas fa-spinner fa-spin" aria-hidden="true"></i> {{ __('Loading…') }}</span>
                </div>
                <div id="balance-panel-body">
                    <div><strong>{{ __('Waste Collection Fee') }} ({{ __('Taka') .' / '.  __('Month') }}):</strong> <span id="bcp-waste-charge">—</span></div>
                    <div><strong>{{ __('Total Amount to be Paid Through') }} <span id="bcp-due-month-label">{{ $dueThroughMonthLabel }}</span> ({{ __('Taka') }}):</strong> <span id="bcp-due">N/A</span></div>
                    <div class="small text-muted mt-2">{{ __('Total Amount Payable from the Start of Service Through') }} <span id="bcp-due-summary-month">{{ $dueThroughMonthLabel }}</span>, {{ __('Excluding Any Payments Already Made.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div id="bcp-no-due-info" class="d-none">
        <div class="bcp-no-due-box">
            <div class="bcp-no-due-title">{{ __('Payment Not Required') }}</div>
            <div class="bcp-no-due-message">{{ __('All Previous Dues Have Been Cleared for This Household.') }}</div>
            <ul class="bcp-no-due-meta">
                <li><strong>{{ __('Holding') }}:</strong> <span id="bcp-no-due-holding">—</span></li>
                <li><strong>{{ __('Household') }}:</strong> <span id="bcp-no-due-household">—</span></li>
                <li><strong>{{ __('Payment Month') }}:</strong> <span id="bcp-no-due-month">—</span></li>
                <li><strong>{{ __('Waste Collection Fee') }}:</strong> <span id="bcp-no-due-waste-charge">—</span></li>
                <li><strong>{{ __('Total Due Through Month') }}:</strong> <span id="bcp-no-due-total-due">0</span></li>
            </ul>
        </div>
    </div>

    <div class="form-group row bcp-due-dependent-row">
        {!! Form::label('payment_time', __('Payment Time'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 bcp-payment-field-col">
            <input type="datetime-local" name="payment_time" id="payment_time" class="form-control w-100" value="{{ $defaultPaymentTime }}" />
        </div>
    </div>

    <div class="form-group row bcp-due-dependent-row" id="bcp-current-amount-row">
        {!! Form::label('amount', __('Current Month Payment') . ' (' . __('Taka') . ')', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 bcp-payment-field-col">
            <div id="bcp-current-amount-input-wrap">
                {!! Form::number('amount', old('amount', $isEdit ? $payment->amount : 0), ['class' => 'form-control w-100', 'step' => '1', 'min' => '0', 'inputmode' => 'numeric']) !!}
            </div>
            <small id="bcp-current-month-paid-note" class="form-text text-info d-none">
                {{ __("The Current Month's Waste Collection Fee Has Been Paid. You May Only Pay Previous Dues.") }}
            </small>
        </div>
    </div>

    <div class="form-group row bcp-due-dependent-row" id="bcp-due-paid-row">
        {!! Form::label('due_paid', __('Previous Due Payment') . ' (' . __('Taka') . ')', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 bcp-payment-field-col">
            {!! Form::number('due_paid', old('due_paid', $isEdit ? ($payment->due_paid ?? 0) : 0), ['class' => 'form-control w-100', 'step' => '1', 'min' => '0', 'inputmode' => 'numeric']) !!}
        </div>
    </div>

    <div class="form-group row required bcp-due-dependent-row">
        {!! Form::label('payment_method', __('Payment Method'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 bcp-payment-field-col">
            {!! Form::select('payment_method', $paymentMethods, old('payment_method', $isEdit ? $payment->payment_method : null), ['class' => 'form-control w-100', 'placeholder' => __('Select')]) !!}
        </div>
    </div>

    <div class="form-group row bcp-due-dependent-row">
        {!! Form::label('receipt_copy', __('Payment Receipt Copy'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 bcp-payment-field-col">
            <input type="file" name="receipt_copy" id="receipt_copy" class="form-control w-100" accept=".jpg,.jpeg,.png,.pdf" />
            <small class="form-text text-muted">{{ __('Allowed File Types: JPG, PNG, PDF. Max Size 10 MB.') }}</small>
            @if($isEdit && !empty($payment->receipt_copy_url))
                <a href="{{ $payment->receipt_copy_url }}" target="_blank" rel="noopener">{{ __('View Current Receipt') }}</a>
            @endif
        </div>
    </div>

    <div class="form-group row bcp-due-dependent-row">
        {!! Form::label('receipt_no', __('Receipt No.'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 bcp-payment-field-col">
            {!! Form::text('receipt_no', old('receipt_no', $isEdit ? $payment->receipt_no : null), ['class' => 'form-control w-100', 'maxlength' => 255]) !!}
        </div>
    </div>

    <div class="form-group row bcp-due-dependent-row">
        {!! Form::label('received_by_user_id', __('Payment Received by'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 bcp-payment-field-col">
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
</div>
<div class="card-footer">
    <a href="{{ route('swm.bill-collection-payments.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info', 'id' => 'bcp-save-btn']) !!}
</div>
</div>

@push('scripts')
@include('components.formatting.currency-script')
<script>
(function() {
    var holdingsUrl = @json(route('swm.bill-collection.holdings-search'));
    var customersUrl = @json(route('swm.bill-collection.customers-by-holding'));
    var balanceUrl = @json(route('swm.bill-collection-payments.balance-through-month'));
    var excludePaymentId = @json($excludePaymentId);
    var csrf = @json(csrf_token());
    var balanceRequestSeq = 0;
    var selectedHouseholdText = '';
    var initialHouseholdDetail = @json($initialHouseholdDetail);

    function householdInfoPlaceholder() {
        return '—';
    }

    function formatHouseholdInfoText(v) {
        if (v === null || v === undefined || v === '') {
            return householdInfoPlaceholder();
        }
        return String(v);
    }

    function setHouseholdInfoVisible(visible) {
        if (visible) {
            $('#bcp-household-info-wrap').removeClass('d-none');
        } else {
            $('#bcp-household-info-wrap').addClass('d-none');
        }
    }

    function updateHouseholdInfoPanel(data) {
        if (!data) {
            clearHouseholdInfoPanel();
            return;
        }
        $('#bcp-hi-contact').text(formatHouseholdInfoText(data.contact_number));
        $('#bcp-hi-sub-location').text(formatHouseholdInfoText(data.sub_location));
        $('#bcp-hi-ward').text(formatHouseholdInfoText(data.ward));
        $('#bcp-hi-road-no').text(formatHouseholdInfoText(data.road_no));
        $('#bcp-hi-road-name').text(formatHouseholdInfoText(data.road_name));
        setHouseholdInfoVisible(true);
    }

    function clearHouseholdInfoPanel() {
        $('#bcp-hi-contact, #bcp-hi-sub-location, #bcp-hi-ward, #bcp-hi-road-no, #bcp-hi-road-name, #bcp-hi-area-mohalla')
            .text(householdInfoPlaceholder());
        setHouseholdInfoVisible(false);
    }

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

    function dueThroughMonthFirstDay(ym) {
        if (!ym || ym.length < 7) return null;
        var parts = ym.split('-');
        if (parts.length < 2) return null;
        var year = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10);
        if (!year || !month || month < 1 || month > 12) return null;
        var dt = new Date(year, month - 2, 1);
        var m = String(dt.getMonth() + 1).padStart(2, '0');
        return dt.getFullYear() + '-' + m + '-01';
    }

    function dueThroughMonthLabel(ym) {
        if (!ym || ym.length < 7) return '—';
        var parts = ym.split('-');
        if (parts.length < 2) return '—';
        var year = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10);
        if (!year || !month || month < 1 || month > 12) return '—';
        var dt = new Date(year, month - 2, 1);
        return dt.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
    }

    function formatCurrencyDisplay(value) {
        return window.ImisFormat.currency('tk', value);
    }

    function setNoDueState(isNoDue) {
        if (isNoDue) {
            $('.bcp-due-dependent-row').hide();
            $('#bcp-no-due-info').removeClass('d-none');
            $('#bcp-save-btn').prop('disabled', true);
            $('#due_paid').prop('required', false).attr('min', '0');
        } else {
            $('.bcp-due-dependent-row').show();
            $('#bcp-no-due-info').addClass('d-none');
            $('#bcp-save-btn').prop('disabled', false);
        }
    }

    function getSelectedPaymentMonthLabel() {
        var ym = $('#payment_for_month').val();
        if (!ym || ym.length < 7) return '—';
        var parts = ym.split('-');
        if (parts.length < 2) return '—';
        var year = parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10);
        if (!year || !month || month < 1 || month > 12) return '—';
        var dt = new Date(year, month - 1, 1);
        return dt.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
    }

    function updateNoDueInfo(currentData, dueData) {
        $('#bcp-no-due-holding').text($('#holding_number').val() || '—');
        $('#bcp-no-due-household').text(selectedHouseholdText || $('#household_code').val() || '—');
        $('#bcp-no-due-month').text(getSelectedPaymentMonthLabel());
        $('#bcp-no-due-waste-charge').text(
            (currentData.waste_charge === null || currentData.waste_charge === undefined)
                ? '—'
                : formatCurrencyDisplay(currentData.waste_charge)
        );
        $('#bcp-no-due-total-due').text(
            (dueData.due === null || dueData.due === undefined)
                ? '0'
                : formatCurrencyDisplay(dueData.due)
        );
    }

    function updateDueMonthLabel() {
        var label = dueThroughMonthLabel($('#payment_for_month').val());
        $('#bcp-due-month-label').text(label);
        $('#bcp-due-summary-month').text(label);
    }

    function refreshBalance() {
        var siteId = $('#household_id').val();
        var ym = $('#payment_for_month').val();
        var pm = monthFirstDay(ym);
        var duePm = dueThroughMonthFirstDay(ym);
        updateDueMonthLabel();
        if (!siteId || !pm || !duePm) {
            setBalanceLoading(false);
            $('#bcp-waste-charge').text('');
            $('#bcp-due').text('');
            setNoDueState(false);
            if (!siteId) {
                clearHouseholdInfoPanel();
            }
            return;
        }
        var seq = ++balanceRequestSeq;
        setBalanceLoading(true);
        function balanceRequestUrl(paymentMonth) {
            var url = balanceUrl + '?household_id=' + encodeURIComponent(siteId)
                + '&payment_for_month=' + encodeURIComponent(paymentMonth);
            if (excludePaymentId) {
                url += '&exclude_payment_id=' + encodeURIComponent(excludePaymentId);
            }
            return url;
        }
        $.when(
            $.getJSON(balanceRequestUrl(pm)),
            $.getJSON(balanceRequestUrl(duePm))
        ).done(function(currentRes, dueRes) {
            if (seq !== balanceRequestSeq) {
                return;
            }
            var data = currentRes[0];
            var dueData = dueRes[0];
            if (data.waste_charge === null || data.waste_charge === undefined) {
                $('#bcp-waste-charge').text('{{ __('Not Set') }}');
            } else {
                $('#bcp-waste-charge').text(formatCurrencyDisplay(data.waste_charge));
            }
            if (dueData.due === null || dueData.due === undefined) {
                $('#bcp-due').text('—');
            } else {
                $('#bcp-due').text(formatCurrencyDisplay(dueData.due));
            }
            var dueNumeric = Number(dueData.due);
            var hasNoDue = isFinite(dueNumeric) && dueNumeric <= 0;
            updateNoDueInfo(data, dueData);
            setNoDueState(hasNoDue);
            if (hasNoDue) {
                return;
            }
            if (data.current_month_fully_paid) {
                $('#bcp-current-amount-input-wrap').hide();
                $('input[name="amount"]').val('0');
                $('#bcp-current-month-paid-note').removeClass('d-none');
                $('#due_paid').prop('required', true).attr('min', '1');
                $('#bcp-current-amount-row').removeClass('required');
                $('#bcp-due-paid-row').addClass('required');
            } else {
                $('#bcp-current-amount-input-wrap').show();
                $('#bcp-current-month-paid-note').addClass('d-none');
                $('#due_paid').prop('required', false).attr('min', '0');
                $('#bcp-due-paid-row').removeClass('required');
            }
        }).fail(function() {
            if (seq !== balanceRequestSeq) {
                return;
            }
            $('#bcp-waste-charge').text('—');
            $('#bcp-due').text('—');
            setNoDueState(false);
            $('#bcp-current-amount-input-wrap').show();
            $('#bcp-current-month-paid-note').addClass('d-none');
            $('#due_paid').prop('required', false).attr('min', '0');
            $('#bcp-due-paid-row').removeClass('required');
        }).always(function() {
            if (seq === balanceRequestSeq) {
                setBalanceLoading(false);
            }
        });
    }

    $('#holding_select').select2({
        placeholder: '{{ __('Search Holding') }}',
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
        placeholder: '{{ __('Select Household') }}',
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
        clearHouseholdInfoPanel();
        refreshBalance();
    });

    $('#holding_select').on('select2:clear', function() {
        $('#holding_number').val('');
        $('#household_id').val('');
        $('#household_code').val('');
        $('#customer_site_select').prop('disabled', true).val(null).trigger('change');
        clearHouseholdInfoPanel();
        refreshBalance();
    });

    $('#customer_site_select').on('select2:select', function(e) {
        var d = e.params.data;
        $('#household_id').val(d.id);
        $('#household_code').val(d.household_id || '');
        selectedHouseholdText = d.text || '';
        if (d.holding_number) {
            $('#holding_number').val(d.holding_number);
        }
        updateHouseholdInfoPanel(d);
        refreshBalance();
    });

    $('#customer_site_select').on('select2:clear', function() {
        $('#household_id').val('');
        $('#household_code').val('');
        selectedHouseholdText = '';
        clearHouseholdInfoPanel();
        refreshBalance();
    });

    $('#payment_for_month').on('change', refreshBalance);
    updateDueMonthLabel();

    @if($initHolding !== '')
    (function initFromExisting() {
        var hn = @json($initHolding);
        var opt = new Option(hn, hn, true, true);
        $('#holding_select').append(opt).trigger('change');
        $('#customer_site_select').prop('disabled', false);
        var sid = @json(old('household_id', $isEdit ? $payment->household_id : ''));
        var cid = @json(old('household_code', $isEdit ? $payment->customer_id : ''));
        var cname = @json($initCustomerName ?? '');
        var label = (cname && cid) ? (cname + ' -- ' + cid) : (cname || cid || '');
        var copt = new Option(label, sid, true, true);
        $(copt).data('data', { id: sid, text: label, household_id: cid });
        $('#customer_site_select').append(copt).trigger('change');
        selectedHouseholdText = label;
        $('#household_id').val(sid);
        $('#household_code').val(cid);
        $('#holding_number').val(hn);
        if (initialHouseholdDetail) {
            updateHouseholdInfoPanel(initialHouseholdDetail);
        }
        refreshBalance();
    })();
    @endif
})();
</script>
@endpush
