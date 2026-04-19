<div class="card-body">
    <div class="form-group row required">
        {!! Form::label('lic_id', __('LIC ID'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('lic_id', null, ['class' => 'form-control', 'placeholder' => __('LIC ID')]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('representative_name', __("LIC Representative's Name"), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('representative_name', null, ['class' => 'form-control', 'placeholder' => __("LIC Representative's Name")]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('contact_no', __('Contact No.'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('contact_no', null, ['class' => 'form-control', 'placeholder' => __('Contact No.')]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('number_of_hhs', __('Number of HHs'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('number_of_hhs', null, ['class' => 'form-control', 'placeholder' => __('Number of HHs'), 'min' => 0]) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('total_population', __('Total Population'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('total_population', null, ['class' => 'form-control', 'placeholder' => __('Total Population'), 'min' => 0]) !!}
        </div>
    </div>
</div>
<div class="card-footer">
    <a href="{{ route('swm.lic.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
    {!! Form::submit( __('Save'), ['class' => 'btn btn-info']) !!}
</div>
