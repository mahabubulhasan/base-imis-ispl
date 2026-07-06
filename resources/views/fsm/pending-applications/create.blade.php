<!-- Last Modified: April 8, 2026
Developed By: Streams Tech Ltd.
Description: Displays the pending FSM application create form using the shared card-form layout. -->
@extends('layouts.dashboard')

@section('title', __('Add Application'))

@section('content')
	@include('layouts.components.error-list')
	@include('layouts.components.success-alert')
	@include('layouts.components.error-alert')

	{!! Form::open(['url' => $formAction, 'class' => 'form-horizontal', 'id' => 'create_pending_application_form']) !!}
	@include('layouts.partial-form', ['submitButtonText' => __('Save'), 'cardForm' => true])
	{!! Form::close() !!}
@endsection

@push('style')
	<style>
		/* Radio Button Styling */
		.radio-options-container {
			display: flex;
			flex-wrap: wrap;
			gap: 2rem;
			margin-top: 0.5rem;
		}

		.form-check {
			display: flex;
			align-items: center;
			gap: 0.5rem;
		}

		.form-check-input {
			cursor: pointer;
			width: 1.2rem;
			height: 1.2rem;
			margin: 0;
		}

		.form-check-label {
			cursor: pointer;
			margin: 0;
			font-weight: 500;
			color: #333;
			user-select: none;
			font-size: 0.95rem;
		}

		.form-check-input:checked + .form-check-label {
			color: #007bff;
			font-weight: 600;
		}

		.form-check-input[type="checkbox"]:checked {
			background-color: #007bff;
			border-color: #007bff;
		}

		/* Additional spacing for form groups */
		.form-group {
			margin-bottom: 1.5rem;
		}

		.field-hidden {
			display: none !important;
		}

		/* Card spacing improvement */
		.card-body {
			padding: 2rem;
		}

		/* Checkbox specific styling */
		.form-check.mt-2 {
			margin-top: 0.5rem !important;
		}

		.font-weight-500 {
			font-weight: 500;
		}
	</style>
@endpush

@push('scripts')
	<script>
		function formatPendingTaxId(element) {
			const digitsOnly = element.value.replace(/[^0-9]/g, '');
			const blocks = [2, 3, 4, 2];
			let formatted = '';
			let index = 0;

			blocks.forEach(function(blockSize, blockIndex) {
				if (index >= digitsOnly.length) {
					return;
				}

				if (blockIndex > 0 && formatted.length > 0) {
					formatted += '-';
				}

				formatted += digitsOnly.substring(index, index + blockSize);
				index += blockSize;
			});

			element.value = formatted;
		}

		function formatPendingContact(element) {
			element.value = element.value.replace(/[^0-9]/g, '').substring(0, 11);
		}

		function togglePendingTaxIdField() {
			const hasTaxId = $('#has_tax_id').val() === 'yes';
			const taxIdFieldWrapper = $('#tax_id').closest('.form-group');

			taxIdFieldWrapper.toggle(hasTaxId);

			if (!hasTaxId) {
				$('#tax_id').val('');
			}
		}

		function togglePaymentAmountFields() {
			const paymentMethodCheckbox = $('#payment_method').is(':checked');
			const amountWrapper = $('#form-group-amount');

			console.log('Payment method checkbox checked:', paymentMethodCheckbox);
			console.log('Amount wrapper found:', amountWrapper.length > 0);

			if (paymentMethodCheckbox) {
				amountWrapper.removeClass('field-hidden');
			} else {
				amountWrapper.addClass('field-hidden');
				$('input[name="amount"]').prop('checked', false);
			}
		}

		$(document).ready(function() {
			const today = new Date().toISOString().split('T')[0];
			$('#proposed_emptying_date').attr('min', today);

			// Initial setup
			$('#has_tax_id').on('change', togglePendingTaxIdField);
			togglePendingTaxIdField();

			// Payment method handling
			$('#payment_method').on('change', togglePaymentAmountFields);
			togglePaymentAmountFields();

			console.log('Form initialized');
		});
	</script>
@endpush
