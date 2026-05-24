{{-- Last Modified Date: 14-04-2024
 Developed By: Innovative Solution Pvt. Ltd. (ISPL)   --}}
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
    <div class="card-header bg-transparent">
        <a href="{{ action('LayerInfo\LowIncomeCommunityController@index') }}" class="btn btn-info">{{ __('Back to List') }}</a>

    </div><!-- /.card-header -->
    <div class="form-horizontal">
        <div class="card-body">
    <div class="form-group required row">
        {!! Form::label('community_name',__('LIC Name'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->community_name,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('representative_name',__("Representative's Name"),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->representative_name,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('representative_contact_no',__("Representative's Contact No."),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->representative_contact_no,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group required row">
         {!! Form::label('no_of_buildings',__('No. of Buildings'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->no_of_buildings,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group required row">
          {!! Form::label('number_of_households',__('No. of Households'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->number_of_households,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group required row">
        {!! Form::label('population_total',__('Total Population'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->population_total,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
         {!! Form::label('population_male',__('Male Population'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->population_male,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('population_female',__('Female Population'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->population_female,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
         {!! Form::label('population_others',__('Other Population'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->population_others,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('water_connection_status',__('Water Connection Status'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->water_connection_status === null ? '' : ($lic->water_connection_status ? __('Yes') : __('No')),['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('no_of_wate_points',__('No. of Wate Points'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->no_of_wate_points,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('sanitation_status',__('Sanitation Status'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->sanitation_status === null ? '' : ($lic->sanitation_status ? __('Yes') : __('No')),['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('no_of_community_toilets',__('No. of Community Toilets'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->no_of_community_toilets,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
         {!! Form::label('no_of_septic_tank',__('No. of Septic Tanks'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->no_of_septic_tank,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('no_of_holding_tank',__('No. of Holding Tanks'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->no_of_holding_tank,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
          {!! Form::label('no_of_pit',__('No. of Pits'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->no_of_pit,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
         {!! Form::label('no_of_sewer_connection',__('No. of Sewer Connections'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->no_of_sewer_connection,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('area_decima',__('Area (Decimal)'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->area_decima,['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('remarks',__('Remarks'),['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$lic->remarks,['class' => 'form-control']) !!}
        </div>
    </div>

        </div><!-- /.card-body -->
    </div>
</div><!-- /.card -->
@stop

