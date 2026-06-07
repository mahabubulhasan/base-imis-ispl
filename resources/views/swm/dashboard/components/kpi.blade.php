<div class="swm-tile-col swm-col-quarter d-flex">
    <div class="kpi-box">
        <div class="kpi-value-row">
            <span class="kpi-value">{{ $value }}</span>
        </div>
        <div class="kpi-footer">
            <span class="kpi-name">
                {{ $name }}@if(empty($hideUnit) && !empty($unit)) ({{ $unit }})@endif
            </span>
        </div>
    </div>
</div>
