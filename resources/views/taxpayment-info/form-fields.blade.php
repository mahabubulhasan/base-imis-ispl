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
