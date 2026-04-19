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
               @if(isset($taxPayment)) readonly @endif placeholder="{{ __('Tax Code') }}" />
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
