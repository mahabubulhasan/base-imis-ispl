<!-- Last Modified: 2026-03-18
// Developed By: Streams Tech Ltd.
// Description: Public Dashboard with infographic card layout for municipal data visualization -->

<div id="dashboard" class="tab-content p-5 md:p-10 bg-background-light min-h-[calc(100vh-100px)] animate-fadeIn">
    <!-- Loading Spinner -->
    <div id="dashboard-loader" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div>
        <span class="ml-4 text-slate-600 text-lg">Loading Dashboard...</span>
    </div>

    <!-- Dashboard Content Container -->
    <div id="dashboard-content" class="max-w-7xl mx-auto space-y-12">
        <!-- Buildings Overview Section -->
        <section>
            <div class="flex items-end justify-between mb-6">
                <div>
                    <h3 class="text-2xl font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-8 h-1 bg-primary rounded-full"></span>
                        Buildings Overview
                    </h3>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="infographic-card relative overflow-hidden bg-blue-50 p-6 rounded-xl border border-blue-100">
                    <span class="material-icons-round stat-icon text-blue-400">apartment</span>
                    <p class="text-blue-600 font-bold text-sm mb-1">Total Buildings</p>
                    <h4 class="text-4xl font-black text-blue-900 building-total">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-green-50 p-6 rounded-xl border border-green-100">
                    <span class="material-icons-round stat-icon text-green-400">home</span>
                    <p class="text-green-600 font-bold text-sm mb-1">Residential</p>
                    <h4 class="text-4xl font-black text-green-900 building-residential">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-purple-50 p-6 rounded-xl border border-purple-100">
                    <span class="material-icons-round stat-icon text-purple-400">storefront</span>
                    <p class="text-purple-600 font-bold text-sm mb-1">Commercial</p>
                    <h4 class="text-4xl font-black text-purple-900 building-commercial">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-orange-50 p-6 rounded-xl border border-orange-100">
                    <span class="material-icons-round stat-icon text-orange-400">factory</span>
                    <p class="text-orange-600 font-bold text-sm mb-1">Industrial</p>
                    <h4 class="text-4xl font-black text-orange-900 building-industrial">-</h4>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
                <div class="infographic-card relative overflow-hidden bg-pink-50 p-6 rounded-xl border border-pink-100">
                    <span class="material-icons-round stat-icon text-pink-400">holiday_village</span>
                    <p class="text-pink-600 font-bold text-sm mb-1">Mixed Use</p>
                    <h4 class="text-4xl font-black text-pink-900 building-mixed">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-indigo-50 p-6 rounded-xl border border-indigo-100">
                    <span class="material-icons-round stat-icon text-indigo-400">account_balance</span>
                    <p class="text-indigo-600 font-bold text-sm mb-1">Institution</p>
                    <h4 class="text-4xl font-black text-indigo-900 building-institution">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-teal-50 p-6 rounded-xl border border-teal-100">
                    <span class="material-icons-round stat-icon text-teal-400">school</span>
                    <p class="text-teal-600 font-bold text-sm mb-1">Educational</p>
                    <h4 class="text-4xl font-black text-teal-900 building-educational">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-slate-100 p-6 rounded-xl border border-slate-200">
                    <span class="material-icons-round stat-icon text-slate-400">more_horiz</span>
                    <p class="text-slate-600 font-bold text-sm mb-1">Others</p>
                    <h4 class="text-4xl font-black text-slate-900 building-others">-</h4>
                </div>
            </div>
        </section>

        <!-- Sanitation Facilities Section -->
        <section>
            <h3 class="text-2xl font-extrabold text-slate-900 mb-6 flex items-center gap-2">
                <span class="w-8 h-1 bg-accent-teal rounded-full"></span>
                Sanitation Facilities
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center gap-4">
                    <div class="bg-blue-100 p-3 rounded-lg text-blue-600">
                        <span class="material-icons-round">water</span>
                    </div>
                    <div>
                        <h5 class="text-xl font-bold sewer-count">-</h5>
                        <p class="text-[10px] uppercase font-bold text-slate-500">Sewer Network</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center gap-4">
                    <div class="bg-green-100 p-3 rounded-lg text-green-600">
                        <span class="material-icons-round">storage</span>
                    </div>
                    <div>
                        <h5 class="text-xl font-bold septic-count">-</h5>
                        <p class="text-[10px] uppercase font-bold text-slate-500">Septic Tanks</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center gap-4">
                    <div class="bg-purple-100 p-3 rounded-lg text-purple-600">
                        <span class="material-icons-round">hourglass_bottom</span>
                    </div>
                    <div>
                        <h5 class="text-xl font-bold pit-count">-</h5>
                        <p class="text-[10px] uppercase font-bold text-slate-500">Pit/Holding Tank</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center gap-4">
                    <div class="bg-pink-100 p-3 rounded-lg text-pink-600">
                        <span class="material-icons-round">science</span>
                    </div>
                    <div>
                        <h5 class="text-xl font-bold treatment-count">-</h5>
                        <p class="text-[10px] uppercase font-bold text-slate-500">Onsite Treatment</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center gap-4">
                    <div class="bg-orange-100 p-3 rounded-lg text-orange-600">
                        <span class="material-icons-round">wc</span>
                    </div>
                    <div>
                        <h5 class="text-xl font-bold composting-count">-</h5>
                        <p class="text-[10px] uppercase font-bold text-slate-500">Composting Toilets</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Public Utilities Section -->
        <section>
            <h3 class="text-2xl font-extrabold text-slate-900 mb-6 flex items-center gap-2">
                <span class="w-8 h-1 bg-accent-orange rounded-full"></span>
                Public Utilities
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-8 relative overflow-hidden min-h-[220px]">
                    <div class="relative z-10">
                        <p class="text-amber-800 font-bold text-sm uppercase tracking-wide">Road Infrastructure</p>
                        <h4 class="text-5xl font-black text-amber-950 mt-2"><span class="road-length">-</span> <span class="text-lg font-medium">m</span></h4>
                        <p class="text-amber-700 mt-1 font-semibold">Total Road Length</p>
                    </div>
                    <div class="absolute bottom-0 right-0 w-2/3 opacity-30">
                        <svg class="fill-amber-400" viewBox="0 0 200 100">
                            <path d="M0 100 Q 50 20, 100 80 T 200 0 L 200 100 Z"></path>
                            <path d="M20 100 L 40 85 M 60 75 L 80 65 M 100 60 L 120 50" stroke="white" stroke-dasharray="4,4" stroke-width="2"></path>
                        </svg>
                    </div>
                </div>
                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-8 relative overflow-hidden min-h-[220px]">
                    <div class="relative z-10">
                        <p class="text-blue-800 font-bold text-sm uppercase tracking-wide">Drainage Network</p>
                        <h4 class="text-5xl font-black text-blue-950 mt-2"><span class="drainage-length">-</span> <span class="text-lg font-medium">m</span></h4>
                        <p class="text-blue-700 mt-1 font-semibold">Total Drain Length</p>
                    </div>
                    <div class="absolute bottom-0 right-0 w-2/3 opacity-30">
                        <svg class="fill-blue-400" viewBox="0 0 200 100">
                            <rect height="20" width="200" x="0" y="80"></rect>
                            <rect height="10" width="160" x="20" y="70"></rect>
                            <rect height="10" width="120" x="40" y="60"></rect>
                        </svg>
                    </div>
                </div>
                <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-8 relative overflow-hidden min-h-[220px]">
                    <div class="relative z-10">
                        <p class="text-emerald-800 font-bold text-sm uppercase tracking-wide">Water Supply</p>
                        <h4 class="text-5xl font-black text-emerald-950 mt-2"><span class="water-length">-</span> <span class="text-lg font-medium">m</span></h4>
                        <p class="text-emerald-700 mt-1 font-semibold">Total Length</p>
                    </div>
                    <div class="absolute bottom-0 right-0 w-2/3 opacity-30">
                        <svg class="fill-emerald-400" viewBox="0 0 200 100">
                            <path d="M0 50 C 50 30, 150 70, 200 50 L 200 100 L 0 100 Z"></path>
                            <circle cx="50" cy="30" fill-opacity="0.3" r="10"></circle>
                            <circle cx="120" cy="40" fill-opacity="0.3" r="15"></circle>
                        </svg>
                    </div>
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
    const content = document.getElementById('dashboard-content');
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
        document.querySelector('.building-total').textContent = formatNumber(data.buildings?.total || 0);
        document.querySelector('.building-residential').textContent = formatNumber(data.buildings?.residential || 0);
        document.querySelector('.building-commercial').textContent = formatNumber(data.buildings?.commercial || 0);
        document.querySelector('.building-industrial').textContent = formatNumber(data.buildings?.industrial || 0);
        document.querySelector('.building-mixed').textContent = formatNumber(data.buildings?.mixed_use || 0);
        document.querySelector('.building-institution').textContent = formatNumber(data.buildings?.institution || 0);
        document.querySelector('.building-educational').textContent = formatNumber(data.buildings?.educational || 0);
        document.querySelector('.building-others').textContent = formatNumber(data.buildings?.others || 0);

        // Update Sanitation Facilities
        document.querySelector('.sewer-count').textContent = formatNumber(data.sanitation?.sewer || 0);
        document.querySelector('.septic-count').textContent = formatNumber(data.sanitation?.septic || 0);
        document.querySelector('.pit-count').textContent = formatNumber(data.sanitation?.pit || 0);
        document.querySelector('.treatment-count').textContent = formatNumber(data.sanitation?.treatment || 0);
        document.querySelector('.composting-count').textContent = formatNumber(data.sanitation?.composting || 0);

        // Update Public Utilities
        document.querySelector('.road-length').textContent = formatNumber(data.utilities?.road || 0);
        document.querySelector('.drainage-length').textContent = formatNumber(data.utilities?.drainage || 0);
        document.querySelector('.water-length').textContent = formatNumber(data.utilities?.water || 0);

        // Update FSM Services
        document.querySelector('.fsm-providers').textContent = formatNumber(data.fsm?.providers || 0);
        document.querySelector('.fsm-vehicles').textContent = formatNumber(data.fsm?.vehicles || 0);
        document.querySelector('.fsm-plants').textContent = formatNumber(data.fsm?.plants || 0);
        document.querySelector('.fsm-applications').textContent = formatNumber(data.fsm?.applications || 0);
        document.querySelector('.fsm-volume').textContent = formatNumber(data.fsm?.volume || 0);
        document.querySelector('.fsm-revenue').textContent = formatNumber(data.fsm?.revenue || 0);

        // Update Public Health
        document.querySelector('.health-hotspots').textContent = formatNumber(data.health?.hotspots || 0);
        document.querySelector('.health-waterborne').textContent = formatNumber(data.health?.waterborne || 0);
        document.querySelector('.health-toilet-users').textContent = formatNumber(data.health?.toilet_users || 0);
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
    return parseInt(num).toLocaleString();
  }
</script>
@endpush
