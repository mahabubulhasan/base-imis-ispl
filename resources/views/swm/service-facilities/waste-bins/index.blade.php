@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class='card'><div class='card-header'><a href='{{ route('swm.waste-bins.create') }}' class='btn btn-info'>{{__('Add Waste Bin')}}</a></div><div class='card-body'><table id='data-table' class='table table-bordered'><thead><tr><th>{{__('Household')}}</th><th>{{__('Type of Waste Bin')}}</th><th>{{__('Placed at Buildings')}}</th><th>{{__('BIN')}}</th><th>{{__('Ward No.')}}</th><th>{{__('Capacity (kg)')}}</th></tr></thead></table></div></div>
@stop
@push('scripts')
<script>$(function(){ $('#data-table').DataTable({processing:true,serverSide:true,ajax:'{!! route("swm.waste-bins.data") !!}',columns:[{data:'household_code',name:'household_code'},{data:'waste_bin_type_name',name:'waste_bin_type_name',orderable:false,searchable:false},{data:'placed_at_buildings_label',name:'placed_at_buildings_label',orderable:false,searchable:false},{data:'bin',name:'bin'},{data:'ward_no',name:'ward_no'},{data:'total_capacity_kg',name:'total_capacity_kg'}]});});</script>
@endpush
