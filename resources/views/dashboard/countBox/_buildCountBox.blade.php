<!-- small box -->
<div class="info-box ">
    <span class="info-box-icon bg-info">
        <img src="{{ asset('img/svg/imis-icons/totalBuildings.svg') }}" alt="Residential Icon">
      </span>
    <div class="info-box-content">
        <span class="info-box-text">
            <h2> {{ number_format($buildingCount) }}</h2>
        </span>
        <span class="info-box-number"> {{ __('Total Buildings')}}</span>
    </div>
</div>
