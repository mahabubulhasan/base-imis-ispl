@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')

    @include('layouts.components.error-list')
    <div class="card card-info">
        {!! Form::model($surveyForm, [
            'url' => 'building-info/buildings',
            'id' => 'prevent-multiple-submits',
            'files' => true,
        ]) !!}
        @include('building-info.buildings.partial-form', ['submitButtomText' => __('Save')])
        {!! Form::close() !!}
    </div><!-- /.card -->
@stop

@push('scripts')
    <script>
        $(document).ready(function() {
            dynamicBuildingForm();
            handleMainBuildingChange();
            handleLowIncomeChange();
            handleLicStatusChange();
            maindrinkingWaterSource();
            wellpresenceStatus();
            handleToiletPresenceChange();
            handleToiletConnectionChange();
            defecationStatus();
            hideoffice();
            munPublicDrinkingWater();
            handleContainmentTypeChange();
            showContainmentDimensionsOnReload();

            var associatedBin = @json($surveyForm->building_associated_to ?? null);
            optionHtmlBIN = associatedBin
                ? `<option value="${associatedBin}" selected="${associatedBin}">${associatedBin}</option>`
                : `<option selected=""></option>`;
            $('#building_associated_to').prepend(optionHtmlBIN).select2({
                ajax: {
                    url: "{{ route('building.get-house-numbers-all') }}",
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                },
                placeholder: '{{ __('BIN of Main Building') }}',
                allowClear: true,
                closeOnSelect: true,
                width: '85%',
            });

            var roadCodeValue = @json($surveyForm->road_code ?? null);
            optionHtmlRoadCode = roadCodeValue
                ? `<option value="${roadCodeValue}" selected="${roadCodeValue}">${roadCodeValue}</option>`
                : `<option selected=""></option>`;
            $('#road_code').prepend(optionHtmlRoadCode).select2({
                ajax: {
                    url: "{{ route('roadlines.get-road-names') }}",
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                },
                placeholder: '{{ __('Road Code - Road Name') }}',
                allowClear: true,
                closeOnSelect: true,
                width: '85%',
            });

            // LIC Name: options load on demand via AJAX (select2)
            $('#lic_id_select').select2({
                ajax: {
                    url: "{{ route('building.lic-options') }}",
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                    cache: true,
                },
                placeholder: '{{ __('LIC Name') }}',
                allowClear: true,
                closeOnSelect: true,
                width: '85%',
                minimumInputLength: 0,
            });

            var waterCodeValue = @json($surveyForm->watersupply_pipe_code ?? null);
            optionHtmlWaterCode = waterCodeValue
                ? `<option selected="${waterCodeValue}">${waterCodeValue}</option>`
                : `<option selected=""></option>`;
            $('#watersupply_pipe_code').prepend(optionHtmlWaterCode).select2({
                ajax: {
                    url: "{{ route('watersupply.get-watersupply-code') }}",
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                },
                placeholder: '{{ __('Water Supply Pipe Line Code') }}',
                allowClear: true,
                closeOnSelect: true,
                width: '85%',
            });

            var sewerCodeValue = @json($surveyForm->sewer_code ?? null);
            optionHtmlSewerCode = sewerCodeValue
                ? `<option selected="${sewerCodeValue}">${sewerCodeValue}</option>`
                : `<option selected=""></option>`;
            $('#sewer_code').prepend(optionHtmlSewerCode).select2({
                ajax: {
                    url: "{{ route('sewerlines.get-sewer-names') }}",
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                },
                placeholder: '{{ __('Sewer Code') }}',
                allowClear: true,
                closeOnSelect: true,
                width: '85%',
            });

            var drainCodeValue = @json($surveyForm->drain_code ?? null);
            optionHtmlDrainCode = drainCodeValue
                ? `<option selected="${drainCodeValue}">${drainCodeValue}</option>`
                : `<option selected=""></option>`;
            $('#drain_code').prepend(optionHtmlDrainCode).select2({
                ajax: {
                    url: "{{ route('drains.get-drain-names') }}",
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                },
                placeholder: '{{ __('Drain Code') }}',
                allowClear: true,
                closeOnSelect: true,
                width: '85%',
            });

            var buildContainValue = @json($surveyForm->build_contain ?? null);
            optionHtmlBIN = buildContainValue
                ? `<option value="${buildContainValue}" selected="${buildContainValue}">${buildContainValue}</option>`
                : `<option selected=""></option>`;
            $('#build_contain').prepend(optionHtmlBIN).select2({
                ajax: {
                    url: "{{ route('building.get-house-numbers-containments') }}",
                    data: function(params) {
                        return {
                            search: params.term,
                            page: params.page || 1
                        };
                    },
                },
                placeholder: '{{ __('BIN of Pre-Connected Building') }}',
                allowClear: true,
                closeOnSelect: true,
                width: '85%',
            });

            handleMainBuildingChange();
            handleToiletPresenceChange();
            handleToiletConnectionChange();
            defecationStatus();
            maindrinkingWaterSource();
            wellpresenceStatus();
            handleLicStatusChange();
            hideoffice();
        });

        var usecatgs = JSON.parse('{!! $usecatgsJson !!}');
        var html = '<option value="">' + _('Use Category of Building') + '</option>';
        var functional_use = $('#functional_use_id').val();
        var selectedUseCategory = @json($surveyForm->use_category_id ?? null);
        if (functional_use) {
            $.each(usecatgs[functional_use], function(key, value) {
                if (key == selectedUseCategory) {
                    html += '<option value="' + key + '" selected="selected">' + value + '</option>';
                } else {
                    html += '<option value="' + key + '">' + value + '</option>';
                }
            });
            if (functional_use == 1) {
                $('#office-business').hide();
            } else {
                $('#office-business').show();
            }
        }
        $('#use_category_id').html(html);

        $('#toilet-connection select').on('change', function() {
            var selectedText = $(this).find('option:selected').text();
            var sanitationId;
            if (selectedText === "Septic Tank") {
                sanitationId = 3;
            } else if (selectedText === "Pit/ Holding Tank") {
                sanitationId = 4;
            } else {
                $('#containment-type select').empty();
                return;
            }
            $.ajax({
                url: "{{ route('building.get-containment-septic') }}",
                method: "GET",
                data: {
                    sanitation_system_id: sanitationId
                },
                success: function(response) {
                    var containmentSelect = $('#containment-type select');
                    containmentSelect.empty();
                    containmentSelect.append('<option selected value=" ">{{ __('Containment Type') }}</option>');
                    $.each(response, function(index, option) {
                        containmentSelect.append($('<option>').text(option.type).attr('value', option.id));
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            var diameterField = document.querySelector('input[name="pit_diameter"]');
            var depthField = document.querySelector('input[name="pit_depth"]');
            var volumeField = document.querySelector('input[name="size"]');
            if (!diameterField || !depthField || !volumeField) {
                return;
            }
            var calculateVolume = function() {
                var diameter = parseFloat(diameterField.value) || 0;
                var depth = parseFloat(depthField.value) || 0;
                var radius = diameter / 2;
                var volume = Math.PI * Math.pow(radius, 2) * depth;
                volumeField.value = volume.toFixed(2);
                volumeField.readOnly = diameterField.value >= 1 || depthField.value >= 1;
            };
            diameterField.addEventListener('input', calculateVolume);
            depthField.addEventListener('input', calculateVolume);
        });

        document.addEventListener('DOMContentLoaded', function() {
            var lengthField = document.querySelector('input[name="tank_length"]');
            var widthField = document.querySelector('input[name="tank_width"]');
            var depthField = document.querySelector('input[name="depth"]');
            var volumeField = document.querySelector('input[name="size"]');
            if (!lengthField || !widthField || !depthField || !volumeField) {
                return;
            }
            var calculateVolume = function() {
                var length = parseFloat(lengthField.value) || 0;
                var width = parseFloat(widthField.value) || 0;
                var depth = parseFloat(depthField.value) || 0;
                volumeField.value = (length * width * depth).toFixed(2);
                volumeField.readOnly = lengthField.value >= 1 || widthField.value >= 1 || depthField.value >= 1;
            };
            lengthField.addEventListener('input', calculateVolume);
            widthField.addEventListener('input', calculateVolume);
            depthField.addEventListener('input', calculateVolume);
        });

        $('#house_image').on('change', function() {
            validateFileSize(document.querySelector('#house_image'), 'fileSizeHintImg', '5');
        });

        var selectedText = $("#toilet-connection :selected").text();
        var sanitationId;
        if (selectedText === "Septic Tank") {
            sanitationId = 3;
        } else if (selectedText === "Pit/ Holding Tank") {
            sanitationId = 4;
        }
        if (sanitationId) {
            $.ajax({
                url: "{{ route('building.get-containment-septic') }}",
                method: "GET",
                data: {
                    sanitation_system_id: sanitationId
                },
                success: function(response) {
                    var containmentSelect = $('#containment-type select');
                    containmentSelect.empty();
                    containmentSelect.append('<option selected="">Containment Type</option>');
                    $.each(response, function(index, option) {
                        containmentSelect.append($('<option>').text(option.type).attr('value', option.id));
                    });
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        }
    </script>
@endpush
