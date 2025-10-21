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

    <div class="form-group row required">
        {!! Form::label('name', __('Language'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Language')]) !!}
        </div>
    </div>
    {{-- <div class="form-group row required">
        {!! Form::label('short', 'Short', ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('short', null, ['class' => 'form-control', 'placeholder' => 'Short' ]) !!}
        </div>
    </div> --}}
    <div class="form-group row required">
        {!! Form::label('code', __('Code'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('code', null, ['class' => 'form-control', 'placeholder' => __('Code') ]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('status', __('Status'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('status', ['1' => 'Active', '0' => 'Disabled'], null, ['class' => 'form-control', 'placeholder' => __('Status')]) !!}
        </div>
    </div>


</div>


</div><!-- /.box-body -->
<div class="card-footer">
    <a href="{{ action('Language\LanguageController@index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}

</div><!-- /.box-footer -->
