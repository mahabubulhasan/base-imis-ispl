<?php
// Last Modified: 2026-04-26
// Developed By: Streams Tech Ltd.
// Description: Public dashboard controller that returns unauthenticated dashboard metrics, including CWIS equity and safety indicators.

namespace App\Http\Controllers;

use App\Models\Cwis\cwis_mne;
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
        * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\JsonResponse
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

        // Check if AJAX request - return JSON data structure
        if (request()->ajax()) {
            $publicCwisData = $this->getPublicCwisData();

            return response()->json([
                'buildings' => [
                    'total' => $buildingCount,
                    'residential' => $residentialBuildingCount,
                    'commercial' => $commercialBuildCount,
                    'industrial' => $industrialBuildingCount,
                    'mixed_use' => $mixedBuildCount,
                    'institution' => $institutionBuildingCount,
                    'educational' => $educationBuildingCount,
                    'others' => $othersCount,
                ],
                'sanitation' => [
                    'septic' => intval(DB::table('building_info.buildings as b')
                        ->join('building_info.sanitation_systems as s', 'b.sanitation_system_id', '=', 's.id')
                        ->where('s.sanitation_system', 'like', '%Septic%')
                        ->whereNull('b.deleted_at')
                        ->count()),
                    'pit' => intval(DB::table('building_info.buildings as b')
                        ->join('building_info.sanitation_systems as s', 'b.sanitation_system_id', '=', 's.id')
                        ->where('s.sanitation_system', 'like', '%Pit%')
                        ->whereNull('b.deleted_at')
                        ->count()),
                    'others' => intval($sanitationSystemOther),
                ],
                'utilities' => [
                    'road' => intval($sumRoads),
                    'drainage' => intval($sumDrains),
                    'water' => intval($sumWatersupply),
                ],
                'fsm' => [
                    'providers' => $serviceProviderCount,
                    'vehicles' => $desludgingVehicleCount,
                    'plants' => $treatmentPlantCount,
                    'applications' => $applicationCount,
                    'volume' => intval($sludgeCollectionsCount),
                    'revenue' => intval($costPaidByOwnerWithReceipt),
                ],
                'health' => [
                    'hotspots' => $totalHotspot,
                    'waterborne' => intval($totalWaterborne),
                    'toilet_users' => intval($totalPtUser),
                ],
                'cwis' => $publicCwisData,
            ]);
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

    /**
     * Get the latest public CWIS equity and safety indicators.
     */
    private function getPublicCwisData(): array
    {
        $indicatorMap = [
            'EQ-1' => ['group' => 'equity', 'key' => 'eq1'],
            'SF-1a' => ['group' => 'safety', 'key' => 'sf1a'],
            'SF-1b' => ['group' => 'safety', 'key' => 'sf1b'],
            'SF-1c' => ['group' => 'safety', 'key' => 'sf1c'],
            'SF-1d' => ['group' => 'safety', 'key' => 'sf1d'],
            'SF-1e' => ['group' => 'safety', 'key' => 'sf1e'],
            'SF-1f' => ['group' => 'safety', 'key' => 'sf1f'],
            'SF-1g' => ['group' => 'safety', 'key' => 'sf1g'],
            'SF-2a' => ['group' => 'safety', 'key' => 'sf2a'],
            'SF-2b' => ['group' => 'safety', 'key' => 'sf2b'],
            'SF-2c' => ['group' => 'safety', 'key' => 'sf2c'],
            'SF-3' => ['group' => 'safety', 'key' => 'sf3'],
            'SF-3b' => ['group' => 'safety', 'key' => 'sf3b'],
            'SF-3c' => ['group' => 'safety', 'key' => 'sf3c'],
            'SF-3e' => ['group' => 'safety', 'key' => 'sf3e'],
            'SF-4a' => ['group' => 'safety', 'key' => 'sf4a'],
            'SF-4b' => ['group' => 'safety', 'key' => 'sf4b'],
            'SF-4d' => ['group' => 'safety', 'key' => 'sf4d'],
            'SF-5' => ['group' => 'safety', 'key' => 'sf5'],
            'SF-6' => ['group' => 'safety', 'key' => 'sf6'],
            'SF-7' => ['group' => 'safety', 'key' => 'sf7'],
            'SF-9' => ['group' => 'safety', 'key' => 'sf9'],
        ];

        $defaultMetrics = [
            'year' => null,
            'equity' => [
                'eq1' => ['value' => null],
            ],
            'safety' => [
                'sf1a' => ['value' => null],
                'sf1b' => ['value' => null],
                'sf1c' => ['value' => null],
                'sf1d' => ['value' => null],
                'sf1e' => ['value' => null],
                'sf1f' => ['value' => null],
                'sf1g' => ['value' => null],
                'sf2a' => ['value' => null],
                'sf2b' => ['value' => null],
                'sf2c' => ['value' => null],
                'sf3' => ['value' => null],
                'sf3b' => ['value' => null],
                'sf3c' => ['value' => null],
                'sf3e' => ['value' => null],
                'sf4a' => ['value' => null],
                'sf4b' => ['value' => null],
                'sf4d' => ['value' => null],
                'sf5' => ['value' => null],
                'sf6' => ['value' => null],
                'sf7' => ['value' => null],
                'sf9' => ['value' => null],
            ],
        ];

        $latestYear = cwis_mne::max('year');

        if (!$latestYear) {
            return $defaultMetrics;
        }

        $metrics = $defaultMetrics;
        $metrics['year'] = $latestYear;

        $records = cwis_mne::query()
            ->where('year', $latestYear)
            ->whereIn('indicator_code', array_keys($indicatorMap))
            ->get(['indicator_code', 'data_value']);

        foreach ($records as $record) {
            if (!isset($indicatorMap[$record->indicator_code])) {
                continue;
            }

            $indicator = $indicatorMap[$record->indicator_code];
            $metrics[$indicator['group']][$indicator['key']]['value'] = $this->normalizeCwisValue($record->data_value);
        }

        return $metrics;
    }

    /**
     * Normalize CWIS values so invalid entries can be rendered safely in the public UI.
     *
     * @param mixed $value
     */
    private function normalizeCwisValue($value): ?float
    {
        if ($value === null) {
            return null;
        }

        $normalizedValue = trim(html_entity_decode((string) $value));

        if ($normalizedValue === '') {
            return null;
        }

        $lowerValue = strtolower($normalizedValue);

        if ($lowerValue === 'na' || $lowerValue === 'nan') {
            return null;
        }

        if (!is_numeric($normalizedValue)) {
            return null;
        }

        return (float) $normalizedValue;
    }
}
