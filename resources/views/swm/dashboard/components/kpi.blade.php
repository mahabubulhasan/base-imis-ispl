<div class="swm-tile-col swm-col-quarter d-flex">
    <div class="kpi-box">
        <div class="kpi-header">
            <span class="kpi-tag">KPI</span>
            {{-- @if($showFrequency ?? true)
                <span class="kpi-freq">{{ __('Monthly') }}</span>
            @endif --}}
        </div>
        <div class="kpi-name">{{ $name }}</div>
        <div class="kpi-value-row">
            <span class="kpi-value">{{ $value }}</span>
            @if(empty($hideUnit) && !empty($unit))
                <span class="kpi-unit">{{ $unit }}</span>
            @endif
        </div>
    </div>
</div>
