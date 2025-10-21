<!-- Containment ID -->

<div id="containment-info" style="display: none;margin:12px">
    <h2 class=""> {{ __("Containment Information") }} </h2>




    <div class="form-group row required" id='containment-type'>
        {!! Form::label('type_id',__('Containment Type'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::select('type_id', $containment_type, null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => __('Containment Type'),
            ]) !!}
        </div>
    </div>

    @if (!empty($containment_building->sewer_code) && !empty($containment_building))
        <div class="form-group row required " id="sewer-code">
            {!! Form::label('sewer_code', __('Sewer Code'), ['class' => 'col-sm-3 control-label  ']) !!}
            <div class="col-sm-5">
                {!! Form::select('sewer_code', $sewer_code, $containment_building->sewer_code, [
                    'class' => 'form-control col-sm-10 sewer_code',
                    'placeholder' => __('Sewer Code'),
                    'id' => 'sewer_code',
                ]) !!}
            </div>
        </div>
    @elseif(empty($containment_building->sewer_code) && !empty($containment_building))
        <div class="form-group row required" id="sewer-code">
            {!! Form::label('sewer_code',__('Sewer Code'), ['class' => 'col-sm-3 control-label  ']) !!}
            <div class="col-sm-5">
                {!! Form::select('sewer_code', $sewer_code, $containment_building->sewer_code, [
                    'class' => 'form-control col-sm-10 sewer_code',
                    'placeholder' => __('Sewer Code'),
                    'id' => 'sewer_code',
                ]) !!}
            </div>
        </div>
    @endif

    @if (!empty($containment_building->drain_code) && !empty($containment_building))
        <div class="form-group row required" id="drain-code">
            {!! Form::label('drain_code', __('Drain Code'), ['class' => 'col-sm-3 control-label  ']) !!}
            <div class="col-sm-5">
                {!! Form::select('drain_code', $drain_code, $containment_building->drain_code, [
                    'class' => 'form-control col-sm-10 drain_code',
                    'placeholder' => __('Drain Code'),
                    'id' => 'drain_code',
                ]) !!}
            </div>
        </div>
    @elseif(empty($containment_building->drain_code) && !empty($containment_building))
        <div class="form-group row required" id="drain-code">
            {!! Form::label('drain_code', __('Drain Code'), ['class' => 'col-sm-3 control-label  ']) !!}
            <div class="col-sm-5">
                {!! Form::select('drain_code', $drain_code, $containment_building->drain_code, [
                    'class' => 'form-control col-sm-10 drain_code',
                    'placeholder' => __('Drain Code'),
                    'id' => 'drain_code',
                ]) !!}
            </div>
        </div>
    @endif

    <div class="form-group row " id="pit-shape" style="display:none">
        {!! Form::label('pit_shape', __('Pit Shape'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::select(
                __('pit_shape'),
                [
                    'Cylindrical' => 'Cylindrical',
                    'Rectangular' => 'Rectangular'
                ],
                'Rectangular',
                ['class' => 'form-control col-sm-10', 'placeholder' => __('Pit Shape')],
            ) !!}
        </div>
    </div>
    <div id="pit-size" style="display: none">
        <div class="form-group row">
            {!! Form::label('pit_diameter',__('Pit Diameter (m)'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('pit_diameter', null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => __('Pit Diameter (m)'),
                   'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')", // Allow decimal values

                ]) !!}
            </div>
        </div>
        <div class="form-group row " id="pit-depth" style="display: none">
            {!! Form::label('pit_depth', __('Pit Depth (m)',), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('pit_depth', null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => __('Pit Depth (m)'),
                   'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')", // Allow decimal values
                ]) !!}
            </div>
        </div>
    </div>
    <div id="tank-size">
        <div class="form-group row" id ="tank-length">
            {!! Form::label('tank_length', __('Tank Length (m)'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('tank_length', null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => __('Tank Length (m)'),
                   'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')", // Allow decimal values

                ]) !!}
            </div>
        </div>
        <div class="form-group row " id ="tank-width">
            {!! Form::label('tank_width', __('Tank Width (m)'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('tank_width', null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => __('Tank Width (m)'),
                   'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')", // Allow decimal values
                ]) !!}
            </div>
        </div>
        <div class="form-group row " id ="tank-depth">
            {!! Form::label('depth', __('Tank Depth (m)'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::text('depth', null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => __('Tank Depth (m)'),
                   'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')", // Allow decimal values

                ]) !!}
            </div>
        </div>
    </div>
    <div class="form-group row required" id="size">
        {!! Form::label('size', __('Containment Volume (m³)'), ['class' => 'col-sm-3 control-label ']) !!}
        <div class="col-sm-5">
            {!! Form::text('size', null, [
                'class' => 'form-control col-sm-10',
                'placeholder' => __('Containment Volume (m³)(Enter Dimensions to auto calculate)'),
               'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')", // Allow decimal values

            ]) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('location', __('Containment Location'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::select(
                'location',
                [
                    'Inside the house' => 'Inside the house',
                    'Outside the house' => 'Outside the house',
                ],
                null,
                ['class' => 'form-control col-sm-10', 'placeholder' => __('Containment Location')],
            ) !!}
        </div>
    </div>



    <div id="septic-tank">
        <div class="form-group row">
            {!! Form::label('septic_criteria', __('Septic Tank Standard Compliance'), ['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-5">
                {!! Form::select('septic_criteria', [true => 'Yes', false => 'No'], null, [
                    'class' => 'form-control col-sm-10',
                    'placeholder' => __('Septic Tank Standard Compliance'),
                ]) !!}
            </div>
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('construction_date', __('Containment Construction Date'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-5">
            {!! Form::date('construction_date', null, [
                'class' => 'form-control col-sm-10',
                'autocomplete' => 'off',
                'placeholder' => __('Containment Construction Date'),
                'max' => now()->format('Y-m-d'),
                'onclick' => 'this.showPicker();'
            ]) !!}
        </div>
    </div>





</div> <!-- containmend id -->
