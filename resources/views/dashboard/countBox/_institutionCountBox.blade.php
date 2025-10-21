<div class="info-box">
    <span class="info-box-icon bg-info">
        <img src="{{ asset('img/svg/imis-icons/institutions.svg') }}" alt="Residential Icon">
    </span>
    <div class="info-box-content">
        <span class="info-box-text">
            <h3>{{ number_format($institutionBuildingCount) }}</h3>
        </span>
        <span class="info-box-number">{{__('Institution') }}</span>
    </div>
    <!-- Top-right icon with tooltip -->
    <span class="top-right-icon" data-tooltip="Culture & Religious<br>Agricultural & Farm">
        <i class="fa-solid fa-circle-info"></i>
        <!-- Display institution names with line breaks -->
        {{-- <div class="custom-tooltip"> {{ __(!!$institutionNames!!) }}</div> --}}

        <div class="custom-tooltip">{{ __('Health Institution') }}<br>
            {{ __('Public Institution') }} <br>
             {{ __('Government Institution') }} <br>
             {{ __('Recreational Institution') }}  <br>
             {{ __('Social Institution') }}  <br>
              {{ __('Financial Institution') }}</div>
    </span>
</div>
