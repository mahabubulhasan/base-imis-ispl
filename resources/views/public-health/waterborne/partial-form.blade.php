<style>
    #map {
        width: 800px;
        height: 400px;
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
<link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css">

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
    <div class="form-group required row">
        {!! Form::label('year',__('Year'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <select name="year" class="form-control" id="dropdownYear"
             onchange="getProjectReportFunc()">
        </select>
        </div>
    </div>
    <div class="form-group required row">
        {!! Form::label('infected_disease', __('Infected Disease'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('infected_disease', $enumValues, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Choose Infected Disease')]) !!}
        </div>
    </div>

    <h4>{{__('No. of Cases')}}</h4>



    <div class="form-group required row">
        {!! Form::label('male_cases',__('Male'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('male_cases',null,['class' => 'form-control', 'placeholder' => __('Male'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); "]) !!}
        </div>
    </div>

    <div class="form-group required row">
        {!! Form::label('female_cases',__('Female'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('female_cases',null,['class' => 'form-control', 'placeholder' => __('Female'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); "]) !!}
        </div>
    </div>

    <div class="form-group required row">
        {!! Form::label('other_cases',__('Other'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('other_cases',null,['class' => 'form-control', 'placeholder' => __('Other'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); "]) !!}
        </div>
    </div>
    <h4>{{__('No. of Fatalities')}}</h4>
    <div class="form-group  row">
        {!! Form::label('male_fatalities',__('Male'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('male_fatalities',null,['class' => 'form-control', 'placeholder' => __('Male'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); "]) !!}
        </div>
    </div>
    <div class="form-group  row">
        {!! Form::label('female_fatalities',__('Female'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('female_fatalities',null,['class' => 'form-control', 'placeholder' => __('Female'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); "]) !!}
        </div>
    </div>

    <div class="form-group  row">
        {!! Form::label('other_fatalities',__('Other'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('other_fatalities',null,['class' => 'form-control', 'placeholder' => __('Other'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, ''); "]) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('notes', __('Notes'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => __('Notes')]) !!}
        </div>
    </div>
    <div class="card-footer">
        <a href="{{ action('PublicHealth\YearlyWaterborneController@index') }}" class="btn btn-info">{{__('Back to List')}}</a>
        {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
    </div>
    <script>
        var i, currentYear, startYear, endYear, newOption, dropdownYear;
        dropdownYear = document.getElementById("dropdownYear");
        currentYear = (new Date()).getFullYear();
        startYear = currentYear;
        endYear = currentYear - 3;
        for (i = startYear; i >= endYear; i--) {
            newOption = document.createElement("option");
            newOption.value = i;
            newOption.label = i;
            if (i == currentYear) {
                newOption.selected = true;
            }
            dropdownYear.appendChild(newOption);
        }
    </script>
