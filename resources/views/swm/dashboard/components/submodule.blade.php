<div class="panel-block" id="swm-sub-{{ $submodule['key'] ?? '' }}">
    <h2 class="panel-title">
        <span class="panel-title-text"><i class="fas fa-folder-open"></i> {{ $submodule['title'] ?? '' }}</span>
    </h2>
    <div class="panel-content">
        @foreach($submodule['blocks'] ?? [] as $block)
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
                <div class="row">
                    @foreach($block['items'] ?? [] as $chart)
                        @include('swm.dashboard.components.chart-card', ['chart' => $chart])
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
</div>
