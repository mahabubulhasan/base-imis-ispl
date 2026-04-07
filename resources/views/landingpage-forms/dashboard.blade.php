<!-- Last Modified: 2026-04-07
// Developed By: Streams Tech Ltd.
// Description: Public Dashboard with infographic card layout for municipal data visualization -->

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
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">water</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Sewer Network</p>
                    <h4 class="text-4xl font-black text-slate-900 sewer-count">-</h4>
                </div>
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
                    <span class="material-icons-round stat-icon text-cyan-600">science</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Onsite Treatment</p>
                    <h4 class="text-4xl font-black text-slate-900 treatment-count">-</h4>
                </div>
                <div class="infographic-card relative overflow-hidden bg-white p-6 rounded-xl border border-slate-300 shadow-sm">
                    <span class="material-icons-round stat-icon text-cyan-600">wc</span>
                    <p class="text-slate-700 font-bold text-sm mb-1">Composting Toilets</p>
                    <h4 class="text-4xl font-black text-slate-900 composting-count">-</h4>
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
