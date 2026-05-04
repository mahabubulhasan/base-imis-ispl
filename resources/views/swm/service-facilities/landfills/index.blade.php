@extends('layouts.dashboard')
@push('style')
<style type="text/css">.dataTables_filter { display: none; }</style>
@endpush
@section('title', $page_title)
@section('content')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card app-mobile-index">
    <div class="card-header">
        @can('Add SW Landfill')
        <a href="{{ action('Swm\LandfillController@create') }}" class="btn btn-info">{{ __('Add Landfill') }}</a>
        @endcan
        @can('Export SW Landfills to CSV')
        <a href="#" id="export" class="btn btn-info">{{ __('Export to CSV') }}</a>
        @endcan
        <a href="#" class="btn btn-info float-right" data-toggle="collapse" data-target="#lf-collapse">{{ __('Show Filter') }}</a>
    </div>
    <div class="card-body"><div id="lf-collapse" class="collapse">
        <form id="filter-form" class="form-horizontal">
            <div class="form-group row">
                <label class="col-md-2" for="landfill_id">{{ __('Landfill ID') }}</label><div class="col-md-2"><input type="text" class="form-control" id="landfill_id"></div>
                <label class="col-md-2" for="name">{{ __('Name') }}</label><div class="col-md-2"><input type="text" class="form-control" id="name"></div>
                <label class="col-md-2" for="operator_name">{{ __('Operator Name') }}</label><div class="col-md-2"><input type="text" class="form-control" id="operator_name"></div>
            </div>
            <div class="form-group row">
                <label class="col-md-2" for="contact_number">{{ __('Contact Number') }}</label><div class="col-md-2"><input type="text" class="form-control" id="contact_number"></div>
                <label class="col-md-2" for="source_sts_id">{{ __('Source STS') }}</label>
                <div class="col-md-2"><select class="form-control chosen-select" id="source_sts_id"><option value="">{{ __('All') }}</option>@foreach($stsOptions as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach</select></div>
                <label class="col-md-2" for="waste_type_id">{{ __('Waste Type') }}</label>
                <div class="col-md-2"><select class="form-control chosen-select" id="waste_type_id"><option value="">{{ __('All') }}</option>@foreach($wasteTypes as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select></div>
            </div>
            <div class="form-group row">
                <label class="col-md-2" for="segregation_practiced">{{ __('Segregation Practiced') }}</label>
                <div class="col-md-2"><select class="form-control" id="segregation_practiced"><option value="">{{ __('All') }}</option><option value="true">{{ __('Yes') }}</option><option value="false">{{ __('No') }}</option></select></div>
                <label class="col-md-2" for="reuse_practiced">{{ __('Reuse Practiced') }}</label>
                <div class="col-md-2"><select class="form-control" id="reuse_practiced"><option value="">{{ __('All') }}</option><option value="true">{{ __('Yes') }}</option><option value="false">{{ __('No') }}</option></select></div>
                <label class="col-md-2" for="treatment">{{ __('Treatment') }}</label>
                <div class="col-md-2"><select class="form-control" id="treatment"><option value="">{{ __('All') }}</option><option value="true">{{ __('Yes') }}</option><option value="false">{{ __('No') }}</option></select></div>
            </div>
            <div class="form-group row">
                <label class="col-md-2" for="operational_status">{{ __('Operational Status') }}</label>
                <div class="col-md-2"><select class="form-control" id="operational_status"><option value="">{{ __('All') }}</option><option value="active">{{ __('Active') }}</option><option value="inactive">{{ __('Inactive') }}</option></select></div>
            </div>
            <div class="text-right"><button type="submit" class="btn btn-info">{{ __('Filter') }}</button> <button type="reset" id="reset-filter" class="btn btn-info">{{ __('Reset') }}</button></div>
        </form>
    </div></div>
    <div class="card-body"><div class="table-responsive">
        <table id="data-table" class="table table-bordered table-striped" width="100%">
            <thead><tr>
                <th>{{ __('Landfill ID') }}</th><th>{{ __('Name') }}</th><th>{{ __('Location') }}</th><th>{{ __('Operator Name') }}</th><th>{{ __('Contact Number') }}</th><th>{{ __('Capacity') }} ({{ __('Ton') }})</th><th>{{ __('Area') }}</th>
                <th>{{ __('Source STS') }}</th><th>{{ __('Source Wards') }}</th><th>{{ __('Segregation Practiced') }}</th><th>{{ __('Reuse Practiced') }}</th><th>{{ __('Waste Types') }}</th><th>{{ __('Monthly Waste for Composting') }} ({{ __('Ton') }})</th><th>{{ __('Treatment') }}</th><th>{{ __('Operational Status') }}</th><th>{{ __('Actions') }}</th>
            </tr></thead>
        </table>
    </div></div>
</div>
@stop
@push('scripts')
<script>
$(function(){
var dt=$('#data-table').DataTable({bFilter:false,processing:true,serverSide:true,scrollCollapse:true,
ajax:{url:'{!! route("swm.landfills.data") !!}',data:function(d){
d.landfill_id=$('#landfill_id').val();d.name=$('#name').val();d.operator_name=$('#operator_name').val();d.contact_number=$('#contact_number').val();
d.source_sts_id=$('#source_sts_id').val();d.waste_type_id=$('#waste_type_id').val();d.operational_status=$('#operational_status').val();
d.segregation_practiced=$('#segregation_practiced').val();d.reuse_practiced=$('#reuse_practiced').val();d.treatment=$('#treatment').val();
}},
columns:[
{data:'landfill_id',name:'landfill_id'},{data:'name',name:'name'},{data:'location',name:'location'},{data:'operator_name',name:'operator_name'},{data:'contact_number',name:'contact_number'},{data:'capacity',name:'capacity'},{data:'area',name:'area'},
{data:'source_sts_text',name:'source_sts_text',orderable:false,searchable:false},{data:'source_wards_text',name:'source_wards_text',orderable:false,searchable:false},
{data:'segregation_practiced',name:'segregation_practiced'},{data:'reuse_practiced',name:'reuse_practiced'},{data:'waste_types',name:'waste_types',orderable:false,searchable:false},{data:'monthly_waste_for_composting',name:'monthly_waste_for_composting'},{data:'treatment',name:'treatment'},{data:'operational_status',name:'operational_status'},
{data:'action',name:'action',orderable:false,searchable:false}
],
order:[[0,'asc']]
}).on('draw',function(){$('.delete').on('click',function(){var f=$(this).closest('form');event.preventDefault();
Swal.fire({title:'{{ __("Are you sure?") }}',text:"{!! __("You won\'t be able to revert this!") !!}",icon:'warning',showCancelButton:true,confirmButtonText:'{{ __("Yes, delete it!") }}',cancelButtonText:'{{ __("Cancel") }}'}).then(function(r){if(r.isConfirmed)f.submit();});});});
resetDataTable(dt);
$('#filter-form').on('submit',function(e){e.preventDefault();dt.draw();});
$('#export').on('click',function(e){e.preventDefault();
window.location.href="{!! route('swm.landfills.export') !!}?searchData="+encodeURIComponent($('input[type=search]').val()||'')+
"&landfill_id="+encodeURIComponent($('#landfill_id').val())+"&name="+encodeURIComponent($('#name').val())+
"&operator_name="+encodeURIComponent($('#operator_name').val())+"&contact_number="+encodeURIComponent($('#contact_number').val())+
"&source_sts_id="+encodeURIComponent($('#source_sts_id').val())+"&waste_type_id="+encodeURIComponent($('#waste_type_id').val())+
"&operational_status="+encodeURIComponent($('#operational_status').val())+"&segregation_practiced="+encodeURIComponent($('#segregation_practiced').val())+
"&reuse_practiced="+encodeURIComponent($('#reuse_practiced').val())+"&treatment="+encodeURIComponent($('#treatment').val());
});
});
</script>
@endpush
