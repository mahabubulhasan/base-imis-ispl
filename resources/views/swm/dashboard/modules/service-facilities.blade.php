@php
    $moduleId = 'service-facilities';
    $title = $module['label'] ?? __('Service Facilities');
@endphp

<section class="dash-section" id="sec-{{ $moduleId }}">
    <div class="section-banner swm-module-toggle" role="button" tabindex="0" aria-expanded="true">
        <span>{{ $title }}</span>
        <i class="fas fa-chevron-up section-chevron"></i>
    </div>
    <div class="section-content">
        @include('swm.dashboard.components.metrics-body', ['module' => $module])
    </div>
</section>
