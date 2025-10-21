{{-- <!-- small box -->
<div class="info-box">
  <span class="info-box-icon bg-info"><i class="fa-solid fa-industry"></i></span>
    <div class="info-box-content">
      <span class="info-box-text"> <h3> {{  number_format($industrialBuildingCount)}}</h3></span>
      <span class="info-box-number">Industrial</span>
    </div>
</div> --}}


<!-- small box -->
<div class="info-box">
    <span class="info-box-icon bg-info">
      <img src="{{ asset('img/svg/imis-icons/industrial_building.svg') }}" alt="Industrial Icon">
    </span>
    <div class="info-box-content">
      <span class="info-box-text"> <h3>{{ number_format($industrialBuildingCount) }}</h3></span>
      <span class="info-box-number">{{__('Industrial') }}</span>
    </div>
  </div>
