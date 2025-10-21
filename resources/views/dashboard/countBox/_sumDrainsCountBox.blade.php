<!-- small box -->
<div class="info-box ">
    <span class="info-box-icon bg-info">
        <img src="{{ asset('img/svg/imis-icons/drains.svg') }}" alt="Total Length Of Drains Icon">
      </span>
    <div class="info-box-content">
        <span class="info-box-text">
            <h2> {{ number_format($sumDrains) }}</h2>
        </span>

        <span class="info-box-number">{{__("Total length of Drain (m)") }}</span>

    </div>
</div>
