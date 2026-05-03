@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class='card card-info'><div class='card-header bg-transparent'><a href='{{ route('swm.waste-bins.index') }}' class='btn btn-info'>{{__('Back to List')}}</a></div><div class='card-body'>
    <p><b>{{__('Household')}}:</b> {{ optional($wasteBin->household)->household_id }}</p>
    <p><b>{{__('Type of Waste Bin')}}:</b> {{ optional($wasteBin->wasteBinType)->name }}</p>
    @if($wasteBin->type_other_detail)
        <p><b>{{__('Others (specify)')}}:</b> {{ $wasteBin->type_other_detail }}</p>
    @endif
    <p><b>{{__('Placed at Buildings')}}:</b> {{ $wasteBin->placed_at_buildings ? __('Yes') : __('No') }}</p>
    <p><b>{{__('BIN')}}:</b> {{ $wasteBin->bin }}</p>
    <p><b>{{__('Sub Location')}}:</b> {{ $wasteBin->sub_location }}</p>
    <p><b>{{__('Ward No.')}}:</b> {{ $wasteBin->ward_no }}</p>
    <p><b>{{__('Road No.')}}:</b> {{ $wasteBin->road_no }}</p>
    <p><b>{{__('Road Name')}}:</b> {{ $wasteBin->road_name }}</p>
    <p><b>{{__('Latitude')}}:</b> {{ $wasteBin->latitude }}</p>
    <p><b>{{__('Longitude')}}:</b> {{ $wasteBin->longitude }}</p>
    <p><b>{{__('Capacity (kg)')}}:</b> {{ $wasteBin->total_capacity_kg }}</p>
</div></div>
@stop
