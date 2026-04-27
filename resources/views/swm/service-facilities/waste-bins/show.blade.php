@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class='card card-info'><div class='card-header bg-transparent'><a href='{{ route('swm.waste-bins.index') }}' class='btn btn-info'>{{__('Back to List')}}</a></div><div class='card-body'><p><b>{{__('Household')}}:</b> {{ optional($wasteBin->household)->household_id }}</p><p><b>{{__('BIN')}}:</b> {{ $wasteBin->bin }}</p><p><b>{{__('Number of waste bins')}}:</b> {{ $wasteBin->number_of_waste_bins }}</p><p><b>{{__('Total capacity of waste bins (kg)')}}:</b> {{ $wasteBin->total_capacity_kg }}</p></div></div>
@stop
