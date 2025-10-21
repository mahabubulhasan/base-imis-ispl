<!-- small box -->
<div class="info-box ">


        <span class="info-box-icon bg-info">
            <img src="{{ asset('img/svg/imis-icons/roads.svg') }}" alt="Total Length Of Roads Icon">
          </span>

    <div class="info-box-content">
        <span class="info-box-text">
            <h2> {{ number_format($sumRoads) }}</h2>
        </span>
        <span class="info-box-number"> {{ __("Total length of Road (m)") }}</span>

    </div>
</div>
