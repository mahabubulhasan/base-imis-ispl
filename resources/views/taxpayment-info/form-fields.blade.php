{{-- Last Modified: 2026-04-19 --}}
{{-- Developed By: Streams Tech Ltd. --}}
{{-- Description: Shared form fields for add/edit tax payment records --}}
@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="form-group row">
    <label for="tax_code" class="col-md-2 col-form-label">{{ __('Tax Code') }}</label>
    <div class="col-md-4">
        <input type="text" class="form-control @error('tax_code') is-invalid @enderror"
               id="tax_code" name="tax_code" value="{{ old('tax_code', $taxPayment->tax_code ?? '') }}"
               @if(isset($taxPayment)) readonly @endif placeholder="ww-rrr-hhhh-xx" />
        @error('tax_code')
        <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="bin" class="col-md-2 col-form-label">{{ __('BIN') }}</label>
    <div class="col-md-4">
        <select class="form-control @error('bin') is-invalid @enderror" id="bin" name="bin">
            <option value=""></option>
            @php $selectedBin = old('bin', $taxPayment->bin ?? null); @endphp
            @if(!empty($selectedBin))
                <option value="{{ $selectedBin }}" selected>{{ $selectedBin }}</option>
            @endif
        </select>
        @error('bin')
        <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="owner_name" class="col-md-2 col-form-label">{{ __('Owner Name') }} <span class="text-danger">*</span></label>
    <div class="col-md-4">
        <input type="text" class="form-control @error('owner_name') is-invalid @enderror"
               id="owner_name" name="owner_name" value="{{ old('owner_name', $taxPayment->owner_name ?? '') }}"
               placeholder="{{ __('Owner Name') }}" required />
        @error('owner_name')
        <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="owner_contact" class="col-md-2 col-form-label">{{ __('Owner Contact') }} <span class="text-danger">*</span></label>
    <div class="col-md-4">
        <input type="text" class="form-control @error('owner_contact') is-invalid @enderror"
               id="owner_contact" name="owner_contact" value="{{ old('owner_contact', $taxPayment->owner_contact ?? '') }}"
               placeholder="{{ __('Owner Contact') }}" required />
        @error('owner_contact')
        <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</div>

<div class="form-group row">
    <label for="last_payment_date" class="col-md-2 col-form-label">{{ __('Last Payment Date') }}</label>
    <div class="col-md-4">
        <input type="date" class="form-control @error('last_payment_date') is-invalid @enderror"
               id="last_payment_date" name="last_payment_date" value="{{ old('last_payment_date', $taxPayment->last_payment_date ?? '') }}"
               placeholder="{{ __('Last Payment Date') }}" />
        @error('last_payment_date')
        <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</div>

@push('scripts')
<script>
    (function () {
        // Input mask for tax_code: ww-rrr-hhhh-xx
        var taxCodeInput = document.getElementById('tax_code');
        if (!taxCodeInput || taxCodeInput.readOnly) return;

        var MASK  = 'AA-AAA-AAAA-AA';
        var HINT  = 'ww-rrr-hhhh-xx';

        // Build SLOTS (positions of user-editable chars) and HINT_CHARS in one pass.
        var SLOTS      = [];
        var HINT_CHARS = [];
        MASK.split('').forEach(function (ch, i) {
            if (ch === 'A') { SLOTS.push(i); HINT_CHARS.push(HINT[i]); }
        });

        // rawValue tracks only the characters the user has actually typed.
        var rawValue = (taxCodeInput.value || '').replace(/[^a-zA-Z0-9]/g, '').toUpperCase().slice(0, SLOTS.length);

        function buildDisplay() {
            return MASK.split('').map(function (ch, i) {
                if (ch !== 'A') return ch;
                var idx = SLOTS.indexOf(i);
                return rawValue[idx] !== undefined ? rawValue[idx] : HINT_CHARS[idx];
            }).join('');
        }

        // Returns the 0-based slot index at or after a cursor position.
        function slotIndexAt(pos) {
            for (var i = 0; i < SLOTS.length; i++) {
                if (SLOTS[i] >= pos) return i;
            }
            return SLOTS.length;
        }

        function setCursor(slotIdx) {
            var pos = slotIdx < SLOTS.length ? SLOTS[slotIdx] : SLOTS[SLOTS.length - 1] + 1;
            taxCodeInput.setSelectionRange(pos, pos);
        }

        function updateDisplay(cursorSlotIdx) {
            taxCodeInput.value = buildDisplay();
            setCursor(cursorSlotIdx);
        }

        taxCodeInput.addEventListener('keydown', function (e) {
            var pos      = this.selectionStart;
            var slotIdx  = slotIndexAt(pos);

            if (e.key === 'Backspace') {
                e.preventDefault();
                var prevSlotIdx = slotIdx - 1;
                if (prevSlotIdx >= 0 && prevSlotIdx < rawValue.length) {
                    rawValue = rawValue.slice(0, prevSlotIdx) + rawValue.slice(prevSlotIdx + 1);
                    updateDisplay(prevSlotIdx);
                } else if (prevSlotIdx >= 0) {
                    setCursor(prevSlotIdx);
                }

            } else if (e.key === 'Delete') {
                e.preventDefault();
                if (slotIdx < rawValue.length) {
                    rawValue = rawValue.slice(0, slotIdx) + rawValue.slice(slotIdx + 1);
                    updateDisplay(slotIdx);
                }

            } else if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                var char = e.key.toUpperCase();
                if (/[A-Z0-9]/.test(char) && slotIdx < SLOTS.length) {
                    rawValue = rawValue.slice(0, slotIdx) + char + rawValue.slice(slotIdx + 1);
                    if (rawValue.length > SLOTS.length) rawValue = rawValue.slice(0, SLOTS.length);
                    updateDisplay(slotIdx + 1);
                }
            }
            // Allow Tab, arrows, Ctrl+A/C/V etc. (no preventDefault)
        });

        taxCodeInput.addEventListener('paste', function (e) {
            e.preventDefault();
            var pasted  = (e.clipboardData || window.clipboardData).getData('text');
            var cleaned = pasted.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
            var slotIdx = slotIndexAt(this.selectionStart);
            for (var i = 0; i < cleaned.length && (slotIdx + i) < SLOTS.length; i++) {
                rawValue = rawValue.slice(0, slotIdx + i) + cleaned[i] + rawValue.slice(slotIdx + i + 1);
            }
            if (rawValue.length > SLOTS.length) rawValue = rawValue.slice(0, SLOTS.length);
            updateDisplay(Math.min(slotIdx + cleaned.length, SLOTS.length));
        });

        taxCodeInput.addEventListener('focus', function () {
            this.value = buildDisplay();
            setCursor(slotIndexAt(this.selectionStart));
        });

        taxCodeInput.addEventListener('blur', function () {
            // Clear so the HTML placeholder attribute shows when empty.
            if (rawValue.length === 0) this.value = '';
        });

        taxCodeInput.addEventListener('click', function () {
            setCursor(slotIndexAt(this.selectionStart));
        });

        // Ensure an empty ghost display is never submitted to the server.
        var form = taxCodeInput.form;
        if (form) {
            form.addEventListener('submit', function () {
                if (rawValue.length === 0) taxCodeInput.value = '';
            });
        }

        // Seed the formatted display if value already exists (e.g., old() after validation error).
        if (rawValue.length > 0) taxCodeInput.value = buildDisplay();
    })();

    $('#bin').select2({
        ajax: {
            url: "{{ route('tax-payment.getBins') }}",
            data: function (params) {
                return {
                    search: params.term,
                    page: params.page || 1
                };
            },
            processResults: function (data) {
                return {
                    results: data.results,
                    pagination: data.pagination
                };
            }
        },
        placeholder: "{{ __('Select BIN') }}",
        allowClear: true,
        closeOnSelect: true,
        width: '100%'
    });
</script>
@endpush
