@extends('layouts.dashboard')
@push('style')
<style type="text/css">
.dataTables_filter {
  display: none;
}
</style>
@endpush
@section('title', $page_title)
@section('content')
<div class="row">
    <div class="col-xs-12">
    <div class="card">
        <div class="card-header">
          @can('Add Building')
          <a href="{{ action('Fsm\CtptController@listBuildings', ['id' => $toilet->id]) }}" class="btn btn-info">{{ __('Back to the added Buildings list')}}</a>
          @endcan
        </div><!-- /.card-header -->
        <div class="card-body">
          <div class="list-wrapper">
            <h4><b>{{ __('Selected Buildings')}}</b></h4>
            <ul class="selected-list">
            </ul>
            @include('errors.list')
            {!! Form::open(['method' => 'PATCH', 'action' => ['Fsm\CtptController@saveBuildings', $toilet->id], 'class' => 'form-horizontal', 'id' => 'save-buildings-form']) !!}
              {!! Form::submit('Submit', ['class' => 'btn btn-info']) !!}
            {!! Form::close() !!}
          </div>
          <hr>
          <h4><b>{{ __('Search to Select Buildings')}}</b></h4>
          <form class="form-inline" id="filter-form">
            <div class="form-group">
              <label for="bin_text">{{ __('House Number')}}</label>
              <input type="text" class="form-control" id="bin_text" />
              <label for="holding_num">{{ __('Tax Code')}}</label>
              <input type="text" class="form-control" id="holding_num" />
            </div>
            <button type="submit" class="btn btn-default">{{ __('Filter') }}</button>
          </form>
          <table id="data-table" class="table table-bordered table-striped" width="100%">
            <thead>
              <tr>
                <th>{{__('House Number')}}</th>
                <th>{{__('Tax Code')}}</th>
                <th>{{__('Structure Type')}}</th>
                <th>{{__('Estimated Area of the Building')}}</th>
                <th>{{__('House Number of Main Building')}}</strong></h1></th>
                <th>{{__('Actions')}}</th>
              </tr>
            </thead>
          </table>
        </div><!-- /.card-body -->
      </div><!-- /.box -->
    </div><!-- /.col -->
  </div><!-- /.row -->

@stop

@push('scripts')
<script>
$(function() {
    var dataTable = $('#data-table').DataTable({
        processing: true,
        bFilter: false,
        serverSide: true,
        pageLength: 5,
        ajax: {
          url: '{!! url("fsm/ctpt/buildings/data") !!}',
          data: function(d) {
            d.bin = $('#bin_text').val();
            d.holding_num = $('#holding_num').val();
          }
        },
        columns: [
            { data: 'bin', name: 'bin' },
            { data: 'taxcd', name: 'taxcd' },
            { data: 'structype', name: 'structype' },
            { data: 'bldgarea', name: 'bldgarea' },
            { data: 'bldgasc', name: 'bldgasc' },
            { data: 'action', defaultContent: '<button title="Add Building" class="btn btn-info btn-xs">Add</button>', orderable: false, searchable: false}
        ]
    });

    $('#filter-form').on('submit', function(e){
        var binB = $('#bin_text').val();
        binB = binB.trim();
        var validB = /^B\d+$/gi.test(binB);
        if(binB != ''){
        if(!validB || (binB.length != 7)){
            swal({
                title: `{{__('House Number should be in BXXXXXX format!')}}`,
                icon: "warning",
                button: "{{__('Close')}}",
                className: "custom-swal",
            });
            return false;
            }
        }
        var taxcdT = $('#holding_num').val();
        var validT = /^\d{2}-\d{3}-\d{4}-\d{2}$/.test(taxcdT);
        if (!validT && (taxcdT != '')){ 
            swal({
                title: `{{ __('Tax Code should be in XX-XXX-XXXX-XX format!')}}`,
                icon: "warning",
                button: "{{ __('Close')}}",
                className: "custom-swal",
            })
            return false;
        }
      e.preventDefault();
      dataTable.draw();
    });

    var selectedData = [];

    $('#data-table tbody').on( 'click', 'button', function () {
        var clickedData = dataTable.row( $(this).parents('tr') ).data();
        var bin = clickedData['bin'];
        var checkBin = $('ul.selected-list li .bin').filter(function() {
                return $(this).text() == bin;
            }).length;
        if ( checkBin == 0 ) {
            $('ul.selected-list').append('<li><span class="bin">'+ bin +'</span> <a href="#"><i class="fa fa-close"></i></a></li>');
        }
    } );

    $('ul.selected-list').on( 'click', 'li a' , function(e) {
        e.preventDefault();
        $(this).closest('li').remove();
    });

    $('#save-buildings-form').on( 'submit', function() {
        var form = $(this);
        form.find('input[name="bin[]"]').remove();
        $('.selected-list li span.bin').each(function(){
          form.append('<input type="hidden" name="bin[]" value="' + $(this).text() + '" />');
        });
    })
});
</script>
@endpush