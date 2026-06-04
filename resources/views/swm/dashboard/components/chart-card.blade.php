@php
    $fullWidthTypes = ['heatmap', 'stackedBar', 'stackedArea', 'network'];
    $isFullWidth = array_key_exists('fullWidth', $chart)
        ? ! empty($chart['fullWidth'])
        : (! empty($chart['fullWidth']) || in_array($chart['type'] ?? '', $fullWidthTypes, true));
    $colClass = $isFullWidth ? 'col-md-12' : 'col-md-6';
    $chartType = $chart['type'] ?? '';
    $isDoughnut = $chartType === 'doughnut';
    $height = $chart['height'] ?? ($isDoughnut ? 380 : 320);
@endphp
<div class="{{ $colClass }} mb-2">
    <div class="card card-outline card-info swm-chart-card{{ $isDoughnut ? ' swm-chart-card--doughnut' : '' }}">
        <div class="card-header">
            <h3 class="card-title">{{ $chart['title'] ?? '' }}</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i></button>
                @if(! in_array($chartType, ['heatmap', 'network'], true))
                <button type="button" class="btn btn-tool swm-export-chart" data-target="{{ $chart['id'] ?? '' }}"><i class="fa-solid fa-image"></i></button>
                @endif
            </div>
        </div>
        <div class="card-body collapse show">
            @if($chartType === 'heatmap')
                <div id="{{ $chart['id'] ?? '' }}" class="heatmap-wrap swm-heatmap" data-heatmap='@json($chart)'></div>
            @elseif($chartType === 'network')
                <div id="{{ $chart['id'] ?? '' }}" class="swm-network" data-network='@json($chart)' style="height:{{ (int) ($chart['height'] ?? 400) }}px"></div>
            @else
                <div class="chart-wrap{{ $isDoughnut ? ' chart-wrap--doughnut' : '' }}" style="height:{{ (int) $height }}px">
                    <canvas id="{{ $chart['id'] ?? '' }}" class="swm-chart-canvas" data-chart='@json($chart)'></canvas>
                </div>
            @endif
        </div>
    </div>
</div>
