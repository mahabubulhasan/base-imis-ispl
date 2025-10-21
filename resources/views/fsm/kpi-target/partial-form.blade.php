<div class="card-body">

    <div class="form-group row required">
        {!! Form::label('indicator_id',__('Indicator'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('indicator_id', $indicators, null, ['class' => 'form-control chosen-select', 'placeholder' => __('Indicator')]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('year',__('Year'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('year',null,['class' => 'form-control', 'placeholder' => __('Year'),'oninput' => "this.value = this.value < 0 ? '' : this.value"]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('target',__('Target (%)'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('target',null,['class' => 'form-control', 'placeholder' => __('Target (%)'),'oninput' => "this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'); this.value = this.value < 0 || this.value.startsWith('-') ? '' : this.value;"]) !!}
        </div>
    </div>


</div><!-- /.box-body -->
<div class="card-footer">
    <a href="{{ action('Fsm\KpiTargetController@index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div><!-- /.box-footer -->
