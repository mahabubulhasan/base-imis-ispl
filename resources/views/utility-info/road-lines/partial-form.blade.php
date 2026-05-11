{{-- Last Modified: 2026-05-01 --}}
{{-- Developed By: Streams Tech Ltd. --}}
{{-- Description: Edit road network form aligned with the map add-road fields and value behavior. --}}
@php
	$currentRoadUid = old('road_uid', $roadline->road_uid ?? $roadline->code ?? '');
	$currentRoadExt = old('road_ext', $roadline->road_ext ?? '');
	$currentRoadCode = old('road_code', $currentRoadExt ? $currentRoadUid . '-' . $currentRoadExt : $currentRoadUid);
	$selectedRoadType = old('road_type', $roadline->road_type ?? '');
	$selectedWard = old('ward', $roadline->ward ?? '');
	$useExtension = (bool) old('use_extension', !empty($currentRoadExt));
	$baseRoadCode = old('base_road_code', $currentRoadExt ? $currentRoadUid : '');
	$serialNumber = old('serial_number', preg_match('/(\d{4})$/', (string) $currentRoadUid, $serialMatch) ? $serialMatch[1] : '');
@endphp
<div class="card-body">
	<div class="form-group row">
		{!! Form::label('code',__('Code'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-5">
			{!! Form::text('code', $roadline->code, ['class' => 'form-control', 'disabled' => 'true', 'placeholder' => __('Code')]) !!}
		</div>
	</div>

	<div class="form-group row required">
		{!! Form::label('name',__('Road Name') . ' <span style="color: red">*</span>',['class' => 'col-sm-3 control-label'], false) !!}
		<div class="col-sm-5">
			{!! Form::text('name',null,['class' => 'form-control', 'placeholder' => __('Road Name')]) !!}
		</div>
	</div>

	<div class="form-group row required">
		{!! Form::label('road_type',__('Road Type') . ' <span style="color: red">*</span>',['class' => 'col-sm-3 control-label'], false) !!}
		<div class="col-sm-5">
			{!! Form::select('road_type', $roadTypes, $selectedRoadType, ['class' => 'form-control', 'placeholder' => __('Road Type'), 'id' => 'road_type']) !!}
		</div>
	</div>

	<div id="municipality_fields_container" style="display: none;">
		<div class="form-group row required">
			{!! Form::label('ward',__('Ward') . ' <span style="color: red">*</span>',['class' => 'col-sm-3 control-label'], false) !!}
			<div class="col-sm-5">
				{!! Form::select('ward', $wards, $selectedWard, ['class' => 'form-control', 'placeholder' => __('Ward'), 'id' => 'ward_select']) !!}
			</div>
		</div>

		{!! Form::hidden('serial_number', $serialNumber, ['id' => 'serial_number']) !!}
	</div>

	<div class="form-group row">
		{!! Form::label('hierarchy',__('Hierarchy'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-5">
			{!! Form::select('hierarchy', $roadHierarchy, null, ['class' => 'form-control', 'placeholder' => __('Road Hierarchy'), 'id' => 'hierarchy']) !!}
		</div>
	</div>

	<div class="form-group row required">
		{!! Form::label('right_of_way',__('Right of Way (m)') . ' <span style="color: red">*</span>',['class' => 'col-sm-3 control-label'], false) !!}
		<div class="col-sm-5">
			{!! Form::number('right_of_way',null,['class' => 'form-control', 'placeholder' => __('Right of Way (m)'), 'min' => 1, 'step' => 'any']) !!}
		</div>
	</div>

	<div class="form-group row required">
		{!! Form::label('carrying_width',__('Carrying Width (m)') . ' <span style="color: red">*</span>',['class' => 'col-sm-3 control-label'], false) !!}
		<div class="col-sm-5">
			{!! Form::number('carrying_width',null,['class' => 'form-control', 'placeholder' => __('Carrying Width (m)'), 'min' => 1, 'step' => 'any']) !!}
		</div>
	</div>

	<div class="form-group row">
		{!! Form::label('surface_type',__('Surface Type'),['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-5">
			{!! Form::select('surface_type', $roadSurfaceTypes, null, ['class' => 'form-control', 'placeholder' => __('Road Surface Type'), 'id' => 'surface_type']) !!}
		</div>
	</div>

	<div class="form-group row required">
		{!! Form::label('length',__('Length (m)') . ' <span style="color: red">*</span>',['class' => 'col-sm-3 control-label'], false) !!}
		<div class="col-sm-5">
			{!! Form::number('length',null,['class' => 'form-control', 'placeholder' => __('Road Length (m)'), 'min' => 1, 'step' => 'any']) !!}
		</div>
	</div>

	<div class="form-group row required">
		{!! Form::label('road_code',__('Road Code') . ' <span style="color: red">*</span>',['class' => 'col-sm-3 control-label'], false) !!}
		<div class="col-sm-5">
			{!! Form::text('road_code', $currentRoadCode, ['class' => 'form-control', 'placeholder' => __('Road Code'), 'id' => 'road_code_field']) !!}
			<small id="municipality_road_hint" style="color: #666; display: none; margin-top: 5px;">{{ __('Municipality Road: auto-generated as 20512510 + Ward(2 digits) + Serial(4 digits)') }}</small>
		</div>
	</div>

	{!! Form::hidden('road_uid', $currentRoadUid, ['id' => 'road_uid']) !!}
	{!! Form::hidden('road_ext', $currentRoadExt, ['id' => 'road_ext']) !!}

	<div class="form-group row">
		<div class="col-sm-3"></div>
		<div class="col-sm-5">
			<div class="custom-control custom-checkbox">
				{!! Form::checkbox('use_extension', 1, $useExtension, ['class' => 'custom-control-input', 'id' => 'use_extension']) !!}
				{!! Form::label('use_extension',__('Use Extension'),['class' => 'custom-control-label'], false) !!}
			</div>
			<small style="color: #666; display: block; margin-top: 5px;">{{ __('If enabled, Road Code above will be filled by Base Road Code selection + 2-digit extension.') }}</small>
		</div>
	</div>

	<div id="extension_fields_container" style="display: {{ $useExtension ? 'block' : 'none' }};">
		<div class="form-group row">
			{!! Form::label('base_road_code',__('Base Road Code') . ' <span style="color: red">*</span>',['class' => 'col-sm-3 control-label'], false) !!}
			<div class="col-sm-5">
				{!! Form::select('base_road_code', $baseRoadCode ? [$baseRoadCode => $baseRoadCode] : [], $baseRoadCode, ['class' => 'form-control', 'placeholder' => __('Select existing road code'), 'id' => 'base_road_code']) !!}
			</div>
		</div>

		<div class="form-group row">
			{!! Form::label('extension',__('Extension (2 digits)') . ' <span style="color: red">*</span>',['class' => 'col-sm-3 control-label'], false) !!}
			<div class="col-sm-5">
				{!! Form::text('extension', $currentRoadExt, ['class' => 'form-control', 'placeholder' => __('01'), 'id' => 'extension', 'maxlength' => '2', 'pattern' => '[0-9]{2}']) !!}
			</div>
		</div>
	</div>

</div><!-- /.card-body -->
<div class="card-footer">
	<a href="{{ action('UtilityInfo\RoadlineController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
	{!! Form::submit(__('Save'), ['class' => 'btn btn-info', 'id' => 'edit_road_submit_btn']) !!}
</div><!-- /.card-footer -->

@push('scripts')
<script>
	$(function() {
		const roadTypeSelect = $('#road_type');
		const municipalityFieldsContainer = $('#municipality_fields_container');
		const hierarchySelect = $('#hierarchy');
		const municipalityRoadHint = $('#municipality_road_hint');
		const useExtensionCheckbox = $('#use_extension');
		const extensionFieldsContainer = $('#extension_fields_container');
		const wardSelect = $('#ward_select');
		const serialNumberInput = $('#serial_number');
		const roadCodeField = $('#road_code_field');
		const roadUidInput = $('#road_uid');
		const roadExtInput = $('#road_ext');
		const baseRoadCodeSelect = $('#base_road_code');
		const extensionInput = $('#extension');
		const editRoadForm = $('#edit_road_submit_btn').closest('form');
		const initialWard = @json((string) $selectedWard);
		const initialSerial = @json((string) $serialNumber);
		const initialBaseRoadCode = @json((string) $baseRoadCode);
		const initialRoadExtension = @json((string) $currentRoadExt);
		let loadedRoadData = [];
		let usedInitialSerial = false;

		function applyRoadCodeReadonlyState() {
			const isMunicipalityRoad = roadTypeSelect.val() === 'MunicipalityRoad';
			const usesExtension = useExtensionCheckbox.is(':checked');
			roadCodeField.prop('readonly', isMunicipalityRoad || usesExtension);
		}

		function syncSubmittedRoadValues() {
			if (useExtensionCheckbox.is(':checked')) {
				roadUidInput.val(baseRoadCodeSelect.val() || '');
				roadExtInput.val(extensionInput.val() || '');

				if (baseRoadCodeSelect.val() && extensionInput.val()) {
					roadCodeField.val(baseRoadCodeSelect.val() + '-' + extensionInput.val());
				}
			} else {
				roadUidInput.val(roadCodeField.val() || '');
				roadExtInput.val('');
			}
		}

		function computeNextExtension(roads) {
			const extensionNumbers = (roads || []).map(function(road) {
				const match = (road.code || '').match(/-(\d{2})$/);
				return match ? parseInt(match[1], 10) : null;
			}).filter(function(value) {
				return value !== null && !isNaN(value);
			});

			if (extensionNumbers.length > 0) {
				const maxExtension = Math.max.apply(null, extensionNumbers);
				return String(Math.min(maxExtension + 1, 99)).padStart(2, '0');
			} else if (roads && roads.length > 0) {
				return '01';
			}

			return '';
		}

		function generateMunicipalityRoadCode(ward, serial) {
			const paddedWard = String(ward).padStart(2, '0');
			const paddedSerial = String(serial).padStart(4, '0');
			return '20512510' + paddedWard + paddedSerial;
		}

		function updateRoadCode() {
			if (roadTypeSelect.val() === 'MunicipalityRoad') {
				const ward = wardSelect.val();
				const serial = serialNumberInput.val();

				if (ward && serial) {
					roadCodeField.val(generateMunicipalityRoadCode(ward, serial));
				}
			}

			syncSubmittedRoadValues();
		}

		function loadBaseRoadCodes(ward, selectedBaseRoadCode, selectedExtension) {
			const roadType = roadTypeSelect.val();

			if (!ward && !roadType) {
				loadedRoadData = [];
				baseRoadCodeSelect.html('<option value="">{{ __("Select existing road code") }}</option>');
				syncSubmittedRoadValues();
				return;
			}

			baseRoadCodeSelect.html('<option value="">{{ __("Loading...") }}</option>');

			$.ajax({
				url: '{{ url("/utilityinfo/roadlines/get-by-ward") }}',
				type: 'GET',
				data: { ward: ward, road_type: roadType },
				dataType: 'json',
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(data) {
					loadedRoadData = data || [];
					let options = '<option value="">{{ __("Select existing road code") }}</option>';

					if (loadedRoadData.length > 0) {
						$.each(loadedRoadData, function(index, road) {
							options += '<option value="' + road.road_uid + '">' + road.name + ' (' + road.code + ')</option>';
						});
					} else {
						options = '<option value="">{{ __("No roads found for selected filters") }}</option>';
					}

					baseRoadCodeSelect.html(options);

					if (selectedBaseRoadCode) {
						baseRoadCodeSelect.val(selectedBaseRoadCode);
					}

					if (selectedExtension) {
						extensionInput.val(selectedExtension);
					} else if (!selectedBaseRoadCode) {
						extensionInput.val('');
					}

					syncSubmittedRoadValues();
				},
				error: function() {
					loadedRoadData = [];
					baseRoadCodeSelect.html('<option value="">{{ __("Error loading roads") }}</option>');
					extensionInput.val('');
					syncSubmittedRoadValues();
				}
			});
		}

		roadTypeSelect.on('change', function() {
			const isMunicipalityRoad = $(this).val() === 'MunicipalityRoad';

			municipalityFieldsContainer.toggle(isMunicipalityRoad);
			municipalityRoadHint.toggle(isMunicipalityRoad);

			if (isMunicipalityRoad && !hierarchySelect.val()) {
				hierarchySelect.val('Primary');
			}

			applyRoadCodeReadonlyState();

			if (isMunicipalityRoad) {
				if (wardSelect.val()) {
					wardSelect.trigger('change');
				} else {
					updateRoadCode();
				}
			}

			if (useExtensionCheckbox.is(':checked')) {
				loadBaseRoadCodes(wardSelect.val());
			} else {
				syncSubmittedRoadValues();
			}
		});

		useExtensionCheckbox.on('change', function() {
			if ($(this).is(':checked')) {
				extensionFieldsContainer.slideDown();
				loadBaseRoadCodes(wardSelect.val(), baseRoadCodeSelect.val() || initialBaseRoadCode, extensionInput.val() || initialRoadExtension);
			} else {
				extensionFieldsContainer.slideUp();
				baseRoadCodeSelect.html('<option value="">{{ __("Select existing road code") }}</option>');
				extensionInput.val('');
			}

			applyRoadCodeReadonlyState();
			syncSubmittedRoadValues();
		});

		wardSelect.on('change', function() {
			const ward = $(this).val();

			if (roadTypeSelect.val() === 'MunicipalityRoad') {
				if (ward) {
					if (!usedInitialSerial && initialSerial && String(ward) === String(initialWard)) {
						serialNumberInput.val(initialSerial);
						usedInitialSerial = true;
						updateRoadCode();
					} else {
						serialNumberInput.val('Loading...');

						$.ajax({
							url: '{{ url("maps/get-next-road-serial") }}/' + ward,
							type: 'GET',
							dataType: 'json',
							headers: {
								'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
							},
							success: function(response) {
								if (response.success) {
									serialNumberInput.val(response.serial);
									updateRoadCode();
								} else {
									serialNumberInput.val('');
								}
							},
							error: function() {
								serialNumberInput.val('');
							}
						});
					}
				} else {
					serialNumberInput.val('');
				}
			}

			if (useExtensionCheckbox.is(':checked')) {
				loadBaseRoadCodes(ward);
			}
		});

		baseRoadCodeSelect.on('change', function() {
			const selectedRoadUid = $(this).val();

			if (selectedRoadUid) {
				const selectedRoad = loadedRoadData.find(function(road) {
					return String(road.road_uid) === String(selectedRoadUid);
				});

				const baseCodePattern = selectedRoad
					? (selectedRoad.code || '').replace(/-\d{2}$/, '')
					: '';

				const relatedRoads = baseCodePattern
					? loadedRoadData.filter(function(road) {
						return (road.code || '').replace(/-\d{2}$/, '') === baseCodePattern;
					})
					: loadedRoadData;

				const nextExtension = computeNextExtension(relatedRoads);
				extensionInput.val(nextExtension);

				if (nextExtension) {
					roadCodeField.val(selectedRoadUid + '-' + nextExtension);
				}
			} else {
				extensionInput.val('');
				roadCodeField.val('');
			}

			syncSubmittedRoadValues();
		});

		extensionInput.on('change input', function() {
			const baseRoadCode = baseRoadCodeSelect.val();
			const extension = $(this).val();

			if (baseRoadCode && extension) {
				roadCodeField.val(baseRoadCode + '-' + extension);
			}

			syncSubmittedRoadValues();
		});

		roadCodeField.on('change input', syncSubmittedRoadValues);
		editRoadForm.on('submit', syncSubmittedRoadValues);

		applyRoadCodeReadonlyState();
		roadTypeSelect.trigger('change');

		if (useExtensionCheckbox.is(':checked')) {
			loadBaseRoadCodes(wardSelect.val(), initialBaseRoadCode, initialRoadExtension);
		} else {
			syncSubmittedRoadValues();
		}
	});
</script>
@endpush
