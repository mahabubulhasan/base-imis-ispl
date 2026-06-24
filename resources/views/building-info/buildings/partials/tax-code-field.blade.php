{{--
    Tax Code / Holding ID field.

    Renderer + validation are driven by config('tax_code.mode'):
      - 'legacy' : tag-list UI; each tag must match config('tax_code.legacy_format')
      - 'regex'  : single free-text input filtered by config('tax_code.allowed_chars')

    Server-side validation mirrors this in
    App\Http\Requests\BuildingInfo\BuildingRequest::taxCodeRules().
--}}
@php
    $taxCodeValue = old(
        'tax_code',
        isset($building) ? $building->tax_code : (isset($buildingSurvey) ? $buildingSurvey->tax_code : null),
    );
    $taxCodeMode = config('tax_code.mode', 'regex');
@endphp

@if ($taxCodeMode === 'legacy')
    <!-- Tax ID (legacy tag-list mode) -->
    <div class="form-group row">
        {!! Form::label('tax_code', __('Tax Code/Holding ID'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {{-- Hidden input that holds the final comma-separated values for submission --}}
            {!! Form::hidden('tax_code', $taxCodeValue, ['id' => 'tax_code_hidden']) !!}

            {{-- Visible tag input UI --}}
            <div id="tax-code-tag-input" class="form-control col-sm-10"
                style="min-height:42px;padding:6px;display:flex;align-items:center;flex-wrap:wrap;cursor:text;">
                <ul id="tax-code-tags" style="list-style:none;display:flex;flex-wrap:wrap;padding:0;margin:0"></ul>
                <input id="tax_code_input" type="text" placeholder="" autocomplete="off"
                    style="border:0;outline:0;flex:1;min-width:150px;padding:5px;" />
            </div>
            <small id="tax-code-error" class="form-text text-danger" style="display:none;margin-top:4px;"></small>
        </div>
    </div>
@else
    <!-- Tax ID (regex / free-text mode) -->
    <div class="form-group row">
        {!! Form::label('tax_code', __('Tax Code/Holding ID'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::text('tax_code', $taxCodeValue, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Tax Code/Holding ID',
                'autocomplete' => 'off',
                'maxlength' => config('tax_code.max_length', 250),
                'oninput' => "this.value = this.value.replace(/[^" . config('tax_code.allowed_chars') . "]/g, '')",
            ]) !!}
        </div>
    </div>
@endif

@if ($taxCodeMode === 'legacy')
    <script>
        // Tag input for Tax Code/Holding ID (legacy mode)
        (function () {
            const input = document.getElementById('tax_code_input');
            const tagsList = document.getElementById('tax-code-tags');
            const hidden = document.getElementById('tax_code_hidden');
            const errorEl = document.getElementById('tax-code-error');
            const TAG_FORMAT = /^{!! config('tax_code.legacy_format') !!}$/i;
            const MAX_RAW = 11; // 2+3+4+2

            // Format raw input into 00-000-0000-00 (allows 'x' only at end groups via handling)
            function formatTaxCodeValue(str) {
                let v = (str || '').toLowerCase();
                v = v.replace(/[^0-9x]/g, '');
                const xCount = (v.match(/x/g) || []).length;
                let digits = v.replace(/x/g, '');
                if (digits.length + xCount > MAX_RAW) {
                    const allowedDigits = Math.min(digits.length, MAX_RAW);
                    digits = digits.slice(0, allowedDigits);
                }
                const xs = 'x'.repeat(Math.min(xCount, Math.max(0, MAX_RAW - digits.length)));
                const clean = (digits + xs).slice(0, MAX_RAW);
                const lens = [2,3,4,2];
                const groups = [];
                let idx = 0;
                for (let i = 0; i < lens.length && idx < clean.length; i++) {
                    groups.push(clean.substr(idx, lens[i]));
                    idx += lens[i];
                }
                return groups.join('-');
            }

            // Render a tag element
            function renderTag(value) {
                const li = document.createElement('li');
                li.className = 'tax-tag';
                li.style.cssText = 'display:flex;align-items:center;background:#e9f2ff;margin:4px 6px;padding:4px 8px;border-radius:12px;font-size:0.9em;';
                li.setAttribute('data-value', value);

                const span = document.createElement('span');
                span.textContent = value;
                span.style.marginRight = '8px';

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.innerHTML = '&times;';
                btn.style.cssText = 'border:0;background:transparent;cursor:pointer;font-size:1em;line-height:1;padding:0;';
                btn.addEventListener('click', function () {
                    tagsList.removeChild(li);
                    updateHidden();
                });

                li.appendChild(span);
                li.appendChild(btn);
                tagsList.appendChild(li);
            }

            // Add tag if valid
            function addTag(raw) {
                const formatted = formatTaxCodeValue(raw);
                if (!formatted) return;
                if (!TAG_FORMAT.test(formatted)) {
                    showError('Tax Code must be in XX-XXX-XXXX-XX format. "x" allowed only in last two characters.');
                    return;
                }
                // avoid duplicates
                const existing = Array.from(tagsList.querySelectorAll('li')).map(n => n.getAttribute('data-value'));
                if (existing.includes(formatted)) {
                    input.value = '';
                    return clearError();
                }
                renderTag(formatted);
                input.value = '';
                clearError();
                updateHidden();
            }

            function updateHidden() {
                const vals = Array.from(tagsList.querySelectorAll('li')).map(n => n.getAttribute('data-value'));
                hidden.value = vals.join(',');
            }

            function showError(msg) {
                errorEl.textContent = msg;
                errorEl.style.display = 'block';
            }

            function clearError() {
                errorEl.textContent = '';
                errorEl.style.display = 'none';
            }

            // handle input formatting while typing
            input.addEventListener('input', function (e) {
                const formatted = formatTaxCodeValue(input.value);
                input.value = formatted;
                input.setSelectionRange(input.value.length, input.value.length);
                clearError();
            });

            // add tag on comma or Enter
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    addTag(input.value);
                } else if (e.key === 'Backspace' && input.value === '') {
                    const last = tagsList.querySelector('li:last-child');
                    if (last) {
                        tagsList.removeChild(last);
                        updateHidden();
                    }
                }
            });

            // also add tag when input loses focus (if any content)
            input.addEventListener('blur', function () {
                const val = input.value.trim();
                if (val !== '') addTag(val);
            });

            // prepopulate from existing hidden value (model binding or old input)
            function preload() {
                const val = hidden.value || @json($taxCodeValue ?? '');
                if (!val) return;
                const items = val.split(',').map(s => s.trim()).filter(Boolean);
                items.forEach(it => {
                    const formatted = formatTaxCodeValue(it);
                    if (formatted && TAG_FORMAT.test(formatted)) renderTag(formatted);
                });
                updateHidden();
            }

            document.addEventListener('DOMContentLoaded', function () {
                preload();
            });
        })();
    </script>
@endif
