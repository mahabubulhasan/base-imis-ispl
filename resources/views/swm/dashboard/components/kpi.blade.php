<div class="swm-tile-col swm-col-quarter d-flex">
    <div class="kpi-box">
        <div class="kpi-value-row">
            <span class="kpi-value">{{ $value }}</span>
        </div>
        <div class="kpi-footer">
            <span class="kpi-name">{{ $name }}</span>
            @if(empty($hideUnit) && !empty($unit))
                <span class="kpi-unit">{{ $unit }}</span>
            @endif
        </div>
    </div>
</div>
