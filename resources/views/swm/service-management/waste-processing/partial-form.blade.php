@php
    $isEdit = isset($wasteProcessingLog) && $wasteProcessingLog;
    $entryVal = old('entry_at');
    if ($entryVal === null && $isEdit && $wasteProcessingLog->entry_at) {
        $entryVal = $wasteProcessingLog->entry_at->format('Y-m-d\TH:i');
    }
    if ($entryVal === null && ! $isEdit) {
        $entryVal = \Carbon\Carbon::now()->format('Y-m-d\TH:i');
    }
    $reportDateVal = old('report_date', $isEdit && $wasteProcessingLog->report_date ? $wasteProcessingLog->report_date->format('Y-m-d') : \Carbon\Carbon::now()->format('Y-m-d'));
    $reportingMonthVal = old('reporting_month', $isEdit && $wasteProcessingLog->reporting_month ? $wasteProcessingLog->reporting_month->format('Y-m') : \Carbon\Carbon::now()->format('Y-m'));
@endphp
<div class="swm-waste-processing-form-mobile app-mobile-form">
<div class="card-body">
    @if($isEdit)
        <div class="form-group row">
            <label class="col-sm-3 control-label">{{ __('Waste Processing Log ID') }}</label>
            <div class="col-sm-3">
                {!! Form::label(null, $wasteProcessingLog->id, ['class' => 'form-control']) !!}
            </div>
        </div>
    @endif

    <div class="form-group row required">
        {!! Form::label('entry_at', __('Entry Date and Time'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="datetime-local" name="entry_at" id="entry_at" class="form-control" value="{{ $entryVal }}" />
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('report_date', __('Report Date'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="date" name="report_date" id="report_date" class="form-control" value="{{ $reportDateVal }}" />
        </div>
    </div>

    <div class="form-group row required">
        {!! Form::label('reporting_month', __('Reporting Month'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="month" name="reporting_month" id="reporting_month" class="form-control" value="{{ $reportingMonthVal }}" />
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('waste_processing_site_name', __('Waste Processing Site Name'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="text" name="waste_processing_site_name" id="waste_processing_site_name" class="form-control"
                value="{{ old('waste_processing_site_name', $isEdit ? $wasteProcessingLog->waste_processing_site_name : null) }}" />
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('waste_received_ton', __('Quantity of Waste Received (Ton)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="number" name="waste_received_ton" id="waste_received_ton" class="form-control" step="0.01" min="0"
                value="{{ old('waste_received_ton', $isEdit ? $wasteProcessingLog->waste_received_ton : null) }}" />
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('organic_waste_composted_ton', __('Organic Waste Composted (Ton)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="number" name="organic_waste_composted_ton" id="organic_waste_composted_ton" class="form-control" step="0.01" min="0"
                value="{{ old('organic_waste_composted_ton', $isEdit ? $wasteProcessingLog->organic_waste_composted_ton : null) }}" />
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('inorganic_waste_recycled_ton', __('Inorganic Non-biodegradable Waste Recycled (Ton)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="number" name="inorganic_waste_recycled_ton" id="inorganic_waste_recycled_ton" class="form-control" step="0.01" min="0"
                value="{{ old('inorganic_waste_recycled_ton', $isEdit ? $wasteProcessingLog->inorganic_waste_recycled_ton : null) }}" />
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('waste_incinerated_ton', __('Waste Incinerated (Ton)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="number" name="waste_incinerated_ton" id="waste_incinerated_ton" class="form-control" step="0.01" min="0"
                value="{{ old('waste_incinerated_ton', $isEdit ? $wasteProcessingLog->waste_incinerated_ton : null) }}" />
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('waste_burned_open_air_ton', __('Waste Burned in Open Air (Ton)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="number" name="waste_burned_open_air_ton" id="waste_burned_open_air_ton" class="form-control" step="0.01" min="0"
                value="{{ old('waste_burned_open_air_ton', $isEdit ? $wasteProcessingLog->waste_burned_open_air_ton : null) }}" />
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('residual_waste_landfilled_ton', __('Residual Waste Landfilled (Ton)'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            <input type="number" name="residual_waste_landfilled_ton" id="residual_waste_landfilled_ton" class="form-control" step="0.01" min="0"
                value="{{ old('residual_waste_landfilled_ton', $isEdit ? $wasteProcessingLog->residual_waste_landfilled_ton : null) }}" />
        </div>
    </div>

    <div class="form-group row">
        {!! Form::label('remarks', __('Remarks'), ['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::textarea('remarks', old('remarks', $isEdit ? $wasteProcessingLog->remarks : null), ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('Remarks')]) !!}
        </div>
    </div>
</div>
<div class="card-footer">
    <a href="{{ route('swm.waste-processing.index') }}" class="btn btn-info">{{ __('Back to List')}}</a>
    {!! Form::submit(__('Save'), ['class' => 'btn btn-info']) !!}
</div>
</div>
