@php
    $colClass = ($chart['type'] ?? 'bar') === 'heatmap' ? 'col-md-12' : 'col-md-6';
    $height = $chart['height'] ?? 320;
@endphp
<div class="{{ $colClass }} mb-3">
    <div class="card card-outline card-info swm-chart-card">
        <div class="card-header">
            <h3 class="card-title">{{ $chart['title'] ?? '' }}</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
                @if(($chart['type'] ?? '') !== 'heatmap')
                <button type="button" class="btn btn-tool swm-export-chart" data-target="{{ $chart['id'] ?? '' }}"><i class="fa-solid fa-image"></i></button>
                @endif
            </div>
        </div>
        <div class="card-body collapse show">
            @if(($chart['type'] ?? '') === 'heatmap')
                <div id="{{ $chart['id'] ?? '' }}" class="heatmap-wrap swm-heatmap" data-heatmap='@json($chart)'></div>
            @else
                <div class="chart-wrap" style="height:{{ (int) $height }}px">
                    <canvas id="{{ $chart['id'] ?? '' }}" class="swm-chart-canvas" data-chart='@json($chart)'></canvas>
                </div>
            @endif
        </div>
    </div>
</div>
