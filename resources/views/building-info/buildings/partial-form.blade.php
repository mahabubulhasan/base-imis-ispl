<div class="card-body" style="font-family: 'Open Sans', sans-serif;">

    <!-- preview building footprint if building is being approved via Building Survey- Approve -->
    @if (!empty($buildingSurvey))
        <div class="form-group row">
            {!! Form::label('', __('Preview Building Footprint'), [
                'class' => 'col-sm-3 control-label',
                'style' => 'font-family: "Open Sans", sans-serif;',
            ]) !!}

            <div class="col-sm-5">
                <a title="Preview Building Location" data-toggle="modal" data-target="#kml-previewer"
                    data-id="{{ $buildingSurvey->kml }}" class="btn btn-info btn-xs"><i class="fa fa-eye"></i></a>
            </div>
        </div>
        @include('building-info.building-surveys.kmlPreviewModal')
    @endif

    <h3 class="mt-4"> Owner Information </h3>
    <!-- Building Owner Information -->
    <div class="form-group row required">
        {!! Form::label('owner_name', __('Owner Name'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::text('owner_name', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Owner Name',
                'autocomplete' => 'off',
            ]) !!}
        </div>
    </div>


    <div class="form-group row ">
        {!! Form::label('nid', __('Owner NID'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::text('nid', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => __('Owner NID'),
                'autocomplete' => 'off',
            ]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('owner_gender', __('Owner Gender'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::select('owner_gender', ['Male' => 'Male', 'Female' => 'Female', 'Others' => 'Others'], null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Owner Gender',
                'autocomplete' => 'off',
            ]) !!}
        </div>
    </div>
    <div class="form-group row required">
    {!! Form::label('owner_contact', __('Owner Contact Number'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::text('owner_contact', null, [
            'class' => 'form-control col-sm-10',
            'placeholder' => 'Owner Contact Number',
            'autocomplete' => 'off',
            'oninput' => "validateOwnerContactInput(this)",
        ]) !!}
    </div>
</div>
    <h3 class="mt-3"> Building Information </h3>

    <!-- Main Building Identifier -->
    <div class="form-group row required" id="main_building">
        {!! Form::label('main_building', __('Main Building'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::select('main_building', [true => 'Yes', false => 'No'], null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Main Building',
            ]) !!}
        </div>
    </div>
    <!--  Associated Main building House Number  -->
    <div class="form-group row required" id="building_associated" style="display: none;">
        {!! Form::label('building_associated_to', __('BIN of Main Building'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::select('building_associated_to', $buildingBin, null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'BIN of Main Building',
                'style' => 'width:100%',
            ]) !!}
        </div>
    </div>
    <!-- Building Location Information -->

    <div class="form-group row required">
        {!! Form::label('ward', __('Ward Number'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::select('ward', $ward, null, ['class' => 'form-control col-sm-10', 'placeholder' => 'Ward Number']) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('road_code', __('Road Code'), ['class' => 'col-sm-3 control-label control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::select('road_code', $road_code, null, [
                'class' => 'form-control col-sm-10 road_code',
                'placeholder' => 'Road Code',
            ]) !!}
        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('house_number', __('House Number'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::text('house_number', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'House Number',
                'autocomplete' => 'off',

            ]) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('house_locality', __('House Locality/Address'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::text('house_locality', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'House Locality/Address',
                'autocomplete' => 'off',
            ]) !!}
        </div>
    </div>

    <!-- Tax  ID -->
    <div class="form-group row">
    {!! Form::label('tax_code', __('Tax Code/Holding ID'), ['class' => 'col-sm-3 control-label ']) !!}
    <div class="col-sm-5">
        {{-- Hidden input that will contain the final comma-separated values for submission --}}
        {!! Form::hidden('tax_code', old('tax_code', isset($building) ? $building->tax_code : null), ['id' => 'tax_code_hidden']) !!}

        {{-- Visible tag input UI --}}
        <div id="tax-code-tag-input" class="form-control col-sm-10" style="min-height:42px;padding:6px;display:flex;align-items:center;flex-wrap:wrap;cursor:text;">
            <ul id="tax-code-tags" style="list-style:none;display:flex;flex-wrap:wrap;padding:0;margin:0"></ul>
            <input id="tax_code_input" type="text" placeholder="" autocomplete="off"
                style="border:0;outline:0;flex:1;min-width:150px;padding:5px;" />
        </div>
        <!-- <small id="tax-code-hint" class="form-text text-muted">Format: 00-000-0000-00 </small> -->
        <small id="tax-code-error" class="form-text text-danger" style="display:none;margin-top:4px;"></small>
    </div>
</div>

    <!-- Basic Building Structure Information -->
    <div class="form-group row required">
        {!! Form::label('structure_type_id', __('Structure Type'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::select('structure_type_id', $structure_type, null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Structure Type',
            ]) !!}
        </div>
    </div>
    <div class="form-group row">
    {!! Form::label('surveyed_date', __('Surveyed Date'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-5">
        @if (empty($buildingSurvey))
            {!! Form::date('surveyed_date', null, [
                'class' => 'form-control date col-sm-10',
                'autocomplete' => 'off',
                'id' => 'surveyed_date',
                'max' => now()->format('Y-m-d'),
                'onclick' => 'this.showPicker();', // Trigger date picker when clicked
            ]) !!}
        @else
            {!! Form::date('surveyed_date', $buildingSurvey->collected_date, [
                'class' => 'form-control date col-sm-10',
                'autocomplete' => 'off',
                'id' => 'surveyed_date',
                'max' => now()->format('Y-m-d'),
                'readonly' => 'readonly',
                'onclick' => 'this.showPicker();',
            ]) !!}
        @endif
    </div>
</div>

    <div class="form-group  row required">
        {!! Form::label('construction_year', __('Construction Date'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::date('construction_year', null, [
                'class' => 'form-control date col-sm-10',
                'autocomplete' => 'off',
                'id' => 'construction_year',
                'max' => now()->format('Y-m-d'),
                'onclick' => 'this.showPicker();',
            ]) !!}
        </div>
    </div>

    <div class="form-group row required">
    {!! Form::label('floor_count', __('Number of Floors'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::text('floor_count', null, [
            'class' => 'form-control col-sm-10',
            'placeholder' => 'Number of Floors',
            'autocomplete' => 'off',
            'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); ",

        ]) !!}

    </div>
</div>
    <!--  Building Function Use Classification -->
    <div class="form-group row  required" id="functional-use">
        {!! Form::label('functional_use_id', __('Functional Use of Building'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::select('functional_use_id', $functional_use, null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Functional Use of Building',
            ]) !!}
        </div>
    </div>
    <div class="form-group row required" id="use-category">
        {!! Form::label('use_category_id', __('Use Category of Building'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::select('use_category_id', $use_category_id, null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Use Category of Building',
                'id' => 'use_category_id', // Add an ID for JavaScript targeting
            ]) !!}
        </div>
    </div>
    <div class="form-group row" id="office-business">
        {!! Form::label('office_business_name', __('Office or Business Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::text('office_business_name', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Office or Business Name',
                'autocomplete' => 'off',
            ]) !!}
        </div>
    </div>

    <!-- Building Population Information - Number of Households -->
    <div class="form-group row required" id="family-count">
        {!! Form::label('household_served', __('Number of Households'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::number('household_served', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Number of Households',
                'autocomplete' => 'off',
                'oninput' => "this.value = this.value < 0 ? '' : this.value",
            ]) !!}
        </div>
    </div>



    <!-- Additional Population Fields (Optional) -->
    <div class="form-group row" id="male-population">
        {!! Form::label('male_population', __('Male Population'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::number('male_population', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Male Population',
                'autocomplete' => 'off',
                'oninput' => "this.value = this.value < 0 ? '' : this.value",
            ]) !!}
        </div>
    </div>
    <div class="form-group row" id="female-population">
        {!! Form::label('female_population', __('Female Population'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::number('female_population', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Female Population',
                'autocomplete' => 'off',
                'oninput' => "this.value = this.value < 0 ? '' : this.value",
            ]) !!}
        </div>
    </div>
    <div class="form-group row" id="other-population">
        {!! Form::label('other_population', __('Other Population'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::number('other_population', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Other Population',
                'autocomplete' => 'off',
                'oninput' => "this.value = this.value < 0 ? '' : this.value",
            ]) !!}
        </div>
    </div>
    <div class="form-group row required" id="population-info">
        {!! Form::label('population_served', __('Population of Building'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::number('population_served', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Population of Building',
                'autocomplete' => 'off',
                'oninput' => "this.value = this.value < 0 ? '' : this.value",
            ]) !!}
        </div>
    </div>
    <!-- <div class="form-group row" >
        {!! Form::label('diff_abled_pop', __('Differently Abled Population'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
        {!! Form::number('diff_abled_pop', null, [
            'class' => 'form-control col-sm-10',
            'placeholder' => 'Differently Abled Population',
            'autocomplete' => 'off',
        ]) !!}
        </div>
    </div> -->

    <div class="form-group row" id='male-diff-population'>
        {!! Form::label('diff_abled_male_pop', __('Differently Abled Male Population'), [
            'class' => 'col-sm-3 control-label',
        ]) !!}
        <div class="col-sm-5">
            {!! Form::number('diff_abled_male_pop', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Differently Abled Male Population',
                'autocomplete' => 'off',
                'oninput' => "this.value = this.value < 0 ? '' : this.value",
            ]) !!}
        </div>
    </div>
    <div class="form-group row" id='female-diff-population'>
        {!! Form::label('diff_abled_female_pop', __('Differently Abled Female Population'), [
            'class' => 'col-sm-3 control-label',
        ]) !!}
        <div class="col-sm-5">
            {!! Form::number('diff_abled_female_pop', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Differently Abled Female Population',
                'autocomplete' => 'off',
                'oninput' => "this.value = this.value < 0 ? '' : this.value",
            ]) !!}
        </div>
    </div>
    <div class="form-group row" id='other-diff-population'>
        {!! Form::label('diff_abled_others_pop', __('Differently Abled Other Population'), [
            'class' => 'col-sm-3 control-label',
        ]) !!}
        <div class="col-sm-5">
            {!! Form::number('diff_abled_others_pop', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Differently Abled Other Population',
                'autocomplete' => 'off',
                'oninput' => "this.value = this.value < 0 ? '' : this.value",
            ]) !!}
        </div>
    </div>
  <!-- Building Population Information - Population of Building -->


    <h3 class="mt-3"> LIC Information </h3>



    {{-- lic information --}}
    <div class="form-group row required " id="low_income_hh">
        {!! Form::label('low_income_hh', __('Is Low Income House'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::select('low_income_hh', [true => 'Yes', false => 'No'], null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Is Low Income House',
            ]) !!}
        </div>
    </div>




    <div class="form-group row " id="lic_status">
        {!! Form::label('lic_status', __('Located In LIC'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::select('lic_status', [true => 'Yes', false => 'No'], null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Located In LIC ?',
            ]) !!}
        </div>
    </div>

    <div class="form-group row required" style="display:none" id="lic_id">
        {!! Form::label('lic_id', __('LIC Name'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::select('lic_id', $licNames, null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'LIC Name',
            ]) !!}
        </div>
    </div>

    <h3 class="mt-3"> Water Source Information </h3>

    <!-- Water Source Information & Water Supply Customer ID -->
    <div id="water-id">
        <div class="form-group row required">
            {!! Form::label('water_source_id', __('Main Drinking Water Source'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::select('water_source_id', $water_source, null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => 'Main Drinking Water Source',
                ]) !!}
            </div>
        </div>
    </div>
    <div id="water-customer-id" style="display: none;">
        <div class="form-group row">
            {!! Form::label('water_customer_id', __('Water Supply Customer ID'), ['class' => 'col-sm-3 control-label ']) !!}
            <div class="col-sm-5">
                {!! Form::text('water_customer_id', null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => 'Water Supply Customer ID',
                    'autocomplete' => 'off',
                ]) !!}
            </div>
        </div>
    </div>
    <div class="form-group row required" id = "water-pipe-id" style="display: none;">
        {!! Form::label('watersupply_pipe_code', __('Water Supply Pipe Line Code'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::select('watersupply_pipe_code', $waterSupply, null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Water Supply Pipe Line Code',
            ]) !!}
        </div>
    </div>

    <div class="form-group row" id="well-presence">
        {!! Form::label('well_presence_status', __('Well in Premises'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::select('well_presence_status', [true => 'Yes', false => 'No'], null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Well in Premises',
            ]) !!}
        </div>
    </div>
    <div class="form-group row" id="distance-from-well" style="display: none;">
        {!! Form::label('distance_from_well', __('Distance of Well from Closest Containment (m)'), [
            'class' => 'col-sm-3 control-label',
        ]) !!}
        <div class="col-sm-5">
            {!! Form::number('distance_from_well', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Distance of Well from Closest Containment (m)',
                'autocomplete' => 'off',
                'oninput' => "this.value = this.value < 0 ? '' : this.value",
            ]) !!}
        </div>
    </div>

    <h3 class="mt-3"> Solid Waste Management Information </h3>

    <div class="form-group row">
        {!! Form::label('swm_customer_id', __('SWM Customer ID'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::text('swm_customer_id', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'SWM Customer ID',
                'autocomplete' => 'off',
            ]) !!}
        </div>
    </div>
    <h3 class="mt-3">Sanitation System Information </h3>

    <div class="form-group row required" id="toilet-presence">
        {!! Form::label('toilet_status', __('Presence of Toilet'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::select('toilet_status', [true => 'Yes', false => 'No'], null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Presence of Toilet',
                'id' => 'toilet_status', // Ensure the id matches the JavaScript selector
            ]) !!}
        </div>
    </div>
    <div class="form-group row required" id="defecation-place" style="display: none">
        {!! Form::label('defecation_place', __('Defecation Place'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::select('defecation_place', $defecationPlace, null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Defecation Place',
            ]) !!}
        </div>
    </div>
    {{-- only show when sanitation system technology is communal --}}
    <div id="ctpt-toilet" style="display:none;">
        <div class="form-group row required">
            {!! Form::label('ctpt_name', __('Community Toilet Name'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::select('ctpt_name', $capitalizedctpt, null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => 'Community Toilet Name',
                ]) !!}
            </div>
        </div>
    </div>
    {{-- show these option when toilet presence is yes  --}}
    <div class="form-group row required" id="toilet-info" style="display: none">
        {!! Form::label('toilet_count', __('Number of Toilets'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::number('toilet_count', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Number of Toilets',
                'oninput' => "this.value = this.value < 0 ? '' : this.value",
            ]) !!}
        </div>
    </div>

    <div class="form-group row" id="shared-toilet" style="display:none" style="margin-left:2px">
        {!! Form::label('household_with_private_toilet', __('Households with Private Toilet'), [
            'class' => 'col-sm-3 control-label ',
        ]) !!}
        <div class="col-sm-5">
            {!! Form::number('household_with_private_toilet', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Households with Private Toilet',
                'autocomplete' => 'off',
                'oninput' => "this.value = this.value < 0 ? '' : this.value",
            ]) !!}
        </div>
    </div>

    <div class="form-group row" id="shared-toilet-popn" style="display:none" style="margin-left:2px">
        {!! Form::label('population_with_private_toilet', __('Population with Private Toilet'), [
            'class' => 'col-sm-3 control-label ',
        ]) !!}
        <div class="col-sm-5">
            {!! Form::number('population_with_private_toilet', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Population with Private Toilet',
                'autocomplete' => 'off',
                'oninput' => "this.value = this.value < 0 ? '' : this.value",
            ]) !!}
        </div>
    </div>

    <div class="form-group row required" id="toilet-connection" style="display: none">
        {!! Form::label('sanitation_system_id', __('Toilet Connection'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::select('sanitation_system_id', $toiletConnection, null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Toilet Connection',
            ]) !!}
        </div>
    </div>



    <!-- Hide containment ID if containment data is being edited -->
    <div class="form-group row required" id="containment-id" style="display:none">
        {!! Form::label('build_contain', __('BIN of Pre-Connected Building'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::select('build_contain', $bin, null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'BIN of Pre-Connected Building',
            ]) !!}
        </div>
    </div>

    <div class="form-group row" style="display:none;" id="vacutug-accessible">
        {!! Form::label('desludging_vehicle_accessible', __('Building Accessible to Desludging Vehicle'), [
            'class' => 'col-sm-3 control-label ',
        ]) !!}
        <div class="col-sm-5">
            {!! Form::select('desludging_vehicle_accessible',  [true => 'Yes', false => 'No'], null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Building Accessible to Desludging Vehicle',
            ]) !!}
        </div>
    </div>



    @if (empty($building))
        <!-- Containment information tab -->
        @include('fsm.containments.partial-form')
    @endif
    <!--  show if toilet connection is Sewer Network -->
    <div class="form-group row required" id="sewer-code" style="display:none">
        {!! Form::label('sewer_code', __('Sewer Code'), ['class' => 'col-sm-3 control-label  ']) !!}
        <div class="col-sm-5">
            {!! Form::select('sewer_code', $sewer_code, null, [
                'class' => 'form-control col-sm-10 sewer_code',
                'placeholder' => 'Sewer Code',
            ]) !!}
        </div>
    </div>

    <!--  show if toilet connection is Drain Network -->
    <div class="form-group row required" style="display:none" id="drain-code">
        {!! Form::label('drain_code', __('Drain Code'), ['class' => 'col-sm-3 control-label  ']) !!}
        <div class="col-sm-5">
            {!! Form::select('drain_code', $drain_code, null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => 'Drain Code',
            ]) !!}
        </div>
    </div>


    @if (empty($building))
        <div class="form-group row required">
        @else
            <div class="form-group row ">
    @endif
    {!! Form::label('geom', __('Building Footprint (KML File)'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-5">
        <!-- if building approved, kml file is preloaded -->
        @if ($buildingSurvey)
            {!! Form::text('kml', $buildingSurvey->kml, [
                'class' => 'col-sm-10 control-label',
                'style' => 'overflow:hidden;color:grey !important',
                'readonly' => 'readonly',
            ]) !!}
            {!! Form::text('kml', $buildingSurvey->kml, ['hidden' => 'true']) !!}
            {!! Form::text('survey_id', $buildingSurvey->id, ['hidden' => 'true']) !!}
        @else
            {!! Form::text('kml', null, ['hidden' => 'true']) !!}

            {!! Form::file('geom', null, ['class' => 'form-control col-sm-10', 'placeholder' => 'KML File']) !!}
            <small class="form-text" id="fileSizeHintKML">(KML File size should not be more than 1MB)</small>

        @endif

    </div>
</div>
<div class="form-group row">
    {!! Form::label('house_image', __('House Image'), ['class' => 'col-sm-3 control-label']) !!}
    <div class="col-sm-5">
        {!! Form::file('house_image', null, [ 'class' => 'form-control col-sm-10']) !!}
        <small class="form-text" id="fileSizeHintImg">(Image (JPG,JPEG) size should not be more than 5MB)</small>
    </div>
</div>
</div><!-- /.card-body -->

<div class="card-footer">
    <a href="{{ action('BuildingInfo\BuildingController@index') }}" class="btn btn-info">Back to List</a>
    {!! Form::submit('Save', [
        'class' => 'btn btn-info prevent-multiple-submits',
        'id' => 'prevent-multiple-submits',
    ]) !!}
</div><!-- /.card-footer -->
@if (!empty($building))
    <div class="card">
        <h2 class="ml-4 mt-3"> Containment Information </h2>
        <div class="card-header">
            <a href="{{ action('Fsm\ContainmentController@createContainment', [$building->bin]) }}"
                class="btn btn-info">Add Containment to Building</a>
        </div>
        <div class="card-body">
            @include('fsm.containments.list-containments')
        </div>
    </div>
@endif
<!-- Last Modified Date: 011-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- <script>
    $(document).ready(function() {
        $('.road_code').on('change', function() {
            var selectedRoadCode = $(this).find('option:selected').text();
            var codePart = selectedRoadCode.split(" - ")[0];
            if (codePart) {
                $.ajax({
                    url: '{{ route('buildings.check-house') }}',
                    type: 'GET',
                    data: {
                        road_code: codePart
                    },
                    success: function(response) {
                        var houseNumberSuffix = (response.count + 1).toString().padStart(4, '0');
                        var newHouseNumberValue = codePart + ' - ' + houseNumberSuffix;
                        $('input[name="house_number"]').val(newHouseNumberValue);
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr);
                    }
                });
            }
        });
    });
</script> --}}
<script>
    // Enforce digits and trailing 'x' characters, auto-insert hyphens: 00-000-0000-00
    function formatTaxCode(el) {
        let v = (el.value || '').toLowerCase();
        // keep only digits and 'x'
        v = v.replace(/[^0-9x]/g, '');
        // count x characters and remove them from digit sequence
        const xCount = (v.match(/x/g) || []).length;
        let digits = v.replace(/x/g, '');
        // limit total raw chars (digits + x) to 11 (2+3+4+2)
        const maxRaw = 11;
        if (digits.length + xCount > maxRaw) {
            // prefer keeping digits; trim excess from digits first
            const allowedDigits = Math.min(digits.length, maxRaw);
            digits = digits.slice(0, allowedDigits);
        }
        const xs = 'x'.repeat(Math.min(xCount, Math.max(0, maxRaw - digits.length)));
        const clean = (digits + xs).slice(0, maxRaw);

        // split into groups [2,3,4,2]
        const groups = [];
        const lens = [2, 3, 4, 2];
        let idx = 0;
        for (let i = 0; i < lens.length && idx < clean.length; i++) {
            groups.push(clean.substr(idx, lens[i]));
            idx += lens[i];
        }
        el.value = groups.join('-');
    }

    // NOTE: Do not auto-format the hidden tax_code input; the tag UI handles display and formatting.
</script>
<script>
    // Tag input for Tax Code/Holding ID
    (function () {
        const input = document.getElementById('tax_code_input');
        const tagsList = document.getElementById('tax-code-tags');
        const hidden = document.getElementById('tax_code_hidden');
        const errorEl = document.getElementById('tax-code-error');
        const TAG_FORMAT = /^\d{2}-\d{3}-\d{4}-[0-9x]{2}$/i; // allowed final 'x' chars in last group
        const MAX_RAW = 11; // 2+3+4+2

        // Format raw input into 00-000-0000-00 (allows 'x' only at end groups via handling)
        function formatTaxCodeValue(str) {
            let v = (str || '').toLowerCase();
            v = v.replace(/[^0-9x]/g, '');
            const xCount = (v.match(/x/g) || []).length;
            let digits = v.replace(/x/g, '');
            if (digits.length + xCount > MAX_RAW) {
                const allowedDigits = Math.min(digits.length, MAX_RAW);
                digits = digits.slice(0, allowedDigits);
            }
            const xs = 'x'.repeat(Math.min(xCount, Math.max(0, MAX_RAW - digits.length)));
            const clean = (digits + xs).slice(0, MAX_RAW);
            const lens = [2,3,4,2];
            const groups = [];
            let idx = 0;
            for (let i = 0; i < lens.length && idx < clean.length; i++) {
                groups.push(clean.substr(idx, lens[i]));
                idx += lens[i];
            }
            return groups.join('-');
        }

        // Render a tag element
        function renderTag(value) {
            const li = document.createElement('li');
            li.className = 'tax-tag';
            li.style.cssText = 'display:flex;align-items:center;background:#e9f2ff;margin:4px 6px;padding:4px 8px;border-radius:12px;font-size:0.9em;';
            li.setAttribute('data-value', value);

            const span = document.createElement('span');
            span.textContent = value;
            span.style.marginRight = '8px';

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.innerHTML = '&times;';
            btn.style.cssText = 'border:0;background:transparent;cursor:pointer;font-size:1em;line-height:1;padding:0;';
            btn.addEventListener('click', function () {
                tagsList.removeChild(li);
                updateHidden();
            });

            li.appendChild(span);
            li.appendChild(btn);
            tagsList.appendChild(li);
        }

        // Add tag if valid
        function addTag(raw) {
            const formatted = formatTaxCodeValue(raw);
            if (!formatted) return;
            if (!TAG_FORMAT.test(formatted)) {
                showError('Tax Code must be in XX-XXX-XXXX-XX format. "x" allowed only in last two characters.');
                return;
            }
            // avoid duplicates
            const existing = Array.from(tagsList.querySelectorAll('li')).map(n => n.getAttribute('data-value'));
            if (existing.includes(formatted)) {
                input.value = '';
                return clearError();
            }
            renderTag(formatted);
            input.value = '';
            clearError();
            updateHidden();
        }

        function updateHidden() {
            const vals = Array.from(tagsList.querySelectorAll('li')).map(n => n.getAttribute('data-value'));
            hidden.value = vals.join(',');
        }

        function showError(msg) {
            errorEl.textContent = msg;
            errorEl.style.display = 'block';
        }

        function clearError() {
            errorEl.textContent = '';
            errorEl.style.display = 'none';
        }

        // handle input formatting while typing
        input.addEventListener('input', function (e) {
            const cursorPos = input.selectionStart;
            const before = input.value;
            const formatted = formatTaxCodeValue(before);
            input.value = formatted.replace(/-/g, ''); // keep typing without hyphens inside input (we want user to see hyphens optionally)
            // After converting, set a visual formatted value for UX: show hyphens while typing by setting placeholder-like overlay:
            // Simpler: show formatted text in the input (with hyphens) but keep selection at end
            input.value = formatted;
            input.setSelectionRange(input.value.length, input.value.length);
            clearError();
        });

        // add tag on comma or Enter
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const raw = input.value;
                addTag(raw);
            } else if (e.key === 'Backspace' && input.value === '') {
                // if input empty and backspace pressed, remove last tag
                const last = tagsList.querySelector('li:last-child');
                if (last) {
                    tagsList.removeChild(last);
                    updateHidden();
                }
            }
        });

        // also add tag when input loses focus (if any content)
        input.addEventListener('blur', function () {
            const val = input.value.trim();
            if (val !== '') addTag(val);
        });

        // prepopulate from existing hidden value (model binding or old input)
        function preload() {
            const val = hidden.value || '{{ old("tax_code") ?? (isset($building) ? $building->tax_code : "") }}';
            if (!val) return;
            // if server provided comma-separated or single value, split
            const items = val.split(',').map(s => s.trim()).filter(Boolean);
            items.forEach(it => {
                const formatted = formatTaxCodeValue(it);
                if (formatted && TAG_FORMAT.test(formatted)) renderTag(formatted);
            });
            updateHidden();
        }

        // initialize
        document.addEventListener('DOMContentLoaded', function () {
            preload();
        });

        // expose for debugging (optional)
        window._taxCodeTagInput = {
            addTag: addTag,
            formatTaxCodeValue: formatTaxCodeValue,
            updateHidden: updateHidden
        };
    })();
</script>
