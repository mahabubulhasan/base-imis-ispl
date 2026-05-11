<!-- Last Modified: 2026-04-26
// Developed By: Streams Tech Ltd.
// Description: Public dashboard with infographic card layout for municipal data visualization, including CWIS equity and safety metrics. -->

@php
    $cwisEquityCards = [
        [
            'key' => 'eq1',
            'code' => 'EQ-1',
            'label' => 'Ratio of LIC access to total population access',
            'icon' => 'balance',
            'unit' => 'number',
            'valueClass' => 'text-5xl md:text-6xl',
        ],
    ];

    $cwisSafetyCards = [
        ['key' => 'sf1a', 'code' => 'SF-1a', 'label' => 'Population with access to safe individual toilets', 'icon' => 'shield'],
        ['key' => 'sf1b', 'code' => 'SF-1b', 'label' => 'Percentage of on-site sanitation that have been desludged', 'icon' => 'shield'],
        ['key' => 'sf1c', 'code' => 'SF-1c', 'label' => 'Percentage of collected FS disposed at a treatment plant or at designated disposal site', 'icon' => 'shield'],
        ['key' => 'sf1d', 'code' => 'SF-1d', 'label' => 'FS treatment capacity as a percentage of total FS generated from NSS connections (excluding safely disposed in situ)', 'icon' => 'shield'],
        ['key' => 'sf1e', 'code' => 'SF-1e', 'label' => 'FS treatment capacity as a percentage of total FS collected from NSS connections', 'icon' => 'shield'],
        ['key' => 'sf1f', 'code' => 'SF-1f', 'label' => 'Wastewater treatment capacity as a percentage of total wastewater generated from sewered connections and greywater generated from non-sewered connections', 'icon' => 'shield'],
        ['key' => 'sf1g', 'code' => 'SF-1g', 'label' => 'Percentage of wastewater flow from sewered households that is safely treated', 'icon' => 'shield'],
        ['key' => 'sf2a', 'code' => 'SF-2a', 'label' => 'Percentage LIC population with access to safe individual toilets', 'icon' => 'shield'],
        ['key' => 'sf2b', 'code' => 'SF-2b', 'label' => 'Percentage of LIC, NSS, IHHLs that have been desludged', 'icon' => 'shield'],
        ['key' => 'sf2c', 'code' => 'SF-2c', 'label' => 'Percentage of collected FS (collected from LIC) disposed at treatment plant or designated disposal sites', 'icon' => 'shield'],
        ['key' => 'sf3', 'code' => 'SF-3', 'label' => 'Percentage of dependent population (those without access to a private toilet/latrine) with access to safe shared facilities (CT/PT)', 'icon' => 'shield'],
        ['key' => 'sf3b', 'code' => 'SF-3b', 'label' => 'Percentage of CTs that adhere to principles of universal design', 'icon' => 'shield'],
        ['key' => 'sf3c', 'code' => 'SF-3c', 'label' => 'Percentage of users of CTs that are women', 'icon' => 'shield'],
        ['key' => 'sf3e', 'code' => 'SF-3e', 'label' => 'Percentage of PTs where handwashing stations are functional with soap and water available', 'icon' => 'shield'],
        ['key' => 'sf4a', 'code' => 'SF-4a', 'label' => 'Percentage of PTs where FS and WW generated is safely transported to TP or safely disposed in situ', 'icon' => 'shield'],
        ['key' => 'sf4b', 'code' => 'SF-4b', 'label' => 'Percentage of PTs that adhere to principles of universal design', 'icon' => 'shield'],
        ['key' => 'sf4d', 'code' => 'SF-4d', 'label' => 'Percentage of users of PTs that are women', 'icon' => 'shield'],
        ['key' => 'sf5', 'code' => 'SF-5', 'label' => 'Percentage of educational institutions where FS/WW generated is safely transported to TP or safely disposed in situ', 'icon' => 'shield'],
        ['key' => 'sf6', 'code' => 'SF-6', 'label' => 'Percentage of healthcare facilities where FS/WW generated is safely transported to TP or safely disposed in situ', 'icon' => 'shield'],
        ['key' => 'sf7', 'code' => 'SF-7', 'label' => 'Percentage of desludging services completed mechanically or semi-mechanically (by a gulper)', 'icon' => 'shield'],
        ['key' => 'sf9', 'code' => 'SF-9', 'label' => 'Percentage of tests which are in compliance with water quality standards for fecal coliform', 'icon' => 'shield'],
    ];
@endphp

<div id="dashboard" class="tab-content p-5 md:p-10 bg-white min-h-[calc(100vh-100px)] animate-fadeIn">
    <!-- Loading Spinner -->
    <div id="dashboard-loader" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-cyan-600 border-t-transparent"></div>
        <span class="ml-4 text-slate-600 text-lg">Loading Dashboard...</span>
    </div>

    <!-- Dashboard Content Container -->
    <div id="dashboard-content" class="max-w-7xl mx-auto">
        <!-- Buildings Overview Section -->
        <section class="py-5">
            <div class="flex items-end justify-between mb-6">
                <div>
                    <h3 class="text-2xl font-extrabold text-slate-800 flex items-center gap-2">
                        <span class="w-8 h-1 bg-cyan-600 rounded-full"></span>
                        Buildings Overview
                    </h3>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">apartment</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Total Buildings</p>
                    <h4 class="text-4xl font-black text-slate-900 building-total">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">home</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Residential</p>
                    <h4 class="text-4xl font-black text-slate-900 building-residential">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">storefront</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Commercial</p>
                    <h4 class="text-4xl font-black text-slate-900 building-commercial">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">factory</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Industrial</p>
                    <h4 class="text-4xl font-black text-slate-900 building-industrial">-</h4>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">holiday_village</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Mixed Use</p>
                    <h4 class="text-4xl font-black text-slate-900 building-mixed">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">account_balance</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Institution</p>
                    <h4 class="text-4xl font-black text-slate-900 building-institution">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">school</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Educational</p>
                    <h4 class="text-4xl font-black text-slate-900 building-educational">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">more_horiz</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Others</p>
                    <h4 class="text-4xl font-black text-slate-900 building-others">-</h4>
                </div>
            </div>
        </section>

        <!-- Sanitation Facilities Section -->
        <section class="py-5">
            <h3 class="text-2xl font-extrabold text-slate-800 mb-6 flex items-center gap-2">
                <span class="w-8 h-1 bg-cyan-600 rounded-full"></span>
                Building Sanitation Facilities
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">storage</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Septic Tanks</p>
                    <h4 class="text-4xl font-black text-slate-900 septic-count">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">hourglass_bottom</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Pit/Holding Tank</p>
                    <h4 class="text-4xl font-black text-slate-900 pit-count">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">more_horiz</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Others</p>
                    <h4 class="text-4xl font-black text-slate-900 sanitation-others-count">-</h4>
                </div>
            </div>
        </section>

        <!-- Public Utilities Section -->
        <section class="py-5">
            <h3 class="text-2xl font-extrabold text-slate-800 mb-6 flex items-center gap-2">
                <span class="w-8 h-1 bg-cyan-600 rounded-full"></span>
                Public Utilities
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">add_road</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Total Length of Road (m)</p>
                    <h4 class="text-4xl font-black text-slate-900"><span class="road-length">-</span></h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">plumbing</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Total Length of Drain (m)</p>
                    <h4 class="text-4xl font-black text-slate-900"><span class="drainage-length">-</span></h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">water_drop</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Total Length of Water Supply (m)</p>
                    <h4 class="text-4xl font-black text-slate-900"><span class="water-length">-</span></h4>
                </div>
            </div>
        </section>

        <!-- CWIS Dashboard -->
        <section class="py-5">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between mb-6">
                <div>
                    <h3 class="text-2xl font-extrabold text-slate-800 flex items-center gap-2">
                        <span class="w-8 h-1 bg-cyan-600 rounded-full"></span>
                        CWIS Dashboard
                    </h3>
                    <p class="text-sm text-slate-500 mt-2">Equity and Safety indicators from the latest available CWIS dataset.</p>
                </div>
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-cyan-100 bg-cyan-50 text-sm font-semibold text-cyan-800">
                    <span class="material-icons-round text-base">calendar_month</span>
                    Latest CWIS Year: <span class="cwis-year">-</span>
                </div>
            </div>

            <div class="mb-10">
                <h4 class="text-lg font-extrabold text-slate-800 mb-4">Equity</h4>
                <div class="grid grid-cols-1 gap-6">
                    @foreach ($cwisEquityCards as $card)
                        <div class="infographic-card relative overflow-hidden bg-white p-6 md:p-8 rounded-xl border border-slate-300 shadow-sm">
                            <div class="absolute inset-x-0 top-0 h-1 bg-cyan-600"></div>
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-black uppercase tracking-[0.24em] text-cyan-700">{{ $card['code'] }}</p>
                                    <p class="text-slate-700 font-bold text-base md:text-lg mt-3 max-w-3xl">{{ __($card['label']) }}</p>
                                </div>
                                <span class="material-icons-round stat-icon text-cyan-600">{{ $card['icon'] }}</span>
                            </div>
                            <div class="mt-8 flex items-end justify-between gap-4">
                                <h4 class="{{ $card['valueClass'] }} font-black text-slate-900 leading-none" data-cwis-group="equity" data-cwis-value="{{ $card['key'] }}" data-cwis-unit="{{ $card['unit'] }}">-</h4>
                                <span class="text-sm font-semibold text-slate-500 uppercase tracking-[0.2em]">Latest value</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <h4 class="text-lg font-extrabold text-slate-800 mb-4">Safety</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach ($cwisSafetyCards as $card)
                        <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm h-full">
                            <div class="absolute inset-x-0 top-0 h-1 bg-cyan-600/75"></div>
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-black uppercase tracking-[0.24em] text-cyan-700">{{ $card['code'] }}</p>
                                    <p class="text-slate-700 font-bold text-sm mt-3">{{ __($card['label']) }}</p>
                                </div>
                                <span class="material-icons-round stat-icon text-cyan-600">{{ $card['icon'] }}</span>
                            </div>
                            <h4 class="mt-8 text-3xl font-black text-slate-900 leading-none" data-cwis-group="safety" data-cwis-value="{{ $card['key'] }}" data-cwis-unit="percent">-</h4>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    <!-- Error Message Container -->
    <div id="dashboard-error" class="hidden max-w-7xl mx-auto bg-red-50 border border-red-200 p-4 rounded-lg text-red-700">
        Failed to load dashboard. Please try again later.
    </div>
</div>

@push('scripts')
<script>
  // Load Public Dashboard via AJAX
  function loadPublicDashboard() {
    const loader = document.getElementById('dashboard-loader');
    const error = document.getElementById('dashboard-error');

    // Show loader, hide error
    loader.classList.remove('hidden');
    error.classList.add('hidden');

    $.ajax({
      url: '{{ route("public-dashboard") }}',
      method: 'GET',
      dataType: 'json',
      success: function(data) {
        // Hide loader
        loader.classList.add('hidden');

        // Update Buildings section
                setMetric('.building-total', data.buildings?.total);
                setMetric('.building-residential', data.buildings?.residential);
                setMetric('.building-commercial', data.buildings?.commercial);
                setMetric('.building-industrial', data.buildings?.industrial);
                setMetric('.building-mixed', data.buildings?.mixed_use);
                setMetric('.building-institution', data.buildings?.institution);
                setMetric('.building-educational', data.buildings?.educational);
                setMetric('.building-others', data.buildings?.others);

        // Update Sanitation Facilities
                setMetric('.septic-count', data.sanitation?.septic);
                setMetric('.pit-count', data.sanitation?.pit);
                setMetric('.sanitation-others-count', data.sanitation?.others);

        // Update Public Utilities
                setMetric('.road-length', data.utilities?.road);
                setMetric('.drainage-length', data.utilities?.drainage);
                setMetric('.water-length', data.utilities?.water);

                // Update CWIS Dashboard
                updateCwisMetrics(data.cwis || {});
      },
      error: function() {
        // Hide loader
        loader.classList.add('hidden');

        // Show error
        error.classList.remove('hidden');
        console.error('Failed to load public dashboard');
      }
    });
  }

  function formatNumber(num) {
        const numericValue = Number(num || 0);
        return Math.round(numericValue).toLocaleString();
    }

    function setMetric(selector, value) {
        const element = document.querySelector(selector);

        if (!element) {
            return;
        }

        element.textContent = formatNumber(value);
    }

    function formatCwisValue(value, unit) {
        if (value === null || value === undefined || value === '') {
            return '-';
        }

        const numericValue = Number(value);

        if (Number.isNaN(numericValue)) {
            return '-';
        }

        const formattedValue = Number.isInteger(numericValue)
            ? numericValue.toLocaleString()
            : numericValue.toLocaleString(undefined, {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                });

        return unit === 'percent' ? formattedValue + '%' : formattedValue;
    }

    function updateCwisMetrics(cwis) {
        const yearElement = document.querySelector('.cwis-year');

        if (yearElement) {
            yearElement.textContent = cwis.year || '-';
        }

        document.querySelectorAll('[data-cwis-value]').forEach(function(element) {
            const group = element.dataset.cwisGroup;
            const key = element.dataset.cwisValue;
            const unit = element.dataset.cwisUnit || 'number';
            const value = cwis?.[group]?.[key]?.value ?? null;

            element.textContent = formatCwisValue(value, unit);
        });
  }
</script>
@endpush
