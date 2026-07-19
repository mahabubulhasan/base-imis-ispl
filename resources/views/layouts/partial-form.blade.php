<!-- Last Modified: April 8, 2026
Developed By: Streams Tech Ltd.
Description: Renders shared dynamic form layouts for standard and card-based forms. -->
{{--
A dynamic form layout
--}}

@if(!empty($cardForm))
    <div class="col-sm-12 col-md-8 col-lg-8">
    @foreach($formFields as $group)
            <div class="card" @if(!empty($group['id'])) id="{{ $group['id'] }}" @endif @if(!empty($group['hidden'])) @if($group['hidden'] === true) style="display: none" @endif @endif>
                <div class="card-header">
                    {{ $group['title'] }}
                    @if(!empty($group["copyDetails"]))
                        @if($group["copyDetails"])
                            <div class="clearfix float-right">
                                <div class="icheck-primary d-inline">
                                    <input type="checkbox" name="autofill" id="autofill" onclick="autoFillDetails()">
                                    <label for="autofill">
                                    {{ __('Same as Owner') }}
                                    </label>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="card-body">
                    @foreach($group['fields'] as $field)
                    <div class="form-group row @if($field->required) required @endif {{ ($field->hidden === true) ? 'field-hidden' : '' }}" id="form-group-{{ $field->inputId }}">
                        {!! Form::label($field->labelFor,$field->label,['class' => $field->labelClass]) !!}
                        <div class="col-sm-4">
                            @if($field->inputType === 'text')
                                {!! Form::text($field->inputId,$field->inputValue,['class' => $field->inputClass, 'placeholder' => $field->placeholder,'disabled' => $field->disabled,'autocomplete'=>$field->autoComplete, 'oninput'=>$field->oninput]) !!}
                                @if($field->disabled)
                                {!! Form::hidden($field->inputId,$field->inputValue) !!}
                                @endif
                            @endif
                            @if($field->inputType === 'textarea')
                                {!! Form::textarea($field->inputId, $field->inputValue, ['class' => $field->inputClass, 'placeholder' => $field->placeholder, 'rows' => 4, 'style' => 'resize:none', 'disabled' => $field->disabled]) !!}
                            @endif
                            @if($field->inputType === 'number')
                                {!! Form::number($field->inputId,$field->inputValue,['class' => $field->inputClass, 'placeholder' => $field->placeholder,'disabled' => $field->disabled, 'oninput'=>$field->oninput]) !!}
                            @endif
                            @if($field->inputType === 'select')
                                {!! Form::select($field->inputId,$field->selectValues,$field->selectedValue,['class' => $field->inputClass, 'placeholder' => $field->placeholder,'disabled' => $field->disabled]) !!}
                                @if($field->disabled)
                                {!! Form::hidden($field->inputId,$field->selectedValue) !!}
                                @endif
                            @endif
                            @if($field->inputType === 'label')
                                {!! Form::label($field->inputId,$field->labelValue,['class' => $field->inputClass,'disabled' => $field->disabled]) !!}
                            @endif
                            @if($field->inputType === 'radio')
                                <div class="radio-options-container">
                                    @foreach($field->radioValues as $radioValue => $radioLabel)
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="{{ $field->inputId }}" id="{{ $field->inputId }}_{{ $loop->index }}" value="{{ $radioValue }}" {{ ($field->selectedValue == $radioValue) ? 'checked' : '' }} {{ $field->disabled ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="{{ $field->inputId }}_{{ $loop->index }}">
                                            {{ $radioLabel }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                            @if($field->inputType === 'checkbox')
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" name="{{ $field->inputId }}" id="{{ $field->inputId }}" value="{{ $field->checkboxValue }}" {{ ($field->selectedValue == $field->checkboxValue) ? 'checked' : '' }} {{ $field->disabled ? 'disabled' : '' }} onchange="togglePaymentAmountFields()">
                                    <label class="form-check-label font-weight-500" for="{{ $field->inputId }}">
                                        {{ $field->label }}
                                    </label>
                                </div>
                            @endif
                            @if($field->inputType === 'multiple-select')
                                {!! Form::select($field->inputId,$field->selectValues,$field->selectedValue,['class' => $field->inputClass,'disabled' => $field->disabled]) !!}
                            @endif
                            @if($field->inputType === 'date')
                            {!! Form::date($field->inputId, $field->inputValue, [
                                'onclick' => 'this.showPicker()',
                                'class' => $field->inputClass,
                                'disabled' => $field->disabled,
                                'autocomplete' => 'off'
                            ]) !!}


                            @endif
                            @if($field->inputType === 'file_viewer')
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <a href="{{ $formField->fileUrl }}"><button type="button" class="btn btn-info"><i class="fa-solid fa-eye"></i></button></a>
                                        </div>
                                        <input type="text" class="form-control" disabled value="{{ $formField->labelValue }}">
                                    </div>
                            @endif
                            @if($field->inputType === 'point_geom_drawer')
                                <div class="input-group mb-3">
                                     <div id="map">
                                     </div>
                                    <input type="hidden" name="latitude" id="latitude" class="form-control">
                                    <input type="hidden" name="longitude" id="longitude" class="form-control">
                                    @push('scripts')
                                        <script>
                                            $(document).ready(function () {
                                                pointGeomDrawer(
                                                    {
                                                        workspace:'{{ Config::get("constants.GEOSERVER_WORKSPACE") }}',
                                                        geoserverUrl:'{{ Config::get("constants.GEOSERVER_URL") }}',
                                                        authKey:'{{ Config::get("constants.AUTH_KEY") }}',
                                                        mapID:'map'
                                                    }
                                                )
                                            });
                                        </script>
                                    @endpush
                                </div>
                                @endif

                            @if($field->inputType === 'geom_viewer')
                                    <div class="input-group mb-3">
                                        <div id="map" style="width: 100%;height: 500px">
                                        </div>
                                        @push('scripts')
                                            <script>
                                                $(document).ready(function () {
                                                    geomViewer(
                                                        {
                                                            workspace:'{{ Config::get("constants.GEOSERVER_WORKSPACE") }}',
                                                            geoserverUrl:'{{ Config::get("constants.GEOSERVER_URL") }}',
                                                            authKey:'{{ Config::get("constants.AUTH_KEY") }}',
                                                            mapID:'map',
                                                            geom: '{{ $formField->inputValue }}'
                                                        }
                                                    )
                                                });
                                            </script>
                                        @endpush
                                    </div>
                                @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
    @endforeach
    </div>
@else
    @foreach($formFields as $formField)
    <div class="col-sm-12 col-md-8 col-lg-8">
        <div class="form-group row @if($formField->required) required @endif {{ ($formField->hidden === true) ? 'field-hidden' : '' }}" id="form-group-{{ $formField->inputId }}">
            {!! Form::label($formField->labelFor,$formField->label,['class' => $formField->labelClass,'disabled' => $formField->disabled]) !!}
            <div class="col-sm-4">
                @if($formField->inputType === 'text')
                    {!! Form::text($formField->inputId,$formField->inputValue,['class' => $formField->inputClass, 'placeholder' => $formField->placeholder,'disabled' => $formField->disabled,'autocomplete'=>$formField->autoComplete, 'oninput'=>$formField->oninput]) !!}
                @endif
                @if($formField->inputType === 'textarea')
                    {!! Form::textarea($formField->inputId, $formField->inputValue, ['class' => 'form-control', 'placeholder' => $formField->placeholder, 'rows' => 4, 'cols' => 54, 'style' => 'resize:none']) !!}
                @endif
                @if($formField->inputType === 'number')
                    {!! Form::number($formField->inputId,$formField->inputValue,['class' => $formField->inputClass, 'placeholder' => $formField->placeholder,'disabled' => $formField->disabled, 'oninput'=>$formField->oninput]) !!}
                @endif
                @if($formField->inputType === 'select')
                    {!! Form::select($formField->inputId,$formField->selectValues,$formField->selectedValue,['class' => $formField->inputClass, 'placeholder' => $formField->placeholder,'disabled' => $formField->disabled]) !!}
                    @if($formField->disabled)
                    {!! Form::hidden($formField->inputId,$formField->selectedValue) !!}
                    @endif
                    @endif
                @if($formField->inputType === 'label')
                    {!! Form::label($formField->inputId,$formField->labelValue,['class' => $formField->inputClass,'disabled' => $formField->disabled]) !!}
                @endif
                @if($formField->inputType === 'radio')
                    <div class="radio-options-container">
                        @foreach($formField->radioValues as $radioValue => $radioLabel)
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="{{ $formField->inputId }}" id="{{ $formField->inputId }}_{{ $loop->index }}" value="{{ $radioValue }}" {{ ($formField->selectedValue == $radioValue) ? 'checked' : '' }} {{ $formField->disabled ? 'disabled' : '' }}>
                            <label class="form-check-label" for="{{ $formField->inputId }}_{{ $loop->index }}">
                                {{ $radioLabel }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                @endif
                @if($formField->inputType === 'checkbox')
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" name="{{ $formField->inputId }}" id="{{ $formField->inputId }}" value="{{ $formField->checkboxValue }}" {{ ($formField->selectedValue == $formField->checkboxValue) ? 'checked' : '' }} {{ $formField->disabled ? 'disabled' : '' }} onchange="togglePaymentAmountFields()">
                        <label class="form-check-label font-weight-500" for="{{ $formField->inputId }}">
                            {{ $formField->label }}
                        </label>
                    </div>
                @endif
                @if($formField->inputType === 'multiple-select')
                    {!! Form::select($formField->inputId,$formField->selectValues,$formField->selectedValue,['class' => $formField->inputClass,'disabled' => $formField->disabled]) !!}
                    @push('scripts')
                        <script>
                            $(document).ready(function() {
                                $('#{{ $formField->inputId }}').prepend('<option selected=""></option>').append('<option value="-1">Address Not Found</option>').select2({
                                    placeholder: '{{ $formField->placeholder }}',
                                    matcher: function(params, data) {
                                        if (data.id === "-1") {
                                            return data;
                                        } else {
                                            return $.fn.select2.defaults.defaults.matcher.apply(this, arguments);
                                        }
                                    },
                                    closeOnSelect: true,
                                    width: 'select'
                                });
                            });
                        </script>
                    @endpush
                @endif
                @if($formField->inputType === 'date_time')
                    {!! Form::datetimeLocal($formField->inputId,$formField->inputValue,['class' => $formField->inputClass]) !!}
                @endif
                @if($formField->inputType === 'date')
                {!! Form::date($formField->inputId, $formField->inputValue, ['onclick' => 'this.showPicker()', 'class' => $formField->inputClass]) !!}
                @endif
                    @if($formField->inputType === 'year')
                        <input type="text" id="{{ $formField->inputId }}" name="{{ $formField->inputId }}" value="{{ $formField->inputValue }}" class="{{ $formField->inputClass }}" autocomplete="off" placeholder="{{ $formField->placeholder }}">
                        @push('scripts')
                            <script>
                                $(document).ready(function () {
                                    $('#{{$formField->inputId}}').datepicker({
                                        format: "yyyy",
                                        minViewMode: "years",
                                        autoclose: true,
                                        startDate:'today',
                                        container:$('#{{ $formField->inputId }}').parent()
                                    });
                                })
                            </script>
                        @endpush
                    @endif
                @if($formField->inputType === 'time')
                    {!! Form::time($formField->inputId,$formField->inputValue,['class' => $formField->inputClass]) !!}
                @endif
                @if($formField->inputType === 'hidden')
                    {!! Form::hidden($formField->inputId,$formField->inputValue,['class' => $formField->inputClass]) !!}
                    {!! Form::text($formField->inputId."_disabled",$formField->inputValue,['class' => $formField->inputClass,'disabled'=>'disabled','id'=>$formField->inputId."_disabled"]) !!}
                @endif
                @if($formField->inputType === 'image')
                    {!! Form::file($formField->inputId,['class' => $formField->inputClass]) !!}
                @endif
                @if($formField->inputType === 'file_viewer')
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                        <a href="{{ $formField->fileUrl }}" target="_blank"><button type="button" class="btn btn-info" {{ !$formField->fileUrl ? 'disabled' : '' }}><i class="fa-solid fa-eye"></i></button></a>
                        </div>
                        <input type="text" class="form-control" disabled value="{{ $formField->labelValue }}">
                    </div>
                @endif
                @if($formField->inputType === 'file_upload')

                        @if( $formField->inputId === 'house_image')
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="{{ $formField->inputId }}" id="{{ $formField->inputId }}">
                            <label class="custom-file-label" for="{{ $formField->labelFor }}">{{ $formField->inputValue??"Choose file" }}</label>
                        </div>
                        <small class="form-text" id="fileSizeHintImg">(Image (JPG,JPEG) size should not be more than 5MB)</small>
                        @elseif ( $formField->inputId === 'receipt_image')
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="{{ $formField->inputId }}" id="{{ $formField->inputId }}">
                            <label class="custom-file-label" for="{{ $formField->labelFor }}">{{ $formField->inputValue??"Choose file" }}</label>
                        </div>
                        <small class="form-text" id="fileSizeRintImg">(Image (JPG,JPEG) size should not be more than 5MB)</small>
                        @else
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="{{ $formField->inputId }}" id="{{ $formField->inputId }}">
                            <label class="custom-file-label" for="{{ $formField->labelFor }}">{{ $formField->inputValue??"Choose file" }}</label>
                        </div>
                        @endif

                @endif
                @if($formField->inputType === 'point_geom_drawer')
                        <link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css">
                        <link rel="stylesheet" href="https://unpkg.com/ol-layerswitcher@3.8.3/dist/ol-layerswitcher.css"/>
                        <div class="input-group mb-3">
                            <div id="map" style="width: 100%;height: 500px">
                            </div>
                            <input type="hidden" name="latitude" id="latitude" class="form-control">
                            <input type="hidden" name="longitude" id="longitude" class="form-control">
                            @push('scripts')
                                <script src="{{ asset('/js/ol.js') }}" type="text/javascript"></script>
                                <script src="https://unpkg.com/ol-layerswitcher@3.8.3"></script>
                                <script src="{{ asset('js/map-functions.js') }}"></script>
                                <script>
                                    $(document).ready(function () {
                                        pointGeomDrawer(
                                            {
                                                workspace:'{{ Config::get("constants.GEOSERVER_WORKSPACE") }}',
                                                geoserverUrl:'{{ Config::get("constants.GEOSERVER_URL") }}',
                                                authKey:'{{ Config::get("constants.AUTH_KEY") }}',
                                                mapID:'map',
                                                geom: '{{ $formField->inputValue }}'
                                            }
                                        )
                                    });
                                </script>
                            @endpush
                        </div>
                    @endif
                @if($formField->inputType === 'poly_geom_drawer')
                        <link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css">
                        <link rel="stylesheet" href="https://unpkg.com/ol-layerswitcher@3.8.3/dist/ol-layerswitcher.css"/>
                        <div class="input-group mb-3">
                            <div id="map" style="width: 100%;height: 500px"></div>
                            <input type="hidden" name="{{ $formField->inputId }}" id="{{ $formField->inputId }}" value="{{ $formField->inputValue }}" >
                            @push('scripts')
                                <script src="{{ asset('/js/ol.js') }}" type="text/javascript"></script>
                                <script src="https://unpkg.com/ol-layerswitcher@3.8.3"></script>
                                <script src="{{ asset('js/map-functions.js') }}"></script>
                                <script>
                                    $(document).ready(function () {
                                        polyGeomDrawer(
                                            {
                                                workspace:'{{ Config::get("constants.GEOSERVER_WORKSPACE") }}',
                                                geoserverUrl:'{{ Config::get("constants.GEOSERVER_URL") }}',
                                                authKey:'{{ Config::get("constants.AUTH_KEY") }}',
                                                mapID:'map',
                                                geom: '{{ $formField->inputValue }}'
                                            }
                                        )
                                    });
                                </script>
                            @endpush
                        </div>
                    @endif
                @if($formField->inputType === 'geom_viewer')
                        <link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css">
                        <link rel="stylesheet" href="https://unpkg.com/ol-layerswitcher@3.8.3/dist/ol-layerswitcher.css"/>
                        <div class="input-group mb-3">
                            <div id="map" style="width: 100%;height: 500px">
                            </div>
                            @push('scripts')
                                <script src="{{ asset('/js/ol.js') }}" type="text/javascript"></script>
                                <script src="https://unpkg.com/ol-layerswitcher@3.8.3"></script>
                                <script src="{{ asset('js/map-functions.js') }}"></script>
                                <script>
                                    $(document).ready(function () {
                                        geomViewer(
                                            {
                                                workspace:'{{ Config::get("constants.GEOSERVER_WORKSPACE") }}',
                                                geoserverUrl:'{{ Config::get("constants.GEOSERVER_URL") }}',
                                                authKey:'{{ Config::get("constants.AUTH_KEY") }}',
                                                mapID:'map',
                                                geom: '{{ $formField->labelValue }}'
                                            }
                                        )
                                    });
                                </script>
                            @endpush
                        </div>
                    @endif

            </div>
        </div></div>
    @endforeach
@endif
@if(Request::is('*/create') || Request::is('*/edit') || Request::is('*/create/*') || !empty($submitButtonText))
    <div class="card-footer">
        @if(Request::is('*/create') || Request::is('*/edit') || Request::is('*/create/*'))
         <a href="{{ $indexAction }}" class="btn btn-info">{{ __('Back to List') }}</a>
        @endif
        @if(!empty($submitButtonText))
        {!! Form::submit($submitButtonText, ['class' => 'btn btn-info']) !!}
        @endif
    </div>
@endif

@push('scripts')
<script>
// Last Modified: July 19, 2026
// Developed By: Streams Tech Ltd.
// Description: Tax Code lookup functionality for auto-filling ward and holding owner name

(function() {
    // Configuration
    const TAX_CODE_PATTERN = /^\d{2}-\d{3}-\d{4}-\d{2}$/;
    const MIN_TAX_ID_LENGTH = 8;
    let taxIdLookupDebounceTimer = null;
    let activeTaxLookupRequestId = 0;
    let hasManualWardOverride = false;
    let hasManualHoldingOwnerOverride = false;

    /**
     * Validates tax code format
     * Format: ##-###-####-##
     */
    function isValidTaxId(taxId) {
        return TAX_CODE_PATTERN.test(taxId.trim());
    }

    /**
     * Checks if tax code has minimum length
     */
    function hasMinimumLength(taxId) {
        const digitsOnly = taxId.replace(/[^0-9]/g, '');
        return digitsOnly.length >= MIN_TAX_ID_LENGTH;
    }

    /**
     * Formats tax code as user types
     * Automatically adds dashes after 2, 5, and 9 characters
     */
    function formatTaxId(value) {
        // Remove all non-digits
        let digitsOnly = value.replace(/[^0-9]/g, '');

        // Apply formatting: ##-###-####-##
        if (digitsOnly.length > 0) {
            if (digitsOnly.length <= 2) {
                return digitsOnly;
            } else if (digitsOnly.length <= 5) {
                return digitsOnly.slice(0, 2) + '-' + digitsOnly.slice(2);
            } else if (digitsOnly.length <= 9) {
                return digitsOnly.slice(0, 2) + '-' + digitsOnly.slice(2, 5) + '-' + digitsOnly.slice(5);
            } else {
                return digitsOnly.slice(0, 2) + '-' + digitsOnly.slice(2, 5) + '-' + digitsOnly.slice(5, 9) + '-' + digitsOnly.slice(9, 11);
            }
        }
        return '';
    }

    /**
     * Extracts ward from tax code (first 2 digits)
     */
    function extractWardFromTaxId(taxIdValue) {
        if (!taxIdValue || !isValidTaxId(taxIdValue)) {
            return '';
        }
        return taxIdValue.substring(0, 2);
    }

    /**
     * Clears auto-populated fields
     */
    function clearAutoPopulatedFields() {
        const holdingOwnerInput = document.getElementById('holding_owner_name');
        if (holdingOwnerInput && !hasManualHoldingOwnerOverride) {
            holdingOwnerInput.value = '';
        }
        hasManualHoldingOwnerOverride = false;
    }

    /**
     * Fetches building data from server using tax code
     */
    async function fetchBuildingDataByTaxId(taxIdValue, requestId) {
        try {
            const endpoint = '{{ route("client-fsm-application.get-building-data") }}';
            const response = await fetch(`${endpoint}?tax_id=${encodeURIComponent(taxIdValue)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            // Check if this is still the active request
            if (requestId !== activeTaxLookupRequestId) {
                return;
            }

            if (response.ok && data.success && data.data) {
                const { owner_name, ward } = data.data;

                // Auto-populate holding owner name
                const holdingOwnerInput = document.getElementById('holding_owner_name');
                if (holdingOwnerInput && !hasManualHoldingOwnerOverride) {
                    holdingOwnerInput.value = owner_name || '';
                }

                // Auto-populate ward if available
                if (ward) {
                    const wardSelect = document.getElementById('ward');
                    if (wardSelect && !hasManualWardOverride) {
                        wardSelect.value = ward;
                    }
                }
            } else {
                clearAutoPopulatedFields();
            }
        } catch (error) {
            if (requestId !== activeTaxLookupRequestId) {
                return;
            }
            clearAutoPopulatedFields();
            console.error('Failed to fetch building data:', error);
        }
    }

    /**
     * Handles tax code input with debounce
     */
    function handleTaxCodeInput(event) {
        const taxIdInput = event.target;
        let value = taxIdInput.value;

        // Format the input
        const formattedValue = formatTaxId(value);
        if (value !== formattedValue) {
            taxIdInput.value = formattedValue;
            value = formattedValue;
        }

        // Clear previous debounce timer
        if (taxIdLookupDebounceTimer) {
            clearTimeout(taxIdLookupDebounceTimer);
        }

        // Reset overrides on user input
        hasManualWardOverride = false;
        hasManualHoldingOwnerOverride = false;

        // Return early if tax code is empty or too short
        if (!value || !hasMinimumLength(value)) {
            clearAutoPopulatedFields();
            return;
        }

        // Debounce the lookup by 800ms
        taxIdLookupDebounceTimer = setTimeout(() => {
            if (isValidTaxId(value)) {
                activeTaxLookupRequestId++;
                const requestId = activeTaxLookupRequestId;
                fetchBuildingDataByTaxId(value, requestId);
            }
        }, 800);
    }

    /**
     * Handles ward field change to detect manual override
     */
    function handleWardChange(event) {
        if (event.target.value) {
            hasManualWardOverride = true;
        }
    }

    /**
     * Handles holding owner name field change to detect manual override
     */
    function handleHoldingOwnerChange(event) {
        if (event.target.value) {
            hasManualHoldingOwnerOverride = true;
        }
    }

    /**
     * Initialize event listeners when DOM is ready
     */
    function initializeTaxCodeLookup() {
        const taxIdInput = document.getElementById('tax_id');
        const wardSelect = document.getElementById('ward');
        const holdingOwnerInput = document.getElementById('holding_owner_name');

        if (taxIdInput) {
            taxIdInput.addEventListener('input', handleTaxCodeInput);
        }

        if (wardSelect) {
            wardSelect.addEventListener('change', handleWardChange);
        }

        if (holdingOwnerInput) {
            holdingOwnerInput.addEventListener('input', handleHoldingOwnerChange);
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeTaxCodeLookup);
    } else {
        initializeTaxCodeLookup();
    }
})();
</script>
@endpush


