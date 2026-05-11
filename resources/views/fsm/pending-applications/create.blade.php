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

		$(document).ready(function() {
			const today = new Date().toISOString().split('T')[0];
			$('#proposed_emptying_date').attr('min', today);

			$('#has_tax_id').on('change', togglePendingTaxIdField);
			togglePendingTaxIdField();
		});
	</script>
@endpush
