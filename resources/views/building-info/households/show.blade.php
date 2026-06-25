@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('building-info.households.index') }}" class="btn btn-info">{{__('Back to List')}}</a>
    </div>
    <div class="form-horizontal">
        <div class="card-body">
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Household ID') }}</label><div class="col-sm-3">{!! Form::label(null, $household->household_id, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Household Owner Name') }}</label><div class="col-sm-3">{!! Form::label(null, $household->household_owner_name, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __("Father's/Husband's Name") }}</label><div class="col-sm-3">{!! Form::label(null, $household->father_or_husband_name, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Contact No.') }}</label><div class="col-sm-3">{!! Form::label(null, $household->contact_number, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Location') }}</label><div class="col-sm-3">{!! Form::label(null, $household->sub_location, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('BIN') }}</label><div class="col-sm-3">{!! Form::label(null, $household->bin, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Ward') }}</label><div class="col-sm-3">{!! Form::label(null, $household->ward, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Road No.') }}</label><div class="col-sm-3">{!! Form::label(null, $household->road_no, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Road Name') }}</label><div class="col-sm-3">{!! Form::label(null, $household->road_name, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Holding No.') }}</label><div class="col-sm-3">{!! Form::label(null, $household->holding_number, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Tax ID') }}</label><div class="col-sm-3">{!! Form::label(null, $household->tax_id, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Waste Collection Fee') }} ({{ __('Taka') }}/{{ __('Month') }})</label><div class="col-sm-3">{!! Form::label(null, currency($household->waste_charge), ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Number of Family Members') }}</label><div class="col-sm-3">{!! Form::label(null, $household->number_of_family_members, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Using This Service Since') }}</label><div class="col-sm-3">{!! Form::label(null, $household->using_this_service_since, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Avg Waste Collected') }} ({{ __('Kg') }}/{{ __('Day') }})</label><div class="col-sm-3">{!! Form::label(null, $household->daily_waste_volume, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Van Puller') }}</label><div class="col-sm-3">{!! Form::label(null, $household->van_puller_name ?? $household->van_puller_id, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Building Owner?') }}</label><div class="col-sm-3">{!! Form::label(null, $household->is_owner ? __('Yes') : __('No'), ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Waste Bin Provided?') }}</label><div class="col-sm-3">{!! Form::label(null, $household->waste_bin_provided ? __('Yes') : __('No'), ['class' => 'form-control']) !!}</div></div>
            @if($household->waste_bin_provided && $household->wasteBins->isNotEmpty())
            <div class="form-group row">
                <label class="col-sm-3 control-label">{{ __('Waste Bins') }}</label>
                <div class="col-sm-9">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Waste Bin Type') }}</th>
                                <th>{{ __('Capacity (kg)') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($household->wasteBins as $wb)
                                <tr>
                                    <td>{{ optional($wb->wasteBinType)->name }}</td>
                                    <td>{{ $wb->total_capacity_kg }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('LIC?') }}</label><div class="col-sm-3">{!! Form::label(null, $household->is_lic ? __('Yes') : __('No'), ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('LIC ID') }}</label><div class="col-sm-3">{!! Form::label(null, $household->lic_id ? ((optional($household->lic)->community_name ? optional($household->lic)->community_name.' - ' : '').$household->lic_id) : null, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Segregation Practiced?') }}</label><div class="col-sm-3">{!! Form::label(null, $household->segregation_practiced ? __('Yes') : __('No'), ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Household Status') }}</label><div class="col-sm-3">{!! Form::label(null, \App\Models\BuildingInfo\Household::statusOptions()[$household->status] ?? $household->status, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Remarks') }}</label><div class="col-sm-3">{!! Form::label(null, $household->remarks, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Survey Date') }}</label><div class="col-sm-3">{!! Form::label(null, $household->survey_date?->format('Y-m-d'), ['class' => 'form-control']) !!}</div></div>
        </div>
    </div>
</div>
@stop
