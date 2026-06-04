@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ route('swm.primary-collection-sites.index') }}" class="btn btn-info">{{ __('Back to List') }}</a>
        @can('Edit Household')
        <a href="{{ route('swm.primary-collection-sites.edit', $primaryCollectionSite->id) }}" class="btn btn-info">{{ __('Edit') }}</a>
        @endcan
    </div>
    <div class="form-horizontal swm-primary-collection-site-form-mobile app-mobile-form">
        <div class="card-body">
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Customer ID') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->customer_id, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Customer Name') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->customer_name, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Contact Number') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->contact_number, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Area / Mohalla Name') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->area_mohalla_name, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('BIN') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->bin, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Ward') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->ward, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Road No.') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->road_no, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Road Name') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->road_name, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Holding Number') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->holding_number, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Tax ID') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->tax_id, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Functional Use') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->functional_use, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Waste Charge') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->waste_charge, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Number of Family Members') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->number_of_family_members, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Using This Service Since') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->using_this_service_since, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Total volume of the waste collected (daily average approx.)') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->daily_waste_volume, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Remarks') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->remarks, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Survey Date') }}</label><div class="col-sm-3">{!! Form::label(null, optional($primaryCollectionSite->survey_date)->format('Y-m-d'), ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Van Puller') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->van_puller_name ?? $primaryCollectionSite->van_puller_id, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Owner') }} ({{ __('Yes') }}/{{ __('No') }})</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->is_owner ? __('Yes') : __('No'), ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('LIC') }} ({{ __('Yes') }}/{{ __('No') }})</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->is_lic ? __('Yes') : __('No'), ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('LIC ID (if Y)') }}</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->lic_id, ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Segregation Practiced') }} ({{ __('Yes') }}/{{ __('No') }})</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->segregation_practiced ? __('Yes') : __('No'), ['class' => 'form-control']) !!}</div></div>
            <div class="form-group row"><label class="col-sm-3 control-label">{{ __('Waste bin Provided') }} ({{ __('Yes') }}/{{ __('No') }})</label><div class="col-sm-3">{!! Form::label(null, $primaryCollectionSite->waste_bin_provided ? __('Yes') : __('No'), ['class' => 'form-control']) !!}</div></div>
        </div>
    </div>
</div>
@stop
