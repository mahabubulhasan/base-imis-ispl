{{--
    Tax Code / Holding ID field.

    A universal chip/tokenizer layer wraps EVERY mode: the user adds one tax code
    at a time and can enter multiple comma-separated codes. A chip is committed when
    the user types ', ' (comma then space), presses Enter, or pastes a comma-list.

    Only the PER-CHIP rule differs by config('tax_code.mode'):
      - 'legacy' : each chip auto-formats to XX-XXX-XXXX-XX and must match config('tax_code.legacy_format')
      - 'regex'  : each chip is limited to config('tax_code.allowed_chars') (comma excluded — it's the separator)

    Submission is unchanged: hidden 'tax_code' holds the comma-joined chips, validated
    server-side by App\Http\Requests\BuildingInfo\BuildingRequest::taxCodeRules().
--}}
@php
    $taxCodeValue = old(
        'tax_code',
        isset($building) ? $building->tax_code : (isset($buildingSurvey) ? $buildingSurvey->tax_code : null),
    );
    $taxCodeMode = config('tax_code.mode', 'regex');
    // Per-chip character set = allowed_chars minus the comma (comma is the chip separator).
    $taxChipChars = str_replace(',', '', config('tax_code.allowed_chars', '0-9a-zA-Z,\-\/\[\]{}()<>'));
@endphp

<!-- Tax ID (universal chip / multi-code input) -->
<div class="form-group row">
    {!! Form::label('tax_code', __('Tax Code/Holding ID'), ['class' => 'col-sm-3 control-label ']) !!}
    <div class="col-sm-5">
        {{-- Hidden input holds the final comma-separated values for submission --}}
        {!! Form::hidden('tax_code', $taxCodeValue, ['id' => 'tax_code_hidden']) !!}

        {{-- Visible chip input UI --}}
        <div id="tax-code-tag-input" class="form-control col-sm-10"
            style="height:auto;min-height:42px;padding:6px;display:flex;align-items:center;flex-wrap:wrap;cursor:text;">
            <ul id="tax-code-tags" style="list-style:none;display:flex;flex-wrap:wrap;padding:0;margin:0"></ul>
            <input id="tax_code_input" type="text" placeholder="Tax Code/Holding ID"
                autocomplete="off" style="border:0;outline:0;flex:1;min-width:150px;padding:5px;" />
        </div>
        <small id="tax-code-error" class="form-text text-danger" style="display:none;margin-top:4px;"></small>
    </div>
</div>

<script>
    // Universal tax-code chip input (works for all config('tax_code.mode') values).
    (function () {
        const input = document.getElementById('tax_code_input');
        const tagsList = document.getElementById('tax-code-tags');
        const hidden = document.getElementById('tax_code_hidden');
        const errorEl = document.getElementById('tax-code-error');

        const MODE = @json($taxCodeMode);
        const LEGACY_RE = new RegExp('^' + @json(config('tax_code.legacy_format', '\d{2}-\d{3}-\d{4}-[0-9x]{2}')) + '$', 'i');
        const CHIP_CHARS = @json($taxChipChars);
        const CHIP_RE = new RegExp('^[' + CHIP_CHARS + ']+$');
        const STRIP_RE = new RegExp('[^' + CHIP_CHARS + ']', 'g');
        const MAX_RAW = 11; // legacy: 2+3+4+2

        // Legacy auto-format into 00-000-0000-00 (allows trailing 'x').
        function formatLegacy(str) {
            let v = (str || '').toLowerCase().replace(/[^0-9x]/g, '');
            const xCount = (v.match(/x/g) || []).length;
            let digits = v.replace(/x/g, '');
            if (digits.length + xCount > MAX_RAW) digits = digits.slice(0, MAX_RAW);
            const xs = 'x'.repeat(Math.min(xCount, Math.max(0, MAX_RAW - digits.length)));
            const clean = (digits + xs).slice(0, MAX_RAW);
            const lens = [2, 3, 4, 2];
            const groups = [];
            let idx = 0;
            for (let i = 0; i < lens.length && idx < clean.length; i++) {
                groups.push(clean.substr(idx, lens[i]));
                idx += lens[i];
            }
            return groups.join('-');
        }

        // Per-mode single-token formatting and validation.
        function formatToken(raw) {
            raw = (raw || '').trim();
            return MODE === 'legacy' ? formatLegacy(raw) : raw.replace(STRIP_RE, '');
        }
        function isValidToken(formatted) {
            return MODE === 'legacy' ? LEGACY_RE.test(formatted) : CHIP_RE.test(formatted);
        }
        function invalidMessage() {
            return MODE === 'legacy'
                ? @json(__('Tax Code must be in XX-XXX-XXXX-XX format. "x" allowed only in the last two characters.'))
                : @json(__('Tax Code contains invalid characters.'));
        }

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

        // Commit the current text (or a given raw value) as a chip.
        function commit(raw) {
            const formatted = formatToken(raw);
            if (!formatted) return;
            if (!isValidToken(formatted)) {
                showError(invalidMessage());
                return;
            }
            const existing = Array.from(tagsList.querySelectorAll('li')).map(n => n.getAttribute('data-value'));
            if (!existing.includes(formatted)) renderTag(formatted);
            input.value = '';
            clearError();
            updateHidden();
        }

        // Live-format the visible token while typing (it never contains a comma).
        input.addEventListener('input', function () {
            const formatted = formatToken(input.value);
            if (formatted !== input.value) {
                input.value = formatted;
                input.setSelectionRange(input.value.length, input.value.length);
            }
            clearError();
        });

        // Trigger: ', ' (comma then space) or Enter. Backspace on empty removes the last chip.
        let armed = false; // set right after a comma; a following space commits.
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                commit(input.value);
                armed = false;
            } else if (e.key === ',') {
                e.preventDefault(); // swallow the comma; wait for the space
                armed = true;
            } else if (e.key === ' ' && armed) {
                e.preventDefault();
                commit(input.value);
                armed = false;
            } else if (e.key === 'Backspace' && input.value === '') {
                const last = tagsList.querySelector('li:last-child');
                if (last) {
                    tagsList.removeChild(last);
                    updateHidden();
                }
                armed = false;
            } else if (e.key !== ' ') {
                armed = false; // comma must be immediately followed by a space
            }
        });

        // Paste: split a comma-separated paste into multiple chips.
        input.addEventListener('paste', function (e) {
            const text = ((e.clipboardData || window.clipboardData).getData('text') || '');
            if (text.indexOf(',') === -1) return; // single value: let it land in the input normally
            e.preventDefault();
            text.split(',').map(s => s.trim()).filter(Boolean).forEach(commit);
        });

        // Commit any leftover text when the field loses focus.
        input.addEventListener('blur', function () {
            if (input.value.trim() !== '') commit(input.value);
        });

        // Prepopulate chips from an existing comma-separated value (model binding or old input).
        function preload() {
            const val = hidden.value || @json($taxCodeValue ?? '');
            if (!val) return;
            String(val).split(',').map(s => s.trim()).filter(Boolean).forEach(function (it) {
                const formatted = formatToken(it);
                if (formatted && isValidToken(formatted)) renderTag(formatted);
            });
            updateHidden();
        }

        document.addEventListener('DOMContentLoaded', preload);
    })();
</script>
