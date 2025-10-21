@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')

@can('Building CountBox')

<h1 style="padding-bottom: 15px;font-size: 24px;">{{  __("Buildings")}}</h1>
  <div class="row">

        <div class="col-lg-3 col-md-12 col-xs-12  d-flex">
            @include('dashboard.countBox._buildCountBox')
        </div> <!-- main col div -->
        <div class="col-lg-9 col-md-12 col-xs-12  extra-padding">
            <div class="row">
                <div class="col-lg-4 d-flex">
                    @include('dashboard.countBox._residentialBuildCountBox')
                </div> <!--sub col div -->
                <div class="col-lg-4  d-flex">
                    @include('dashboard.countBox._commercialBuildCountBox')
                </div> <!--sub col div -->
                <div class="col-lg-4  d-flex">
                    @include('dashboard.countBox._industrialBuildCountBox')
                </div> <!--sub col div -->
            </div> <!-- sub row -->
            <div class="row">
                <div class="col-lg-4  d-flex">
                    @include('dashboard.countBox._mixedBuildCountBox')
                </div> <!--sub col div -->
                <div class="col-lg-4 d-flex ">
                    @include('dashboard.countBox._institutionCountBox')
                </div>
                <div class="col-lg-4 d-flex ">
                    @include('dashboard.countBox._educationBuildCountBox')
                </div>

            </div> <!--sub row -->


            <div class="row">

                <div class="col-lg-4 d-flex ">
                    @include('dashboard.countBox._othersBuildCountBox')
                </div>

            </div>

        </div> <!-- col div -->
  </div>
  @endcan

  @can('Sanitation CountBox')
  <h1 style="padding: 15px 0 15px 0; font-size: 24px;">{{ __("Sanitation Systems") }}</h1>
  <div class="row">
      @foreach ($sanitationSystems as $sanitationSystem)
          <div class="col-lg-3 col-xs-6">
              <div class="info-box sanitation-system-info">
                  <span class="info-box-icon bg-info">
                      @if (
                          $sanitationSystem->icon_name &&
                              $sanitationSystem->icon_name != 'no_icon' &&
                              $sanitationSystem->icon_name != 'others.svg')
                          <img src="{{ asset('img/svg/imis-icons/' . $sanitationSystem->icon_name) }}"
                              alt="{{ __($sanitationSystem->sanitation_system) }}">
                      @else
                          <i class="fa fa-building" aria-hidden="true" title="{{ __('Building') }}"></i>
                      @endif
                  </span>
                  <div class="info-box-content">
                      <span class="info-box-text">
                          <h3>{{ number_format($sanitationSystem->bin_count) }}</h3>
                      </span>
                      <span class="info-box-number">{{ __($sanitationSystem->sanitation_system) }}</span>
                  </div>
              </div>
          </div> <!-- sub col div -->
      @endforeach

      <div class="col-lg-3 col-xs-6">
          @include('dashboard.countBox._sanitationOffsiteContainmentCountBox')
      </div> <!-- sub col div -->
  </div> <!-- row div -->
@endcan



<div class="row">
    @can('Ward-Wise Distribution of Buildings Chart')
    <div class="col-md-6">
    @include('dashboard.buildings._buildingsPerWardChart')
    </div>
    @endcan
    @can('Building Use Composition Chart')
    <div class="col-md-6">
    @include('dashboard.buildings._buildingUseChart')
    </div>
    @endcan
</div>

@stop

@push('scripts')
<script>
$('[id="year_select"]').change(function(e) {
        // e.preventDefault();
      var year_select = $(this).val();
      localStorage.setItem('year_select', year_select);
    })
  </script>
  <script>
	$(document).ready(function() {
		year_sel = localStorage.getItem('year_select');
      if(year_sel){
      $("#year_select").val(year_sel);
      }
	})
        $(function () {
$('[data-toggle="tooltip"]').tooltip({
            html: true
        });
});
</script>
@endpush
