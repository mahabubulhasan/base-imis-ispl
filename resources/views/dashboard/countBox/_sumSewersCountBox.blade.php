<!-- small box -->
<div class="info-box ">
    <span class="info-box-icon bg-info">
        <img src="{{ asset('img/svg/imis-icons/sewers.svg') }}" alt="Total Length Of Sewers Icon">
      </span>
    <div class="info-box-content">
        <span class="info-box-text">
            <h2> {{ number_format($sumSewers) }}</h2>
        </span>

        <span class="info-box-number">{{ __('Total length of Sewer (m)')}}  </span>

    </div>
</div>
