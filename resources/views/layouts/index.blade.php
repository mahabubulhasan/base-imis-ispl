<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
{{--
A Layout for the index page of every page
--}}
<div class="card border-0">
    <div class="card-header">
        @if(!empty($createBtnLink) && !empty($createBtnTitle))<a href="{{ $createBtnLink }}" class="btn btn-info">{{ $createBtnTitle }}</a>@endif
        @if(!empty($exportBtnLink))<a href="{{ $exportBtnLink }}" class="btn btn-info" id="export" onclick="exportToCsv(event)">{{ __('Export to CSV') }}</a>@endif
        @if(!empty($reportBtnLink))
        <a  class="btn btn-info" data-toggle="collapse" data-target="#collapseFilterPdf" aria-expanded="false" aria-controls="collapseFilterPdf">{{ __('Generate Report') }}</a>
        <div class="card-body">
            <div class="col-12">
                <div id="collapseFilterPdf" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <div class="form-group row">
                            <label for="bin_text" class="control-label col-md-2">Month</label>
                            <div class="col-md-2">
                                <select class="form-control row" id="month_select" name="month">
                                <?php
                                        $pickdate ="select distinct extract(month from application_date) as date1 from fsm.applications";
                                        $pickdateResults = DB::select($pickdate);
                                        foreach($pickdateResults as $unique)
                                        {
                                        ?> <option value= "<?php echo $unique->date1 ?>" > <?php echo $unique->date1 ?></option>
                                        <?php  }
                                        ?>
                                </select>
                            </div>
                            <label for="bin_text" class="control-label col-md-2">Year</label>
                            <div class="col-md-2">
                                <select class="form-control row" id="year_select" name="year">
                                <?php
                                        $pickdate ="select distinct extract(year from application_date) as date1 from fsm.applications";
                                        $pickdateResults = DB::select($pickdate);
                                        foreach($pickdateResults as $unique) {
                                            ?> <option value= "<?php echo $unique->date1 ?>" > <?php echo $unique->date1 ?></option>
                                        <?php }
                                    ?>
                                </select>
                            </div>
                                <a class="btn btn-info pdf" id="pdf" >{{ __('Export to PDF') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            @endif

        <a class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
        {{ __('Show Filter') }}
        </a>
    </div><!-- /.card-header -->
    <div class="card-body">
        <div class="row">
            <div class="accordion" id="filterAccordion">
                <div class="accordion-item">
                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#filterAccordion">
                        <div class="accordion-body">
                            <form class="form-horizontal" id="filter-form">
                                @yield('filter-form')
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-info">{{ __('Filter') }}</button>
                                    <button id="reset-filter" class="btn btn-info">{{ __('Reset') }}</button>
                                </div>
                            </form>
                        </div>  <!--- accordion body!-->
                    </div>    <!--- collapseOne!-->
                </div>      <!--- accordion item!-->
            </div>        <!--- accordion !-->
        </div>            <!--- row !-->
    </div>              <!--- card body !-->
    <div class="card-body">
        @yield('data-table')
    </div><!-- /.card-body -->
</div> <!-- /.card -->
