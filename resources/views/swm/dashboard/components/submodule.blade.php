<div class="panel-block" id="swm-sub-{{ $submodule['key'] ?? '' }}">
    <h2 class="panel-title">
        <span class="panel-title-text"><i class="fas fa-folder-open"></i> {{ $submodule['title'] ?? '' }}</span>
    </h2>
    <div class="panel-content">
        @foreach($submodule['blocks'] ?? [] as $block)
            <div class="swm-metric-block swm-metric-block--{{ $block['type'] ?? 'unknown' }}">
                @if(!empty($block['subsection']))
                    <h4 class="subsection-title">
                        <i class="fas {{ ($block['type'] ?? '') === 'kpis' ? 'fa-bullseye' : 'fa-chart-pie' }}"></i>
                        {{ $block['subsection'] }}
                    </h4>
                @endif
                @if(($block['type'] ?? '') === 'tiles')
                    <div class="swm-tiles-row">
                        @foreach($block['items'] ?? [] as $tile)
                            @include('swm.dashboard.components.tile', $tile)
                        @endforeach
                    </div>
                @elseif(($block['type'] ?? '') === 'kpis')
                    <div class="swm-tiles-row">
                        @foreach($block['items'] ?? [] as $kpi)
                            @include('swm.dashboard.components.kpi', $kpi)
                        @endforeach
                    </div>
                @elseif(($block['type'] ?? '') === 'charts')
                    <div class="row swm-charts-row">
                        @foreach($block['items'] ?? [] as $chart)
                            @include('swm.dashboard.components.chart-card', ['chart' => $chart])
                        @endforeach
                    </div>
                @elseif(($block['type'] ?? '') === 'table')
                    @include('swm.dashboard.components.metric-table', [
                        'title' => $block['title'] ?? null,
                        'columns' => $block['columns'] ?? [],
                        'rows' => $block['rows'] ?? [],
                    ])
                @endif
            </div>
        @endforeach
    </div>
</div>
