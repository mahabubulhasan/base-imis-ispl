<style type="text/css">
    .roles-selector {
        min-height: 150px !important;

    }

    .invalid-feedback {
        color: red;
    }

    tr.group,
    tr.group:hover {
        background-color: #ddd !important;
    }

    .group{
        /*font-size: 2.5rem;*/
        font-weight: bold;
    }
    div.dataTables_filter {
        text-align: right !important;
    }


</style>
<div class="card-body">
        @if(isset($role))
        <div class="form-group row">
            {{ Form::label('name', __('Name'), ['class' => 'col-sm-1 control-label']) }}
            <div class="col-sm-3">
                {{ Form::label('name', $role->name, ['class' => 'control-label']) }}
                                {{ Form::hidden('name', null, [ 'class' => 'form-control' . ($errors->has('name') ? ' is-invalid' : '') ])}}

            </div>
        </div>
        @else
        <div class="form-group row required">
            {{ Form::label('name', __('Name'), ['class' => 'col-sm-1 control-label']) }}
            <div class="col-sm-3" >
                {{ Form::text('name', null, [ 'class' => 'form-control' . ($errors->has('name') ? ' is-invalid' : ''),'placeholder' => __('Name')  ])}}
                @if($errors->has('name'))
                    <div class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </div>
                @endif
            </div>
        </div>
        @endif
</div>
<div class="form-group col-lg-12">
    <label class="control-label">{{ __('Permissions')}} </label>

    <table class="table table-bordered" id="permissions-table" style="width:100%">
        <thead>
        <tr>
            <th>{{ __('Permissions')}}</th>
            <th>Category</th>
        </tr>
        </thead>
            @foreach($grouped_permissions as $category=>$permissions)
                @foreach($permissions as $value)
                    <tr>
                        @if(isset($rolePermissions))
                            <td><label style="width: 100%">{{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, array('class'=>$value->id)) }}
                        @else
                            <td><label>{{ Form::checkbox('permission[]', $value->id, [], array('id' => $value->id,'class'=>'permission-select')) }}
                                    @endif
                                    {{ $value->name }}</label></td>
                            <td>{{$category}}</td>
                    </tr>
                @endforeach
            @endforeach
    </table>



    {{--USING ADMIN LTE BOXES
    @foreach($grouped_permissions->chunk(2) as $permission_group)
        <div class="row">
            @foreach($permission_group as $category=>$permissions)
                <div class="col-sm-6">
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h3 class="box-title">{{$category}}</h3>
                            <div class="box-tools pull-right">
                                <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                </button>
                            </div><!-- /.box-tools -->
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <table class="table table-bordered" id="{{str_replace(' ','-',$category)}}-table" style="width:100%">
                                <thead>
                                <tr>
                                    <th>Permission</th>
                                </tr>
                                </thead>
                            @foreach($permissions as $value)
                                <tr>
                                    @if(isset($rolePermissions))
                                        <td><label style="width: 100%">{{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, array('class'=>$value->id)) }}
                                    @else
                                        <td><label>{{ Form::checkbox('permission[]', $value->id, [], array('id' => $value->id,'class'=>'permission-select')) }}
                                    @endif
                                    {{ $value->name }}</label></td>
                                </tr>
                            @endforeach
                            </table>
                        </div><!-- /.box-body -->
                    </div><!-- /.box -->
                </div>
            @endforeach
        </div>
    @endforeach--}}

    {{--OLD UI--}}
    {{--@foreach($permission as $value)
    <div class="col-sm-3">
        @if(isset($rolePermissions))
        {{ Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions) ? true : false, array('class' => 'name', 'id' => $value->id)) }}
        @else
        {{ Form::checkbox('permission[]', $value->id, [], array('class' => 'name', 'id' => $value->id)) }}
        @endif
        <label for="{{ $value->id }}">{{ $value->name }}</label>

    </div>

    @endforeach--}}
</div>
</div>


@push('scripts')
{{--<script>
    $(document).ready(function() {
        var groupColumn = 1;
        var table = $("#permissions-table").DataTable(
            {
                "paging":false,
                "columnDefs": [
                    {"visible": false, "targets": groupColumn}
                ],
                "order": [[groupColumn, 'asc']],
                "rowGroup" : {
                    dataSrc: groupColumn,
                }
            });
        table.$('input[type=checkbox]').each(function () {
            // If checkbox is checked
        });
        $('input[type = checkbox]').change(function () {
           $("."+this.className).prop('checked', $(this).prop('checked'));
        });
        // Order by the grouping
    } );
</script>--}}
<script>
    $(document).ready(function() {
        var groupColumn = 1;
        var table = $("#permissions-table").DataTable(
            {
                "paging":false,
                "columnDefs": [
                    {"visible": false, "targets": groupColumn}
                ],
                "order": [[groupColumn, 'asc']],
                "drawCallback": function (settings) {
                    var api = this.api();
                    var rows = api.rows({page: 'current'}).nodes();
                    var last = null;

                    api.column(groupColumn, {page: 'current'}).data().each(function (group, i) {
                        if (last !== group) {
                            $(rows).eq(i).before(
                                '<tr class="group"><td colspan="5">' + group + '</td></tr>'
                            );

                            last = group;
                        }
                    });
                }
            });
        $('.form-horizontal').on('submit', function(e) {
            var $form = $(this);
            table.rows().nodes().to$().find('input[type="checkbox"]').each(function () {
                if (!$.contains(document, this)) {
                    // If checkbox is checked
                    if (this.checked) {
                        // Create a hidden element
                        $form.append(
                            $('<input>')
                                .attr('type', 'hidden')
                                .attr('name', this.name)
                                .val(this.value)
                        );
                    }
                }
            });
        });

        $('input[type = checkbox]').change(function () {
           $("."+this.className).prop('checked', $(this).prop('checked'));
        });
        // Order by the grouping
        $('#permissions-table tbody').on( 'click', 'tr.group', function () {
            var currentOrder = table.order()[0];
            if ( currentOrder[0] === groupColumn && currentOrder[1] === 'asc' ) {
                table.order( [ groupColumn, 'desc' ] ).draw();
            }
            else {
                table.order( [ groupColumn, 'asc' ] ).draw();
            }
        } );
    } );
</script>

@endpush

