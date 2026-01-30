<!-- Last Modified Date: 30-01-2026
Developed By: GitHub Copilot
Purpose: Public Dashboard View for Landing Page -->

<!-- BUILDINGS SECTION -->
<div class="mb-8 space-y-4">
    <h3 class="text-[#1f3b7d] text-xl md:text-2xl font-bold border-b-4 border-[#0056b3] pb-2">{{ __("Buildings") }}</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border-l-4 border-blue-500 shadow-sm">
            @include('dashboard.countBox._buildCountBox')
        </div>
        <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg border-l-4 border-green-500 shadow-sm">
            @include('dashboard.countBox._residentialBuildCountBox')
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg border-l-4 border-purple-500 shadow-sm">
            @include('dashboard.countBox._commercialBuildCountBox')
        </div>
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-4 rounded-lg border-l-4 border-orange-500 shadow-sm">
            @include('dashboard.countBox._industrialBuildCountBox')
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-pink-50 to-pink-100 p-4 rounded-lg border-l-4 border-pink-500 shadow-sm">
            @include('dashboard.countBox._mixedBuildCountBox')
        </div>
        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-4 rounded-lg border-l-4 border-indigo-500 shadow-sm">
            @include('dashboard.countBox._institutionCountBox')
        </div>
        <div class="bg-gradient-to-br from-cyan-50 to-cyan-100 p-4 rounded-lg border-l-4 border-cyan-500 shadow-sm">
            @include('dashboard.countBox._educationBuildCountBox')
        </div>
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-4 rounded-lg border-l-4 border-gray-500 shadow-sm">
            @include('dashboard.countBox._othersBuildCountBox')
        </div>
    </div>
</div>

<!-- SANITATION FACILITIES SECTION -->
<div class="mb-8 space-y-4">
    <h3 class="text-[#1f3b7d] text-xl md:text-2xl font-bold border-b-4 border-[#0056b3] pb-2">{{ __("Building Sanitation Facilities") }}</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($sanitationSystems as $sanitationSystem)
            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        @if (
                            $sanitationSystem->icon_name &&
                                $sanitationSystem->icon_name != 'no_icon' &&
                                $sanitationSystem->icon_name != 'others.svg')
                            <img src="{{ asset('img/svg/imis-icons/' . $sanitationSystem->icon_name) }}"
                                alt="{{ __($sanitationSystem->sanitation_system) }}" class="h-6 w-6">
                        @else
                            <i class="fa fa-building text-blue-600" aria-hidden="true"></i>
                        @endif
                    </div>
                    <div>
                        <h5 class="text-2xl font-bold text-blue-600">{{ number_format($sanitationSystem->bin_count) }}</h5>
                        <p class="text-sm text-gray-600">{{ __($sanitationSystem->sanitation_system) }}</p>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
            @include('dashboard.countBox._sanitationOffsiteContainmentCountBox')
        </div>
    </div>
</div>

<!-- UTILITIES SECTION -->
<div class="mb-8 space-y-4">
    <h3 class="text-[#1f3b7d] text-xl md:text-2xl font-bold border-b-4 border-[#0056b3] pb-2">{{ __("Utilities") }}</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-amber-50 to-amber-100 p-4 rounded-lg border-l-4 border-amber-500 shadow-sm">
            @include('dashboard.countBox._sumRoadsCountBox')
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border-l-4 border-blue-500 shadow-sm">
            @include('dashboard.countBox._sumDrainsCountBox')
        </div>
        <div class="bg-gradient-to-br from-teal-50 to-teal-100 p-4 rounded-lg border-l-4 border-teal-500 shadow-sm">
            @include('dashboard.countBox._sumWatersupplyCountBox')
        </div>
    </div>
</div>

<!-- FSM SERVICES SECTION -->
<div class="mb-8 space-y-4">
    <h3 class="text-[#1f3b7d] text-xl md:text-2xl font-bold border-b-4 border-[#0056b3] pb-2">{{ __("FSM Services") }}</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.fsmCharts._serviceProvidersCountBox')
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.fsmCharts._desludgingVehicleCountBox')
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.fsmCharts._treatmentPlantCountBox')
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.fsmCharts._emptyingServicesCountBox')
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.fsmCharts._sludgeCollectionsEmptyingServicesBox')
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.fsmCharts._costPaidByOwnerWithReceiptBox')
        </div>
    </div>
</div>

<!-- PT/CT SECTION -->
<div class="mb-8 space-y-4">
    <h3 class="text-[#1f3b7d] text-xl md:text-2xl font-bold border-b-4 border-[#0056b3] pb-2">{{ __("PT/CT") }}</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-red-50 to-red-100 p-4 rounded-lg border-l-4 border-red-500 shadow-sm">
            @include('dashboard.countBox._pTCountBox')
        </div>
        <div class="bg-gradient-to-br from-rose-50 to-rose-100 p-4 rounded-lg border-l-4 border-rose-500 shadow-sm">
            @include('dashboard.countBox._cTCountBox')
        </div>
        <div class="bg-gradient-to-br from-violet-50 to-violet-100 p-4 rounded-lg border-l-4 border-violet-500 shadow-sm">
            @include('dashboard.countBox._totalPtUserCountBox')
        </div>
        <div class="bg-gradient-to-br from-fuchsia-50 to-fuchsia-100 p-4 rounded-lg border-l-4 border-fuchsia-500 shadow-sm">
            @include('dashboard.countBox._totalCtUserCountBox')
        </div>
    </div>
</div>

<!-- PUBLIC HEALTH SECTION -->
<div class="mb-8 space-y-4">
    <h3 class="text-[#1f3b7d] text-xl md:text-2xl font-bold border-b-4 border-[#0056b3] pb-2">{{ __("Public Health") }}</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-gradient-to-br from-red-50 to-red-100 p-4 rounded-lg border-l-4 border-red-500 shadow-sm">
            @include('dashboard.countBox._totalHotspotCountBox')
        </div>
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-4 rounded-lg border-l-4 border-orange-500 shadow-sm">
            @include('dashboard.countBox._totalWaterBorneCasesCountBox')
        </div>
    </div>
</div>

<!-- CHARTS SECTION -->
{{-- <div class="mb-8 space-y-6">
    <h3 class="text-[#1f3b7d] text-xl md:text-2xl font-bold border-b-4 border-[#0056b3] pb-2">{{ __("Key Indicators & Analytics") }}</h3>

    <!-- Building Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.buildings._buildingsPerWardChart')
        </div>
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.buildings._buildingUseChart')
        </div>
    </div>

    <!-- Sanitation Systems Chart -->
    <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
        @include('dashboard.buildings._sanitationSystemsChart')
    </div>

    <!-- Containment Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.containments._containTypeChart')
        </div>
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.containments._containmentTypesPerWardChart')
        </div>
    </div>

    <!-- FSM Service Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.fsmCharts._emptyingServiceByTypeYearChart')
        </div>
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.fsmCharts._sludgeCollectionByTreatmentPlant')
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
        @include('dashboard.cost-paid-emptying._costPaidByContainmentOwnerPerwardChart')
    </div>

    <!-- Utility Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.charts._roadLengthPerWardChart')
        </div>
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.charts._drainLengthPerWardChart')
        </div>
    </div>

    <!-- Health Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.charts._waterborneCasesChart')
        </div>
        <div class="bg-white p-5 rounded-lg border border-gray-200 shadow-sm">
            @include('dashboard.fsmCharts._treatmentPlantTestbyYearChart')
        </div>
    </div>
</div> --}}

<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip({
            html: true
        });
    });
</script>
