<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<style>
    #map {
        width: 100px;
        height: 50px;
        /* 100% of the viewport height - navbar height */
    }

    #olmap {
        border: 1px solid #000000;
        margin-top: 20px;
    }

    a.skiplink {
        position: absolute;
        clip: rect(1px, 1px, 1px, 1px);
        padding: 0;
        border: 0;
        height: 1px;
        width: 1px;
        overflow: hidden;
    }

    a.skiplink:focus {
        clip: auto;
        height: auto;
        width: auto;
        background-color: #fff;
        padding: 0.3em;
    }

    #map:focus {
        outline: #4A74A8 solid 0.15em;
    }
</style>
<link rel="stylesheet" href="https://unpkg.com/ol-layerswitcher@3.8.3/dist/ol-layerswitcher.css" />
<style>
    .layer-switcher {
        top: 0.5em;
    }

    .layer-switcher button {
        width: 25px;
        height: 25px;
        background-position: unset;
        background-size: contain;
    }
</style>

<div class="card-body">
    <div class="form-group row required" id="treatment_plant_id">
        {!! Form::label('treatment_plant_id', __('Treatment Plant'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('treatment_plant_id', $trtName, null, [
                'class' => 'form-control',
                'placeholder' => __('Treatment Plant'),
            ]) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('date', __('Sample Date'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::date('date', null, [
                'class' => 'form-control',
                'placeholder' => __('Choose Sample Date'),
                'onclick' => 'this.showPicker();',
                'max' => date('Y-m-d')
            ]) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('temperature', __('Temperature °C'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3 input-group">
            {!! Form::text('temperature', null, [
                'class' => 'form-control',
                'placeholder' => __('Temperature °C'),
                'oninput' => "this.value = this.value.replace(/(?!^-)[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')"
            ]) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('ph', __('pH'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('ph', null, [
                'class' => 'form-control',
                'placeholder' => __('pH'),
                'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')"
            ]) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('cod', __('COD (mg/l)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('cod', null, [
                'class' => 'form-control',
                'placeholder' => __('COD (mg/l)'),
                'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')"
            ]) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('bod', __('BOD (mg/l)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('bod', null, [
                'class' => 'form-control',
                'placeholder' => __('BOD (mg/l)'),
                'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')"
            ]) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('tss', __('TSS (mg/l)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('tss', null, [
                'class' => 'form-control',
                'placeholder' => __('TSS (mg/l)'),
                'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\\..*?)\\..*/g, '$1')"
            ]) !!}
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('ecoli', __('Ecoli'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('ecoli', null, [
                'class' => 'form-control',
                'placeholder' => __('Ecoli'),
                'oninput' => "this.value = this.value.replace(/[^0-9]/g, '')"
            ]) !!}
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('remarks', __('Remark'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('remarks', null, [
                'class' => 'form-control',
                'placeholder' => __('Remark')
            ]) !!}
        </div>
    </div>
</div>


</div><!-- /.box-body -->
<div class="card-footer">
    <a href="{{ action('Fsm\TreatmentPlantTestController@index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}

</div><!-- /.box-footer -->
