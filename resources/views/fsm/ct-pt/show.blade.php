<!-- Last Modified Date: 19-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
<div class="card-header bg-transparent ">

    <a href="{{ action('Fsm\CtptController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
</div>
    <div class="form-horizontal">
    <div class="card-body">
        <div class="form-group row ">
        {!! Form::label('type', __('Toilet Type'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->type, null,['class' => ' form-control']) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('name', __('Toilet Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->name, null,['class' => ' form-control']) !!}
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('ward', __('Ward Number'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->ward, null,['class' => ' form-control']) !!}
        </div>
    </div>

    <div class="form-group row ">
            {!! Form::label('location_name', __('Location'), ['class' => 'col-sm-3 control-label ']) !!}
            <div class="col-sm-6">
            {!! Form::label($ctpt->location_name, null,['class' => ' form-control']) !!}
            </div>
        </div>
        <div class="form-group row " id="bin" >
            {!! Form::label('bin', __('BIN'), ['class' => 'col-sm-3 control-label ']) !!}
            <div class="col-sm-6">
            {!! Form::label($ctpt->bin, null,['class' => ' form-control']) !!}
            </div>
        </div>
        <div class="form-group row "  >
            {!! Form::label('address', __('House Number'), ['class' => 'col-sm-3 control-label ']) !!}
            <div class="col-sm-6">
            {!! Form::label($address->house_address, null,['class' => ' form-control']) !!}
            </div>
        </div>

        <div class="form-group row "  >
            {!! Form::label('building_sanitation_system', __('Toilet Connection of Building'), ['class' => 'col-sm-3 control-label ']) !!}
            <div class="col-sm-6">
            {!! Form::label('building_sanitation_system', $building_data[1],['class' => ' form-control']) !!}
            </div>
            <div class=col-sm-1>
            <span class="top-left-icon" data-tooltip="building-sanitation-info">
                <i class="fa-solid fa-circle-info"></i>
                <!-- Display institution names with line breaks -->
                <div class="custom-tooltip" id="building-sanitation-info">

                </div>
            </span>
            </div>
        </div>
        
        <div class="form-group row " id="access_frm_nearest_road">
        {!! Form::label('access_frm_nearest_road', __('Distance from Nearest Road (m)'), [
            'class' => 'col-sm-3 control-label',
        ]) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->access_frm_nearest_road, null,['class' => ' form-control']) !!}

        </div>
    </div>
        <div class="form-group row  " id="status">
            {!! Form::label('status',__('Status'),['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-6">
            {!! Form::label(null,$operational,['class' => ' form-control']) !!}
            </div>
        </div>

        <div class="form-group row ">
        {!! Form::label('caretaker_name', __('Caretaker Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->caretaker_name, null,['class' => ' form-control']) !!}
        </div>
    </div>
    <div class="form-group row " id="caretaker_gender">
        {!! Form::label('caretaker_gender',__('Caretaker Gender'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->caretaker_gender, null,['class' => ' form-control']) !!}
     </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('caretaker_contact_number', __('Caretaker Contact'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->caretaker_contact_number, null,['class' => ' form-control']) !!}
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('owner', __('Owning Institution'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->owner, null,['class' => ' form-control']) !!}
        </div>
    </div>
<div class="form-group row ">
        {!! Form::label('owning_institution_name', __('Name of Owning Institution'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->owning_institution_name, null,['class' => ' form-control']) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('operator_or_maintainer', __('Operate and Maintained by'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->operator_or_maintainer, null,['class' => ' form-control']) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('operator_or_maintainer_name', __('Name of Operate and Maintained by'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->operator_or_maintainer_name, null,['class' => ' form-control']) !!}
        </div>
    </div>
   
    <div class="form-group row " id="total_no_of_toilets">
        {!! Form::label('total_no_of_toilets', __('Total Number of Seats'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->total_no_of_toilets, null,['class' => ' form-control']) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('total_no_of_urinals', __('Total Number of Urinals'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->total_no_of_urinals, null,['class' => ' form-control']) !!}
        </div>
    </div>
    <div class="form-group row " id="male_or_female_facility">
        {!! Form::label('male_or_female_facility', __('Separate Facility for Male and Female'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($male_or_female_facility, null,['class' => ' form-control']) !!}
            
        </div>
    </div>

    <div class="form-group row " id="male_seats">
        {!! Form::label('male_seats', __('No. of Seats for Male Users'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->male_seats, null,['class' => ' form-control']) !!}

        </div>
    </div>

    <div class="form-group row " id="female_seats">
        {!! Form::label('female_seats', __('No. of Seats for Female Users'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->female_seats, null,['class' => ' form-control']) !!}
        </div>
    </div>
    
    <div class="form-group row " id="handicap_facility">
        {!! Form::label('handicap_facility',__('Separate Facility for People with Disability'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($handicap_facility, null,['class' => ' form-control']) !!}
            
        </div>
    </div>

    <div class="form-group row " id="pwd_seats">
        {!! Form::label('pwd_seats', __('No. of Seats for People with Disability'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->pwd_seats, null,['class' => ' form-control']) !!}

        </div>
    </div>

    <div class="form-group row " id="children_facility">
        {!! Form::label('children_facility',__('Separate Facility for Children'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($children_facility, null,['class' => ' form-control']) !!}

          </div>
    </div>
   
   
    <div class="form-group row " id="separate_facility_with_universal_design">
        {!! Form::label('separate_facility_with_universal_design', __('Adherence with Universal Design Principles'), [
            'class' => 'col-sm-3 control-label',
        ]) !!}
        <div class="col-sm-6">
           
        {!! Form::label($separate_facility_with_universal_design, null,['class' => ' form-control']) !!}

        </div>
    </div>
    
    <div class="form-group row ">
        {!! Form::label('indicative_sign',__('Presence of Indicative Sign'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($indicative_sign, null,['class' => ' form-control']) !!}

     </div>
    </div>

    <div class="form-group row " id="sanitary_supplies_disposal_facility">
        {!! Form::label('sanitary_supplies_disposal_facility',__('Sanitary Supplies and Disposal Facilities'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($sanitary_supplies_disposal_facility, null,['class' => ' form-control']) !!}

             </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('fee_collected',__('Uses Fee Collection'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($fee_collected, null,['class' => ' form-control']) !!}

     </div>
    </div>

    <div class="form-group row " id="amount_fee_collected" >
        {!! Form::label('amount_of_fee_collected', __('Uses Fee Rate'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->amount_of_fee_collected, null,['class' => ' form-control']) !!}
 </div>
    </div>
    <div class="form-group row " id="frequency_of_fee_collected">
        {!! Form::label('frequency_of_fee_collected', __('Frequency of Fee Collection'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-6">
        {!! Form::label($ctpt->frequency_of_fee_collected, null,['class' => ' form-control']) !!}
 </div>
</div>
</div><!-- /.card -->

@stop   

@push('scripts')

<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip({
                    html: true
                });
            });
            
  // JavaScript
    const selectedValue = document.querySelector('#bin .form-control').innerText; // or textContent
    // $("#bin :selected").text(); // Get selected dropdown value
    const sanitationInput = document.getElementById('building_sanitation_system_name'); // Text field to populate data
    if (selectedValue) {
        // Perform AJAX request using jQuery
        $.ajax({
        url: "{{ route('building.get-sanitation-system') }}", // Laravel route
        type: "GET",
        data: {
            bin: selectedValue, // Send dropdown value as 'toilet_type'
        },
        success: function (response) {
            // Assuming the response contains the relevant data
            const sanitationData = response.data; // Adjust key based on your API structure
            console.log(sanitationData)
            $('#building-sanitation-info').html(sanitationData);
        },
        error: function (xhr, status, error) {
        }
        });
    } else {
        sanitationInput.value = ''; // Clear the text field if no selection
    }

</script> 

@endpush

