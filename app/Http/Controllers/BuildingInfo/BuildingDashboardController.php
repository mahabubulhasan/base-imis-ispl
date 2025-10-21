<?php

namespace App\Http\Controllers\BuildingInfo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BuildingInfo\BuildingDashboardService;
use App\Models\Fsm\KeyPerformanceIndicator;
use Illuminate\Support\Facades\Auth;
use App\Models\BuildingInfo\Building;
use App\Models\Fsm\Containment;
use App\Models\Fsm\Emptying;
use App\Models\Fsm\ServiceProvider;
use App\Models\Fsm\Application;
use App\Models\Fsm\Feedback;
use App\Models\Fsm\TreatmentPlant;
use App\Models\Fsm\SludgeCollection;
use Illuminate\Support\Facades\DB;
use App\Models\BuildingInfo\FunctionalUse;


class BuildingDashboardController extends Controller
{
    protected BuildingDashboardService $buildingdashboardService;


    public function __construct(BuildingDashboardService $buildingdashboardService)
    {
        $this->middleware('auth');
        $this->buildingdashboardService = $buildingdashboardService;
    }

    public function index()
    { {

            $page_title = __("Building Dashboard");
            $buildingCount = Building::whereNull('deleted_at')->count();


            $commercialBuildCount = $this->buildingdashboardService->countBuildingsByUseExact('Commercial');
            $residentialBuildingCount = $this->buildingdashboardService->countBuildingsByUseExact('Residential');
            $mixedBuildCount = $this->buildingdashboardService->countBuildingsByUseExact('Mixed (Residential, Commercial, Office uses)');
            $industrialBuildingCount = $this->buildingdashboardService->countBuildingsByUseExact('Industrial');
            $educationBuildingCount = $this->buildingdashboardService->countBuildingsByUseExact('Educational');
            $institutionBuildingCount = $this->buildingdashboardService->countBuildingsByUse('Institution');
            $institutionNames = FunctionalUse::where('name', 'like', '%Institution%')
                ->pluck('name')
                ->implode('<br>');
            // Number of buildings with other functional uses (Displayed as "Others" in UI)
            $othersCount = $buildingCount - ($commercialBuildCount + $residentialBuildingCount + $mixedBuildCount  + $industrialBuildingCount + $institutionBuildingCount  + $educationBuildingCount);


            //sanitation systems
            $containmentCount = Containment::whereNull('deleted_at')->count();
            $sanitationSystemOther = DB::table('building_info.buildings as b')
            ->join('building_info.sanitation_systems as s', 'b.sanitation_system_id', '=', 's.id')
            ->where('s.dashboard_display', false)
            ->whereNull('b.deleted_at')
            ->count();
            $sanitationSystemOthername = $sanitationSystemNames = DB::table('building_info.buildings as b')
            ->join('building_info.sanitation_systems as s', 'b.sanitation_system_id', '=', 's.id')
            ->where('s.dashboard_display', false)
            ->where('s.id', '!=', 11) // Exclude sanitation_systems.id == 12
            ->whereNull('b.deleted_at')
            ->distinct()
            ->select('s.sanitation_system')
            ->get();


            if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')) {
                $whereRawEmptyingsServiceProvider = "emptyings.service_provider_id = " . Auth::user()->service_provider_id;
                $whereRawApplicationsServiceProvider = "applications.service_provider_id = " . Auth::user()->service_provider_id;
                $whereRawSludgeCollectionServiceProvider = "sludge_collections.service_provider_id = " . Auth::user()->service_provider_id;
                $whereRawFeedbackServiceProvider = "feedbacks.service_provider_id = " . Auth::user()->service_provider_id;
                $whereUserId = "users.id = " . Auth::user()->id;
                $whereRawSludgeServiceProvider = "sludge_collections.service_provider_id = " . Auth::user()->service_provider_id;
            } else {

                $whereRawEmptyingsServiceProvider = "1 = 1";
                $whereRawApplicationsServiceProvider = "1 = 1";
                $whereRawSludgeCollectionServiceProvider = "1 = 1";
                $whereRawFeedbackServiceProvider = "1 = 1";
                $whereUserId = "1 = 1";
                $whereRawSludgeServiceProvider = "1 = 1";
            }

            if (Auth::user()->hasRole('Treatment Plant - Admin')) {
                $whereRawTreatmentPlant = "treatment_plant_id = " . Auth::user()->treatment_plant_id;
            } else {
                $whereRawTreatmentPlant = "1 = 1";
            }
            if (request()->year) {




                $monthlyAppRequestByoperators = $this->buildingdashboardService->getMonthlyAppRequestByoperators(request()->year);

                //fsm services
                $uniqueContainCodeEmptiedCount = Application::whereYear('created_at', '=', request()->year)->Where('emptying_status', true)->whereNull('deleted_at')->whereRaw($whereRawApplicationsServiceProvider)->distinct('containment_id')->count('containment_id');
                //$uniqueContainCodeEmptiedCount = Emptying::whereYear( 'created_at', '=' , request()->year )->whereNull('deleted_at')->whereRaw($whereRawEmptyingsServiceProvider)->distinct('application_id')->count('application_id');
                $emptyingServiceCount = Emptying::whereYear('created_at', '=', request()->year)->whereNull('deleted_at')->whereRaw($whereRawEmptyingsServiceProvider)->distinct('application_id')->count('application_id');
                $serviceProviderCount = ServiceProvider::leftJoin('auth.users', 'service_providers.id', '=', 'users.service_provider_id')->where('service_providers.status', 1)->whereRaw($whereUserId)->whereYear('service_providers.created_at', '<=', request()->year)->whereNull('service_providers.deleted_at')->count();
                $applicationCount = Application::whereYear('created_at', '=', request()->year)->whereNull('deleted_at')->whereRaw($whereRawApplicationsServiceProvider)->count();
                $costPaidByContainmentOwnerPerwardChart = $this->buildingdashboardService->getCostPaidByContainmentOwnerPerward(request()->year);
                $sludgeCollectionEmptyingServices = Emptying::whereYear('created_at', '=', request()->year)->whereNull('deleted_at')->whereRaw($whereRawEmptyingsServiceProvider)->sum('volume_of_sludge');
                $sludgeCollectionsCount = SludgeCollection::whereYear('date', '=', request()->year)->whereNull('deleted_at')->whereRaw($whereRawSludgeCollectionServiceProvider)->sum('volume_of_sludge');
                $treatmentPlantCount = TreatmentPlant::leftJoin('auth.users', 'treatment_plants.id', '=', 'users.treatment_plant_id')->where('treatment_plants.status', 1)->whereYear('treatment_plants.created_at', '=', request()->year)->whereNull('treatment_plants.deleted_at')->distinct('treatment_plants.id')->whereRaw($whereUserId)->count('treatment_plants.id');
                $feedbackCount = Feedback::distinct('application_id')->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider)->count('application_id');
                $costPaidByOwnerWithReceipt = Emptying::whereYear('created_at', '=', request()->year)->whereNull('deleted_at')->whereRaw($whereRawEmptyingsServiceProvider)->sum('total_cost');
                $emptyingServicePerWardsChart = $this->buildingdashboardService->getEmptyingServicePerWards(request()->year);

                // feedback charts

                $fsmSrvcQltyChart = $this->buildingdashboardService->getFsmSrvcQltyChart(request()->year);

                $ppe = $this->buildingdashboardService->getppeChart(request()->year);

                $sludgeCollectionByTreatmentPlantChart = $this->buildingdashboardService->getSludgeCollectionByTreatmentPlantChart(request()->year);
                $hotspotsPerWardChart = $this->buildingdashboardService->getHotspotsPerWard(request()->year);

                $numberOfEmptyingbyMonthsChart = $this->buildingdashboardService->getNumberOfEmptyingbyMonths(request()->year);
            } else {
                $numberOfEmptyingbyMonthsChart = $this->buildingdashboardService->getNumberOfEmptyingbyMonths(null);
                $uniqueContainCodeEmptiedCount = Application::Where('emptying_status', true)->whereNull('deleted_at')->whereRaw($whereRawApplicationsServiceProvider)->distinct('containment_id')->count('containment_id');
                $emptyingServiceCount = Emptying::distinct('emptyings.application_id')->whereRaw($whereRawEmptyingsServiceProvider)->whereNull('emptyings.deleted_at')->count('emptyings.application_id');
                $serviceProviderCount = ServiceProvider::leftJoin('auth.users', 'service_providers.id', '=', 'users.service_provider_id')->where('service_providers.status', 1)->whereRaw($whereUserId)->whereNull('service_providers.deleted_at')->count();
                $applicationCount = Application::whereNull('deleted_at')->whereRaw($whereRawApplicationsServiceProvider)->count();
                $costPaidByContainmentOwnerPerwardChart = $this->buildingdashboardService->getCostPaidByContainmentOwnerPerward(null);
                $sludgeCollectionEmptyingServices = Emptying::whereNull('deleted_at')->whereRaw($whereRawEmptyingsServiceProvider)->sum('volume_of_sludge');
                $sludgeCollectionsCount = SludgeCollection::whereNull('deleted_at')->whereRaw($whereRawSludgeCollectionServiceProvider)->sum('volume_of_sludge');
                $treatmentPlantCount = TreatmentPlant::leftJoin('auth.users', 'treatment_plants.id', '=', 'users.treatment_plant_id')->where('treatment_plants.status', 1)->whereNull('treatment_plants.deleted_at')->whereRaw($whereUserId)->count('treatment_plants.id');

                $feedbackCount = Feedback::distinct('application_id')->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider)->count('application_id');
                $costPaidByOwnerWithReceipt = Emptying::whereNull('deleted_at')->whereRaw($whereRawEmptyingsServiceProvider)->sum('total_cost');
                $emptyingServicePerWardsChart = $this->buildingdashboardService->getEmptyingServicePerWards(null);
                $monthlyAppRequestByoperators = $this->buildingdashboardService->getMonthlyAppRequestByoperators(null);
                $fsmSrvcQltyChart = $this->buildingdashboardService->getFsmSrvcQltyChart(null);

                $ppe = $this->buildingdashboardService->getppeChart(null);


                $hotspotsPerWardChart = $this->buildingdashboardService->getHotspotsPerWard(null);
                $sludgeCollectionByTreatmentPlantChart = $this->buildingdashboardService->getSludgeCollectionByTreatmentPlantChart(null);

            }

            $buildingsPerWardChart = $this->buildingdashboardService->getBuildingsPerWardChart();
            $emptyingRequestsbyStructureTypesChart = $this->buildingdashboardService->getEmptyingRequestsPerStructureTypeChart();

            $containmentTypesPerWardChart = $this->buildingdashboardService->getContainmentTypesPerWard();
            $buildingFloorCountPerWard = $this->buildingdashboardService->getBuildingFloorCountPerWard();

            $containmentTypesByBldgUsesChart = $this->buildingdashboardService->getContainmentTypesByBldgUse();
            $containmentTypesByBldgUsesResidentialsChart = $this->buildingdashboardService->getContainmentTypesByBldgUseResidentials();

            $emptyingServiceByTypeYearChart = $this->buildingdashboardService->getEmptyingServiceByTypeYear();
            $containmentEmptiedByWardChart = $this->buildingdashboardService->getcontainmentEmptiedByWard();
            $containTypeChart = $this->buildingdashboardService->getContainTypeChart();

            $buildingUseChart = $this->buildingdashboardService->getBuildingUseChart();
            $nextEmptyingContainmentsChart = $this->buildingdashboardService->getNextEmptyingContainmentsChart();

            $proposedEmptyingDateContainmentsChart = $this->buildingdashboardService->getproposedEmptyingDateContainmentsChart();
            $proposedEmptiedDateContainmentsByWardChart = $this->buildingdashboardService->getProposedEmptiedDateContainmentsByWard();
            $sewerLengthPerWardChart = $this->buildingdashboardService->getSewerLengthPerWard();

            $maxDate = date('Y') + 1;
            $minDate = date('Y') - 4;

            /**
             * Key Performance Indicators
             */
            $noOfEmptying = Emptying::whereNull('deleted_at')->whereRaw($whereRawEmptyingsServiceProvider)->distinct('application_id')->count('application_id');
            $noOfEmptyingReachedToTreatment = SludgeCollection::distinct('application_id')->whereRaw($whereRawSludgeServiceProvider)->whereNull('deleted_at')->count('application_id');

            $noOfFeedback = Feedback::whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider)->distinct('application_id')->count('application_id');
            $noOfPpeWear = $this->buildingdashboardService->getTotalFeedbackPpeWear();


            $keyPerformanceData = [];
            $keyPerformanceIndicators = KeyPerformanceIndicator::all()->pluck("target", "indicator");
            foreach ($keyPerformanceIndicators as $indicator => $target) {
                switch ($indicator) {
                    case 'Application Response Efficiency':
                        array_push($keyPerformanceData, [
                            "indicator" => $indicator,
                            "target" => $target,
                            "value" => $applicationCount == 0 ? "0" : ceil(($noOfEmptying / $applicationCount) * 100),
                            "icon" => '<i class="fa-solid fa-calendar-check"></i>'
                        ]);
                        break;
                    case 'Safe Desludging':
                        array_push($keyPerformanceData, [
                            "indicator" => $indicator,
                            "target" => $target,
                            "value" => $noOfEmptying == 0 ? "0" : ceil(($noOfEmptyingReachedToTreatment / $noOfEmptying) * 100),
                            "icon" => '<i class="fa-solid fa-house-circle-check"></i>'
                        ]);
                        break;
                    case 'Customer Satisfaction':
                        $satisfaction_data = DB::select("select count(*) AS total_count,sum(fsm_service_quality::int) as total_sum from fsm.feedbacks;");
                        $noOfFeedbackCategories = 3;
                        $noOfFeedbackRate = 5;
                        array_push($keyPerformanceData, [
                            "indicator" => $indicator,
                            "target" => $target,
                            "value" => $noOfFeedback == 0 ? "0" : (ceil($satisfaction_data[0]->total_sum / ($satisfaction_data[0]->total_count * $noOfFeedbackCategories)) / $noOfFeedbackRate) * 100,
                            "icon" => '<i class="fa-solid fa-users"></i>'
                        ]);
                        break;
                    case 'OHS Compliance(PPE)':
                        array_push($keyPerformanceData, [
                            "indicator" => $indicator,
                            "target" => $target,
                            "value" => ($noOfFeedback) == 0 ? "0" : ceil(($noOfPpeWear / $noOfFeedback) * 100),
                            "icon" => '<i class="fa-solid fa-user-shield"></i>'
                        ]);
                        break;
                }
            }
            $sanitationSystems = $this->buildingdashboardService->getBuildingSanitationSystem();
            $sanitationSystemsOthers = $this->buildingdashboardService->getBuildingSanitationSystemOthers();

            return view('dashboard.buildingDashboard', compact(
                'page_title',
                'buildingCount',
                'commercialBuildCount',
                'residentialBuildingCount',
                'mixedBuildCount',
                'containmentCount',
                'emptyingServiceCount',
                'serviceProviderCount',
                'sludgeCollectionsCount',
                'uniqueContainCodeEmptiedCount',
                'applicationCount',
                'buildingsPerWardChart',
                'numberOfEmptyingbyMonthsChart',
                'emptyingRequestsbyStructureTypesChart',
                'containmentTypesPerWardChart',
                'buildingFloorCountPerWard',
                'emptyingServicePerWardsChart',
                'emptyingServiceByTypeYearChart',
                'containmentEmptiedByWardChart',
                'containTypeChart',
                'buildingUseChart',
                'nextEmptyingContainmentsChart',
                'sludgeCollectionByTreatmentPlantChart',
                'fsmSrvcQltyChart',
                'ppe',
                'proposedEmptyingDateContainmentsChart',
                'proposedEmptiedDateContainmentsByWardChart',
                'maxDate',
                'minDate',
                'containmentTypesByBldgUsesChart',
                'monthlyAppRequestByoperators',
                'containmentTypesByBldgUsesResidentialsChart',
                'costPaidByOwnerWithReceipt',
                'costPaidByContainmentOwnerPerwardChart',
                'sludgeCollectionEmptyingServices',
                'treatmentPlantCount',
                'hotspotsPerWardChart',
                'sewerLengthPerWardChart',
                'keyPerformanceData',
                'industrialBuildingCount',
                'othersCount',
                'sanitationSystems',
                'sanitationSystemsOthers',
                'institutionBuildingCount',
                'institutionNames',
                'educationBuildingCount',
                'sanitationSystemOther',
                'sanitationSystemOthername'

            ));
        }
    }
}
