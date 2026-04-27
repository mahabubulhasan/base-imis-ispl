@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class='card'><div class='card-header'><a href='{{ route('swm.waste-bins.create') }}' class='btn btn-info'>{{__('Add Waste Bin')}}</a></div><div class='card-body'><table id='data-table' class='table table-bordered'><thead><tr><th>{{__('Household')}}</th><th>{{__('BIN')}}</th><th>{{__('Number of waste bins')}}</th><th>{{__('Total capacity of waste bins (kg)')}}</th></tr></thead></table></div></div>
@stop
@push('scripts')
<script>$(function(){ $('#data-table').DataTable({processing:true,serverSide:true,ajax:'{!! route("swm.waste-bins.data") !!}',columns:[{data:'household_code',name:'household_code'},{data:'bin',name:'bin'},{data:'number_of_waste_bins',name:'number_of_waste_bins'},{data:'total_capacity_kg',name:'total_capacity_kg'}]});});</script>
@endpush
