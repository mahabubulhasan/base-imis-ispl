@extends('layouts.dashboard')
@section('title', $page_title)
@push('style')
<style type="text/css">
.dataTables_filter {
    display: none;
}
</style>
@endpush
@section('content')
<div class="card">
    <div class="card-header">
    @can('Import SWM Service Payment Collection From CSV')
      <a href="{{ route('swm-payment.create') }}" class="btn btn-info">{{__('Import from CSV')}}</a>
    @endcan
    @can('Export SWM Service Payment Collection From CSV')
    <a href="/templates/swmservice-payment-collection-iss-template.csv" download="Solid Waste Information Support System-Template.csv" class="btn btn-info">{{__('Download CSV Template')}}</a>
    @endcan
    @can('Export SWM Service Payment Collection From CSV')
      <a href="{{ route('swm-payment.export') }}" id="export" class="btn btn-info">{{__('Export to CSV')}} </a>
      <a href="{{ route('tax-payment.exportunmatched') }}" id="exportunmatched" class="btn btn-info">{{__('Export Unmatched Records')}}</a>
      @endcan
      <a href="#" class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
        {{__('Show Filter')}}
      </a>
    </div><!-- /.box-header -->
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <form class="form-horizontal" id="filter-form">
                                    <div class="form-group row">
                                        <label for="code" class="control-label col-md-2 ">
                                             {{__('Ward')}}</label>
                                        <div class="col-md-2" >
                                        <select class="form-control" id="ward_select">
                                        <option value=""> {{__('Ward')}}</option>
                                        @foreach($wards as $key=>$value)
                                        <option value="{{$key}}">{{$value}}</option>
                                        @endforeach
                                      </select>
                                        </div>
                                        <label for="road_hier_select" class="control-label col-md-2"
                                            >{{__('Years Due')}}</label>
                                        <div class="col-md-2" >
                                        <select class="form-control" id="dueyear_select">
                                        <option value="">{{__('Years Due')}}</option>
                                          @foreach($dueYears as $key=>$value)
                                          <option value="{{$key}}">{{$value}}</option>
                                          @endforeach
                                    </select>
                                        </div>

                                        <label for="swm_customer_id" class="control-label col-md-2" >{{__('SWM Customer ID')}}</label>
                                            <div class="col-md-2" >
                                                <input type="text" class="form-control" id="swm_customer_id"
                                                    placeholder="{{__('SWM Customer ID')}}" />
                                            </div> 
                                       
                                    </div>

                                    <div class="form-group row">
                                         <label for="bin" class="control-label col-md-2" >{{__('BIN')}}</label>
                                            <div class="col-md-2" >
                                                <input type="text" class="form-control" id="bin"
                                                    placeholder="{{__('BIN')}}" 
                                                    oninput = "this.value = this.value.replace(/[^a-zA-Z0-9]/g, ''); "/> <!-- Allow only alphabetic and numeric characters -->
                                            </div>
                                         <label for="tax_code" class="control-label col-md-2" >{{__('Tax Code')}}</label>
                                            <div class="col-md-2" >
                                                <input type="text" class="form-control" id="tax_code"
                                                    placeholder="{{__('Tax Code')}}" 
                                                    oninput = "this.value = this.value.replace(/[^a-zA-Z0-9-]/g, ''); "/> <!-- Allow only alphabetic characters, numbers, and the hyphen (-) -->
                                            </div>      
                                    </div>
                                    <div class="card-footer text-right">
                                        <button type="submit" class="btn btn-info ">{{__('Filter')}}</button>
                                        <button id="reset-filter" type="reset" class="btn btn-info">{{__('Reset')}}</button>
                                    </div>
                                </form>
                            </div>
                            <!--- accordion body!-->
                        </div>
                        <!--- collapseOne!-->
                    </div>
                    <!--- accordion item!-->
                </div>
                <!--- accordion !-->
            </div>
        </div>
        <!--- row !-->
    </div>
    <!--- card body !-->

    <div class="card-body">
        <div style="overflow: auto; width: 100%;">
            <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
                <thead>
                    <tr>
                    <th>{{__('SWM Customer ID')}}</th>
                    <th>{{__('BIN')}}</th>
                    <th>{{__('Tax Code')}}</th>
                    <th>{{__('Customer Name')}}</th>
                    <th>{{__('Years Due')}}</th>
                    <th>{{__('Ward')}}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div><!-- /.box-body -->
</div><!-- /.box -->
@stop

@push('scripts')
<script>
$(function() {
    var dataTable = $('#data-table').DataTable({
        bFilter: false,
        processing: true,
        serverSide: true,
        scrollCollapse: true,
        "bStateSave": true,
        "stateDuration": 1800, // In seconds; keep state for half an hour

        ajax: {
          url: '{!! url("swm-payment/data") !!}',
            data: function(d) {
            d.ward_select = $('#ward_select').val();
            d.dueyear_select = $('#dueyear_select').val();
            d.swm_customer_id = $('#swm_customer_id').val();
            d.bin = $('#bin').val();
            d.tax_code = $('#tax_code').val();
            }
        },
        columns: [
            { data: 'swm_customer_id', name: 'swm_customer_id' },
            { data: 'bin', name: 'bin' },
            { data: 'tax_code', name: 'tax_code' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'name', name: 'name' },
            { data: 'ward', name: 'ward' }
        ]
    }).on('draw', function() {
      $('.delete').on('click', function(e) {

      var form =  $(this).closest("form");
      event.preventDefault();
      swal({
          title: '{{__(`Are you sure you want to delete this record?`)}}',
          text: '{{__("If you delete this, it will be gone forever.")}}',
          icon: "warning",
          buttons: true,
          dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
          form.submit();
        }
      })
      });
    });

    var ward_select = '', dueyear_select = '', swm_customer_id = '';


    $('#filter-form').on('submit', function(e) {

        e.preventDefault();
        dataTable.draw();
        ward_select = $('#ward_select').val();
        dueyear_select = $('#dueyear_select').val();
        swm_customer_id = $('#swm_customer_id').val();
        bin = $('#bin').val();
        tax_code = $('#tax_code').val();
    });
    filterDataTable(dataTable);
    resetDataTable(dataTable);
    //  $('#data-table_filter input[type=search]').attr('readonly', 'readonly');

    $("#export").on("click", function(e) {
        e.preventDefault();
        var searchData = $('input[type=search]').val();
        var  ward_select = $('#ward_select').val();
        var dueyear_select = $('#dueyear_select').val();
        var swm_customer_id = $('#swm_customer_id').val();
        var tax_code = $('#tax_code').val();
        var bin = $('#bin').val();
        window.location.href = "{!! url('swm-payment/export?searchData=') !!}" + searchData + 
        "&ward=" + ward_select + 
        "&due_year=" + dueyear_select + 
        "&swm_customer_id=" + swm_customer_id +
        "&bin=" + bin  +
        "&tax_code=" + tax_code;
    });

    $("#exportunmatched").on("click", function(e) {
        e.preventDefault();
        window.location.href = "{!! url('swm-payment/exportunmatched') !!}";
    });



});
</script>

@endpush
