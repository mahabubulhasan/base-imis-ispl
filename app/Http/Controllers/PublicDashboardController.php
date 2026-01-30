<?php
// Last Modified Date: 30-01-2026
// Developed By: GitHub Copilot
// Purpose: Public Dashboard Controller - Provides dashboard data for unauthenticated users without role-based filtering

namespace App\Http\Controllers;

use App\Models\Fsm\VacutugType;
use App\Models\BuildingInfo\Building;
use App\Models\Fsm\Containment;
use App\Models\Fsm\Emptying;
use App\Models\Fsm\ServiceProvider;
use App\Models\Fsm\Application;
use App\Models\Fsm\TreatmentPlant;
use App\Models\Fsm\SludgeCollection;
use App\Services\DashboardService;
use Illuminate\Support\Facades\DB;
use App\Models\UtilityInfo\Roadline;
use App\Models\UtilityInfo\SewerLine;
use App\Models\UtilityInfo\Drain;
use App\Models\Fsm\Ctpt;
use App\Models\PublicHealth\Hotspots;
use App\Models\PublicHealth\YearlyWaterborne;
use App\Models\UtilityInfo\WaterSupplys;
use App\Models\BuildingInfo\FunctionalUse;

class PublicDashboardController extends Controller
{
    /**
     * Public Dashboard Service
     */
    protected DashboardService $dashboardService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
        // No authentication middleware for public access
    }

    /**
     * Show the public dashboard.
     * Returns all dashboard data without role-based filtering for public viewing.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $page_title = __("IMIS Public Dashboard");

        // Building Counts
        $buildingCount = Building::whereNull('deleted_at')->count();

        $commercialBuildCount = $this->dashboardService->countBuildingsByUseExact('Commercial');
        $residentialBuildingCount = $this->dashboardService->countBuildingsByUseExact('Residential');
        $mixedBuildCount = $this->dashboardService->countBuildingsByUseExact('Mixed (Residential, Commercial, Office uses)');
        $industrialBuildingCount = $this->dashboardService->countBuildingsByUseExact('Industrial');
        $educationBuildingCount = $this->dashboardService->countBuildingsByUseExact('Educational');
        $institutionBuildingCount = $this->dashboardService->countBuildingsByUse('Institution');
        $institutionNames = FunctionalUse::where('name', 'like', '%Institution%')
            ->pluck('name')
            ->implode('<br>');
        $othersCount = $buildingCount - ($commercialBuildCount + $residentialBuildingCount + $mixedBuildCount + $industrialBuildingCount + $institutionBuildingCount + $educationBuildingCount);

        // Sanitation Systems Count
        $containmentCount = Containment::whereNull('deleted_at')->count();

        // Utility Count
        $sumRoads = Roadline::sum('length') ?? 0;
        $sumSewers = SewerLine::sum('length') ?? 0;
        $sumDrains = Drain::sum('length') ?? 0;
        $sumWatersupply = WaterSupplys::sum('length') ?? 0;

        // PT/CT Count
        $ctCount = Ctpt::where('type', 'Community Toilet')->whereNull('deleted_at')->count();
        $ptCount = Ctpt::where('type', 'Public Toilet')->whereNull('deleted_at')->count();

        // Community Toilet Users
        $communityToilet = DB::table('fsm.toilets as t')
            ->select(DB::raw('sum(b.population_served) as toilet_users'))
            ->leftJoin('fsm.build_toilets as bt', function($join) {
                $join->on('bt.toilet_id', '=', 't.id')
                     ->whereNull('bt.deleted_at');
            })
            ->leftJoin('building_info.buildings as b', 'b.bin', '=', 'bt.bin')
            ->where('t.type', '=', 'Community Toilet')
            ->where('t.status', true)
            ->whereNull('t.deleted_at')->first();

        $totalCtUser = $communityToilet->toilet_users ?? 0;

        // Sanitation Systems Other
        $sanitationSystemOther = DB::table('building_info.buildings as b')
            ->join('building_info.sanitation_systems as s', 'b.sanitation_system_id', '=', 's.id')
            ->where('s.dashboard_display', false)
            ->whereNull('b.deleted_at')
            ->count();

        $sanitationSystemOthername = DB::table('building_info.buildings as b')
            ->join('building_info.sanitation_systems as s', 'b.sanitation_system_id', '=', 's.id')
            ->where('s.dashboard_display', false)
            ->where('s.id', '!=', 11)
            ->whereNull('b.deleted_at')
            ->distinct()
            ->select('s.sanitation_system')
            ->get();

        // Public Toilet Users
        $publicToilet = DB::table('fsm.toilets as t')
            ->join('fsm.ctpt_users as u', 't.id', '=', 'u.toilet_id')
            ->selectRaw('sum(u.no_male_user) as total_male_user, sum(u.no_female_user) as total_female_user')
            ->where('t.type', 'Public Toilet')
            ->where('t.status', true)
            ->whereNull('t.deleted_at')
            ->whereNull('u.deleted_at')->first();

        $totalPtUser = ($publicToilet->total_male_user ?? 0) + ($publicToilet->total_female_user ?? 0);

        // Date Range
        $maxDate = date('Y');
        $minDate = date('Y') - 4;

        // FSM Service Dashboard - No role-based filtering
        $numberOfEmptyingbyMonthsChart = $this->dashboardService->getNumberOfEmptyingbyMonths(null);

        $uniqueContainCodeEmptiedCount = Application::where('emptying_status', true)
            ->whereNull('deleted_at')
            ->distinct('containment_id')
            ->count('containment_id');

        $emptyingServiceCount = Application::where('emptying_status', true)
            ->whereNull('deleted_at')
            ->count('id');

        $serviceProviderCount = ServiceProvider::where('status', 1)
            ->whereNull('deleted_at')
            ->count();

        $applicationCount = Application::whereNull('deleted_at')->count();

        $costPaidByContainmentOwnerPerwardChart = $this->dashboardService->getCostPaidByContainmentOwnerPerward(null);

        $sludgeCollectionEmptyingServices = Emptying::whereNull('deleted_at')->sum('volume_of_sludge') ?? 0;

        $sludgeCollectionsCount = SludgeCollection::whereNull('deleted_at')->sum('volume_of_sludge') ?? 0;

        $treatmentPlantCount = TreatmentPlant::where('status', 1)
            ->whereNull('deleted_at')
            ->count();

        $desludgingVehicleCount = VacutugType::where('status', 1)
            ->whereNull('deleted_at')
            ->count();

        $costPaidByOwnerWithReceipt = Emptying::whereNull('deleted_at')->sum('total_cost') ?? 0;

        $emptyingServicePerWardsChart = $this->dashboardService->getEmptyingServicePerWards(null);

        $monthlyAppRequestByoperators = $this->dashboardService->getMonthlyAppRequestByoperators(null);

        $fsmSrvcQltyChart = $this->dashboardService->getFsmSrvcQltyChart(null);

        $ppe = $this->dashboardService->getppeChart(null);

        $hotspotsPerWardChart = $this->dashboardService->getHotspotsPerWard(null);

        $sludgeCollectionByTreatmentPlantChart = $this->dashboardService->getSludgeCollectionByTreatmentPlantChart(null);

        $totalHotspot = Hotspots::count();

        $totalWaterborne = YearlyWaterborne::sum('total_no_of_cases') ?? 0;

        // Chart Data
        $buildingsPerWardChart = $this->dashboardService->getBuildingsPerWardChart();
        $sanitationSystemsChart = $this->dashboardService->getSanitationSystemsChart();
        $emptyingRequestsbyStructureTypesChart = $this->dashboardService->getEmptyingRequestsPerStructureTypeChart();
        $containmentTypesPerWardChart = $this->dashboardService->getContainmentTypesPerWard();
        $containmentTypesByBldgUsesChart = $this->dashboardService->getContainmentTypesByBldgUse();
        $containmentTypesByBldgUsesResidentialsChart = $this->dashboardService->getContainmentTypesByBldgUseResidentials();
        $containmentTypesByLanduseChart = $this->dashboardService->getContainmentTypesByLanduse();
        $emptyingServiceByTypeYearChart = $this->dashboardService->getEmptyingServiceByTypeYear();
        $containmentEmptiedByWardChart = $this->dashboardService->getcontainmentEmptiedByWard();
        $containTypeChart = $this->dashboardService->getContainTypeChart();
        $buildingUseChart = $this->dashboardService->getBuildingUseChart();
        $nextEmptyingContainmentsChart = $this->dashboardService->getNextEmptyingContainmentsChart();
        $taxRevenueChart = $this->dashboardService->getTaxRevenueChart();
        $solidWasteChart = $this->dashboardService->getSolidWastePaymentChart();
        $waterSupplyPaymentChart = $this->dashboardService->getWaterSupplyPaymentChart();
        $proposedEmptyingDateContainmentsChart = $this->dashboardService->getproposedEmptyingDateContainmentsChart();
        $proposedEmptiedDateContainmentsByWardChart = $this->dashboardService->getProposedEmptiedDateContainmentsByWard();
        $drainLengthPerWardChart = $this->dashboardService->getDrainLengthPerWardChart();
        $roadLengthPerWardChart = $this->dashboardService->getRoadLengthPerWardChart();
        $waterborneCasesChart = $this->dashboardService->getWaterborneCasesChart();
        $sanitationSystems = $this->dashboardService->getBuildingSanitationSystem();
        $sanitationSystemsOthers = $this->dashboardService->getBuildingSanitationSystemOthers();
        $swmPresenceward = $this->dashboardService->swmPresencebyWard();
        $taxCodePresenceward = $this->dashboardService->taxCodePresencebyWard();
        $pipeCodePresenceWard = $this->dashboardService->waterSupplyPipeCodePresenceByWard();
        $treatmentPlantTest = $this->dashboardService->treatmentPlantTestResultsByYear();

        // Check if AJAX request - return only content without layout
        if (request()->ajax()) {
            return view('dashboard.public-dashboard', compact(
                'page_title',
                'buildingCount',
                'commercialBuildCount',
                'residentialBuildingCount',
                'mixedBuildCount',
                'educationBuildingCount',
                'containmentCount',
                'emptyingServiceCount',
                'serviceProviderCount',
                'sludgeCollectionsCount',
                'uniqueContainCodeEmptiedCount',
                'desludgingVehicleCount',
                'applicationCount',
                'buildingsPerWardChart',
                'sanitationSystemsChart',
                'numberOfEmptyingbyMonthsChart',
                'emptyingRequestsbyStructureTypesChart',
                'containmentTypesPerWardChart',
                'emptyingServicePerWardsChart',
                'emptyingServiceByTypeYearChart',
                'containmentEmptiedByWardChart',
                'containTypeChart',
                'buildingUseChart',
                'nextEmptyingContainmentsChart',
                'sludgeCollectionByTreatmentPlantChart',
                'fsmSrvcQltyChart',
                'ppe',
                'taxRevenueChart',
                'waterSupplyPaymentChart',
                'proposedEmptyingDateContainmentsChart',
                'proposedEmptiedDateContainmentsByWardChart',
                'maxDate',
                'minDate',
                'containmentTypesByBldgUsesChart',
                'monthlyAppRequestByoperators',
                'containmentTypesByBldgUsesResidentialsChart',
                'containmentTypesByLanduseChart',
                'costPaidByOwnerWithReceipt',
                'costPaidByContainmentOwnerPerwardChart',
                'sludgeCollectionEmptyingServices',
                'treatmentPlantCount',
                'hotspotsPerWardChart',
                'sanitationSystemOther',
                'sanitationSystemOthername',
                'drainLengthPerWardChart',
                'industrialBuildingCount',
                'institutionBuildingCount',
                'othersCount',
                'sumRoads',
                'sumDrains',
                'sumSewers',
                'sumWatersupply',
                'ctCount',
                'ptCount',
                'totalCtUser',
                'totalPtUser',
                'totalHotspot',
                'totalWaterborne',
                'roadLengthPerWardChart',
                'waterborneCasesChart',
                'sanitationSystems',
                'sanitationSystemsOthers',
                'institutionNames',
                'solidWasteChart',
                'swmPresenceward',
                'taxCodePresenceward',
                'pipeCodePresenceWard',
                'treatmentPlantTest',
            ));
        }

        // Return full page view for direct access
        return view('dashboard.public-dashboard-page', compact(
            'page_title',
            'buildingCount',
            'commercialBuildCount',
            'residentialBuildingCount',
            'mixedBuildCount',
            'educationBuildingCount',
            'containmentCount',
            'emptyingServiceCount',
            'serviceProviderCount',
            'sludgeCollectionsCount',
            'uniqueContainCodeEmptiedCount',
            'desludgingVehicleCount',
            'applicationCount',
            'buildingsPerWardChart',
            'sanitationSystemsChart',
            'numberOfEmptyingbyMonthsChart',
            'emptyingRequestsbyStructureTypesChart',
            'containmentTypesPerWardChart',
            'emptyingServicePerWardsChart',
            'emptyingServiceByTypeYearChart',
            'containmentEmptiedByWardChart',
            'containTypeChart',
            'buildingUseChart',
            'nextEmptyingContainmentsChart',
            'sludgeCollectionByTreatmentPlantChart',
            'fsmSrvcQltyChart',
            'ppe',
            'taxRevenueChart',
            'waterSupplyPaymentChart',
            'proposedEmptyingDateContainmentsChart',
            'proposedEmptiedDateContainmentsByWardChart',
            'maxDate',
            'minDate',
            'containmentTypesByBldgUsesChart',
            'monthlyAppRequestByoperators',
            'containmentTypesByBldgUsesResidentialsChart',
            'containmentTypesByLanduseChart',
            'costPaidByOwnerWithReceipt',
            'costPaidByContainmentOwnerPerwardChart',
            'sludgeCollectionEmptyingServices',
            'treatmentPlantCount',
            'hotspotsPerWardChart',
            'sanitationSystemOther',
            'sanitationSystemOthername',
            'drainLengthPerWardChart',
            'industrialBuildingCount',
            'institutionBuildingCount',
            'othersCount',
            'sumRoads',
            'sumDrains',
            'sumSewers',
            'sumWatersupply',
            'ctCount',
            'ptCount',
            'totalCtUser',
            'totalPtUser',
            'totalHotspot',
            'totalWaterborne',
            'roadLengthPerWardChart',
            'waterborneCasesChart',
            'sanitationSystems',
            'sanitationSystemsOthers',
            'institutionNames',
            'solidWasteChart',
            'swmPresenceward',
            'taxCodePresenceward',
            'pipeCodePresenceWard',
            'treatmentPlantTest',
        ));
    }
}
