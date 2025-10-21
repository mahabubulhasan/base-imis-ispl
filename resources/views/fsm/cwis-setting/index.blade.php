@extends('layouts.dashboard')
@push('style')
    <style type="text/css">
        .dataTables_filter {
            display: none;
        }
    </style>
@endpush
@section('title', $page_title)
@section('content')
    @include('layouts.components.error-list')
    @include('layouts.components.success-alert')
    @include('layouts.components.error-alert')
    <div class="card">

        <div class="card-body">
            {!! Form::model([
                'method' => 'PATCH',
                'action' => ['Fsm\CwisSettingController@update'],
                'class' => 'form-horizontal',
                'id' => 'editForm',
            ]) !!}
            <div class="form-group row">
                <div class="col-sm-3" style="color:grey">
                    <small><i class="fa-regular fa-clock"></i> {{__('Last Updated')}}: {{ $updated ?? '' }}</small>
                </div>
            </div>
            
            @php
                $units = [
                    'average_water_consumption_lpcd' => 'liters/day',
                    'waste_water_conversion_factor' => 'liters/day',
                    'greywater_conversion_factor_connected_to_sewer' => '%',
                    'greywater_conversion_factor_not_connected_to_sewer' => '%',
                    'fs_generation_from_containment_not_connected_to_sewer_lpcd' => 'liters/day',
                    'fs_generation_from_permeable_or_unlined_pit_lpcd' => 'liters/day',
                                     
                ];

                $abbreviations = [
                    'average_water_consumption_lpcd' => __('Average Water Consumption (LPCD)'),
                    'waste_water_conversion_factor' => __('Waste Water Conversion Factor (%)'),
                    'greywater_conversion_factor_connected_to_sewer' => __('Greywater Conversion Factor Connected To Sewer (%)'),
                    'greywater_conversion_factor_not_connected_to_sewer' => __('Greywater Conversion Factor Not Connected To Sewer (%)'),
                    'fs_generation_from_containment_not_connected_to_sewer_lpcd' => __('FS Generation From Containment Not Connected To Sewer (LPCD)'),
                    'fs_generation_from_permeable_or_unlined_pit_lpcd' => __('FS Generation From Permeable/ Unlined Pit (LPCD)')
                ];
            @endphp

            @foreach (['average_water_consumption_lpcd', 'waste_water_conversion_factor', 'greywater_conversion_factor_connected_to_sewer','greywater_conversion_factor_not_connected_to_sewer','fs_generation_from_containment_not_connected_to_sewer_lpcd','fs_generation_from_permeable_or_unlined_pit_lpcd'] as $key)
                <div class="form-group row">
                    {!! Form::label(
                        $key,
                        isset($abbreviations[$key])
                            ? $abbreviations[$key]
                            : ucwords(str_replace('_', ' ', $key)) . ' (' . $units[$key] . ')',
                        ['class' => 'col-sm-3 control-label' ],
                    ) !!}
                    <div class="col-sm-3">
                        {!! Form::number($key, $data[$key], [
                            'class' => 'form-control',
                            'placeholder' => str_replace('Lpcd', '(LPCD)', ucwords(str_replace('_', ' ', $key))) ,
                            'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*)\\../g, '$1')"
                        ]) !!}
                    </div>
                </div>
            @endforeach
        </div><!-- /.box-body -->
        <div class="card-footer">
             @can('Edit CWIS')
            <span id="editButton" class="btn btn-info">{{__('Edit')}}</span>
            <button type="submit" id="saveButton" class="btn btn-info" style="display: none;">{{__('Save')}}</button>
            @endcan
        </div><!-- /.box-footer -->
    </div>
    {!! Form::close() !!}
    </div>


    </div><!-- /.box -->
@stop
@push('scripts')
<script>
    $(document).ready(function() {
        // Function to toggle readonly attribute
        function toggleReadOnly(readonly) {
            $('input').prop('readonly', readonly);
        }

        // Initially set form fields as read-only
        toggleReadOnly(true);

        // Edit button click event
        $('#editButton').click(function() {
            $('input').removeAttr('readonly');
            $('#editButton').hide();
            $('#saveButton').show();

            // Hide "Last Updated" element
            $('.col-sm-3 small').hide();
        });

        // Check for errors and update buttons accordingly
        var hasErrors = $('.alert-danger').length > 0;

        if (hasErrors) {
            $('input').removeAttr('readonly');
            $('#editButton').hide();
            $('#saveButton').show();
        } else {
            $('#saveButton').hide();
            $('#editButton').show();
        }
    });
</script>

@endpush
