@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')


<!-- <div class="card"> -->
<div class="card-header" style="
    background-color: unset;
    border: unset;">
<button type="submit" id="report" class="ml-1 btn btn-info float-right">{{__('Generate Report')}}</button>
</div>
  <div class="card-body">
    <div class="row">
      <div class="col-12">
        <form class="form-horizontal" id="filter-form">
          <div class="form-group row">
            <label for="year_select" class="col-sm-2 col-form-label ">{{__('Year')}}</label>
            <div class="col-sm-4">
              <select class="form-control" id="year_select" name="year">
                <option value="">{{__('All Years')}}</option>
                @foreach($years as $key)
                <option value="{{$key}}" >{{$key}}</option>
                @endforeach
              </select>
            </div>
            @if(!Auth::user()->service_provider_id)
            <label for="service_provider_select" class="col-sm-2 col-form-label ">{{__('Service Provider Name')}}</label>
            <div class="col-sm-4">
              <select id="service_provider_select" class="form-control" name="service_provider">
                <option value="">{{__('Choose a Service Provider')}}</option>
                  @foreach($serviceProviders as  $key=>$value)
                  <option value="{{$key}}">{{$value}}</option>
                  @endforeach
              </select>
            </div> 
            @endif
          </div>           
          <div class=" text-right">
            <button type="submit" id = "filter" class="ml-1 btn btn-info">{{ __('Filter') }}</button>
            <a href="{{ action('Fsm\KpiDashboardController@index')}}" id="reset" class="ml-1 btn btn-info reset">{{__('Reset')}}</a> 
          </div> 
        </div>
        </form>
      </div>       
    </div>
  <!-- </div> -->
  
  <div id="loader" style="display: none;">
    Loading...
  </div>

  @can('KPI')
@if($year != '')
<div  id="content-div" class="row">
  <!-- Key performance data will be loaded here -->

    @foreach($keyPerformanceData[1] as $indicators)

        @include('layouts.dashboard.key-performance-indicator-card',compact('indicators'))
    @endforeach
    
</div>
@endif
@endcan

  <div class="row">
  @can('Application Response Chart')
    <div class="col-md-6">
      @include('fsm.kpi-dashboard.kpiCharts._applicationResponseEfficiencyChart')
    </div>
  @endcan
 @can('Customer Satisfaction Chart')
    <div class="col-md-6">
      @include('fsm.kpi-dashboard.kpiCharts._customerSatisfactionCharts')
    </div>
    @endcan
</div>
<div class="row">

    @can('PPE Compliance Chart')
    <div class="col-md-6">
      @include('fsm.kpi-dashboard.kpiCharts._ppeComplianceChartsCharts')
    </div>
    @endcan
    @can('Safe Desludging Chart')
    <div class="col-md-6">
      @include('fsm.kpi-dashboard.kpiCharts._safeDesludgingCharts')
    </div>
    @endcan
</div>  

<div class="row">

@can('Inclusion Chart')
    <div class="col-md-6">
      @include('fsm.kpi-dashboard.kpiCharts._inclusionCharts')
    </div>
    @endcan
    @can('Response Time Chart')
    <div class="col-md-6">
     @include('fsm.kpi-dashboard.kpiCharts._responseTimeCharts')
    </div> 
    @endcan
</div> 

@can('Faecal Sludge Collection Ratio Chart')
<div class="col-md-6">
      @include('fsm.kpi-dashboard.kpiCharts._fscrCharts')
    </div>
    @endcan
@stop

@push('scripts')
<script>
 $(document).ready(function() {
  checkURL();
    if ("{{ Auth::user()->service_provider_id }}") {
        var selectedOption = "{{ $company_name }}";
        localStorage.setItem('selectedOption', selectedOption);
    }

    $('#filter').on('click', function(e) {
       
        var year = $("#year_select").val();
        var selectedOption = $("#service_provider_select").val();
        var a= localStorage.setItem('year', year);
        var b = localStorage.setItem('selectedOption', selectedOption);
      
    });

    var year_sel = localStorage.getItem('year');
    if (year_sel) {
        $("#year_select").val(year_sel);
    }

    var service_provider_sel = localStorage.getItem('selectedOption');
    if (service_provider_sel) {
        $("#service_provider_select").val(service_provider_sel);
    }

    $('#reset').on('click', function() {
        localStorage.removeItem("selectedOption");
        localStorage.removeItem("year");
    });
    
    function removeLocalStorageItems() {
        localStorage.removeItem("selectedOption");
        localStorage.removeItem("year");
    }

    function checkURL() {
        var currentURL = window.location.href;
        if (currentURL.endsWith("fsm/kpi-dashboard")) {
            removeLocalStorageItems(); // Remove localStorage items if URL matches
        }
    }

});


$('[id="report"]').click(function(e) {
    e.preventDefault(); // Prevent the default behavior of the click event

    // Retrieve values from localStorage
    var year = localStorage.getItem('year');
    var serviceprovider = localStorage.getItem('selectedOption');

    const url = 'generate-report/' + (year ? year : null) + '/' +(serviceprovider ?  serviceprovider : null);

    window.open(url, "Monthly Report");
});



</script>
@endpush