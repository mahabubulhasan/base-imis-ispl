<select class="form-control doe-yn-select" name="{{ $name }}" @if(!empty($auto)) data-auto="1" @endif>
    <option value="">-- {{ __('Select') }} --</option>
    <option value="yes" @selected(($value ?? '') === 'yes')>হ্যাঁ</option>
    <option value="no" @selected(($value ?? '') === 'no')>না</option>
</select>
