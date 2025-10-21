<?php
// Last Modified Date: 09-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)    
namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fsm\KeyPerformanceIndicator;
use Carbon\Carbon;
use App\Models\Fsm\Emptying;
use Illuminate\Support\Facades\Auth;
use App\Models\Fsm\SludgeCollection;
use App\Models\Fsm\Feedback;
use App\Models\Fsm\ServiceProvider;
use App\Models\Fsm\Application;
use App\Models\Fsm\KpiTarget;
use App\Services\Fsm\KpiDashboardService;
use Illuminate\Support\Facades\DB;
use PDF;
use App\Models\Fsm\Quarters;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;


class KpiDashboardController extends Controller
{
    protected KpiDashboardService $kpiDashboardService;

    
    /**
     * Constructor method for the class.
     * @param KpiDashboardService $kpiDashboardService The KpiDashboardService instance used for kpi-related operations.
     * @return void
     */
    public function __construct(KpiDashboardService $kpiDashboardService)
    {
        $this->kpiDashboardService = $kpiDashboardService;
    }

    /**
     * Calculate the total number of households using sanitation systems technologies up to the specified year.
     * 
     * @param int $year The specified year to calculate up to.
     * @return float The total number of households using sanitation systems.
     */
    public static function fscr($year)
    {
        // Calculate population per household ratio
        $populationPerHouseholdRatio = DB::table('building_info.buildings')
            ->selectRaw('SUM(population_served) / SUM(household_served) as population_per_household_ratio')
            ->value('population_per_household_ratio');

        // Count buildings with sanitation system technology excluded
        $countSanitationSystemIncluded = DB::table('building_info.buildings')
        ->whereRaw("EXTRACT(YEAR FROM construction_year) <= $year")
        ->whereNotIn('sanitation_system_id', [4]) 
        ->count();
    
        $countSanitationSystem_ptIncluded = DB::table('building_info.buildings')
        ->whereRaw("EXTRACT(YEAR FROM construction_year) <= $year")
            ->whereIn('sanitation_system_id', [4]) // 1 = 'Single Pit', 2='Cesspool/ Holding Tank', 9='Double Pit with Soak Away Pit'
            ->count();
      
        // Calculate septic tank usage
        $sum = $countSanitationSystemIncluded * $populationPerHouseholdRatio * 0.26  + $countSanitationSystem_ptIncluded * $populationPerHouseholdRatio * 0.26;
       
        return $sum;
    }

    /**
     * This function retrieves key performance data and cards for display based on optional parameters.
     *
     * @param int|null $select_year The selected year for filtering data (optional).
     * @param int|null $service_provider_id The ID of the service provider for filtering data (optional).
     * @return array An array containing key performance data and cards.
     */
    public function data($select_year= null, $service_provider_id= null)
    {
        $keyPerformanceData = [];
        $cards_kpi =[];
        
       // Retrieve unique years from KpiTarget model, sort them, and convert to an array
        $years = KpiTarget::pluck('year')->unique()->sort()->toArray(); 
        // Check if the logged-in user has roles related to service provider admin or help desk
        if(Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk'))
            {
                $service_provider_id = Auth::user()->service_provider_id;     
            }
        
        if(!empty($service_provider_id))
            {
                $whereServiceProviderID = "a.service_provider_id = " . $service_provider_id;
            }
        else
            {
                // If the service_provider_id is empty, set a condition that always evaluates to true
                $whereServiceProviderID = "1 = 1";
            }
        
        // Constructing the base query to retrieve data related to applications, emptyings, sludge collections, feedbacks, and service providers
        $base_query = DB::table('fsm.applications as a')
            ->leftJoin('fsm.emptyings as e', 'a.id', '=', 'e.application_id')
            ->leftJoin('fsm.sludge_collections as s', 'a.id', '=', 's.application_id')
            ->leftJoin('fsm.feedbacks as f', 'a.id', '=', 'f.application_id')
            ->leftJoin('fsm.service_providers as sp', 'a.service_provider_id', '=', 'sp.id')
            ->select(
                    'a.application_date',
                    'e.emptied_date',
                    'sp.id as service_provider_id',
                    'a.id as application_id',
                    'e.id as emptying_id',
                    's.id as sludge_collection_id',
                    'f.wear_ppe',
                    'f.fsm_service_quality as quality')
                
            ->whereRaw($whereServiceProviderID)
            ->whereNull('a.deleted_at');

        
        if($select_year !== null && $select_year !== "null")
            {
           
             /* ----------------------------------------------- For Cards ------------------------------------------------------------- */
                // Clone the base query for further use
                $query2 = clone $base_query;
                $query1 = clone $base_query;

                // Count the number of applications where the application date year matches the selected year
                $applicationCount = $query2->where(DB::raw('extract(year from a.application_date)'), $select_year)->count('a.id');

                // Count the number of emptying where the application date year matches the selected year
                $noOfEmptying= $query2->where(DB::raw('extract(year from a.application_date)'), $select_year)->count('e.application_id');
              
                // Count the number of emptying that reached to TP where the application date year matches the selected year
                $noOfEmptyingReachedToTreatment=$query2->where(DB::raw('extract(year from a.application_date)'), $select_year)->count('s.application_id');

                // Count the number of feedback where the application date year matches the selected year
                $noOfFeedback=$query2->where(DB::raw('extract(year from a.application_date)'), $select_year)->count('f.application_id');

                // Count the number of feedback where ppe is true and the application date year matches the selected year
                $noOfPpeWear=$query2->where(DB::raw('extract(year from a.application_date)'), $select_year)->where('f.wear_ppe',true)->count('f.application_id');

                // Count the number of feedback where service quality is true and the application date year matches the selected year
                $noOfFsmServiceQuality=$query1->where(DB::raw('extract(year from a.application_date)'), $select_year)->where('f.fsm_service_quality',true)->count('f.application_id'); 

                //Response Time
                // query to retrieve the average time between application date and emptied date  for each year,  where the application date year matches the selected year and optionally by service provider ID.
                $response = DB::select(DB::raw(" SELECT  EXTRACT(YEAR FROM a.application_date) AS year, AVG(AGE(e.emptied_date, a.application_date)) AS time FROM fsm.applications AS a JOIN fsm.emptyings AS e ON a.id = e.application_id WHERE    EXTRACT (YEAR FROM a.application_date) = $select_year and $whereServiceProviderID GROUP BY year "));

                // Inclusion
                // query to retrieve the total application count for buildings within low-income communities,  where the application date year matches the selected year and optionally by service provider
                $inclusion = DB::select(DB::raw("SELECT
                        SUM(application_count) AS total_application_count
                    FROM
                        (SELECT
                            COUNT(emptyings.application_id) AS application_count
                        FROM
                            building_info.buildings AS buildings
                        JOIN
                            layer_info.low_income_communities AS communities
                        ON
                            ST_Within(buildings.geom, communities.geom)
                        LEFT JOIN
                            fsm.emptyings AS emptyings
                        ON
                            emptyings.application_id IN (
                                SELECT a.id
                                FROM fsm.applications AS a
                                WHERE buildings.bin = a.bin and EXTRACT(year FROM a.application_date) = $select_year and  $whereServiceProviderID
                            )
                        GROUP BY
                            buildings.geom) AS subquery;
                    "));
                $inclusionValue = $inclusion[0]->total_application_count;

                //FSCR
                $fscr = $this->fscr($select_year);
                
                // query to retrieve the total volume of sludge emptied,  where the application date year matches the selected year and optionally by service provider
                $sludgeCount = DB::select(DB::raw("
                            SELECT
                                SUM(volume_of_sludge) AS sCount
                            FROM
                                fsm.emptyings
                            LEFT JOIN
                                fsm.applications as a ON a.id = emptyings.application_id
                            WHERE
                                EXTRACT(YEAR FROM a.application_date) = $select_year and $whereServiceProviderID
                        "));

                // Query to retrieve key performance indicators along with their targets for the selected year
                $kpiResults = DB::table('fsm.key_performance_indicators AS i')->leftJoin('fsm.kpi_targets AS t', 'i.id', '=', 't.indicator_id')->select('i.indicator', 'i.id', 't.target', 't.year')->where('t.year', '=', $select_year)->whereNull('t.deleted_at')->orderBy('i.indicator')->get();
        
                foreach ($kpiResults as $result) 
                {
                    $indicator = $result->indicator;
                    $target = $result->target ?? 0;
                
                    $commonStructure = [
                        "indicator" => $indicator,
                        "target" => $target,
                    ];
            
                    switch ($indicator) 
                    {
                        case 'Application Response Efficiency':
                            $commonStructure["icon"] = '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(public_path("img/svg/Application Response.svg"))) . '">';
                            $commonStructure["achievement"] = $applicationCount == 0 ? 0 : ceil(($noOfEmptying / $applicationCount) * 100);
                            break;
                        case 'Customer Satisfaction':
                            $commonStructure["icon"] ='<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(public_path("img/svg/customer satisfaction.svg"))) . '">';
                            $commonStructure["achievement"] = $noOfFeedback == 0 ? 0 : ceil(( $noOfFsmServiceQuality/ $noOfFeedback) * 100);
                            break;
                        case 'PPE Compliance':
                            $commonStructure["icon"] ='<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(public_path("img/svg/ppe.svg"))) . '">';
                            $commonStructure["achievement"] = $noOfFeedback == 0 ? 0: ceil(($noOfPpeWear / $noOfFeedback) * 100);
                            break;
                        case 'Safe Desludging':
                            $commonStructure["icon"] ='<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(public_path("img/svg/safe Desludging.svg"))) . '">';
                            $commonStructure["achievement"] = ( $noOfEmptying == 0) ? 0 : ceil(($noOfEmptyingReachedToTreatment /  $noOfEmptying) * 100);
                            break;
                        case 'Inclusion':
                            $commonStructure["icon"] = '<i class="fa-solid fa-people-group"></i>'; 
                            $commonStructure["achievement"] = ($applicationCount == 0) ? 0 : ceil(($inclusionValue / $applicationCount) * 100);
                            break;
                        case 'Faecal Sludge Collection Ratio (FSCR)':
                            $commonStructure["icon"] = '<i class="fa-solid fa-percent"></i>'; 
                            if (!empty($sludgeCount)) {
                                $commonStructure["achievement"] = ceil((($sludgeCount[0]->scount)/ $fscr)* 100);
                            } else {
                                $commonStructure["achievement"] = 0;
                            }
                            break;    
                        case 'Response Time':
                            $commonStructure["icon"] = '<i class="fa-regular fa-clock"></i>'; 
                            if (!empty($response)) {
                    
                                $time = Carbon::parse($response[0]->time);
                                $total_hours = $time->diffInHours();
                                
                                $commonStructure["achievement"] = $total_hours ? $total_hours : 0;
                            } else {
                                $commonStructure["achievement"] = 0;
                            }
                                    break;    
                    }    
                    
                    $cards_kpi[] = $commonStructure;
                }
               
       
              /* -----------------------------------------------------------------  For Quarters  ------------------------------------------------------------------------- */
                
                $quarterCounts = []; 
                $quarters = Quarters::where('year', '=', $select_year)->get();
                foreach($quarters as $quarter)
                {
                
                    // Constructing the query to retrieve data related to applications, emptyings, sludge collections, feedbacks, and service providers based on the selected year, and the specified quarter
                    $base_query1 = DB::table('fsm.applications as a')
                    ->leftJoin('fsm.emptyings as e', 'a.id', '=', 'e.application_id')
                    ->leftJoin('fsm.sludge_collections as s', 'a.id', '=', 's.application_id')
                    ->leftJoin('fsm.feedbacks as f', 'a.id', '=', 'f.application_id')
                    ->select(
                        'a.application_date', 
                        'e.emptied_date', 
                        'a.id as application_id',
                         'e.id as emptying_id', 
                         's.id as sludge_collection_id',
                          'f.wear_ppe', 
                          'f.fsm_service_quality as quality')
                          ->whereRaw($whereServiceProviderID)
                          ->whereYear('application_date','=', $select_year)
                          ->whereBetween('application_date', [$quarter->starttime, $quarter->endtime])
                            ->orWhereDate('application_date', '=', $quarter->starttime)
                            ->orWhereDate('application_date', '=', $quarter->endtime)
                    ->whereNull('a.deleted_at');

                     $base_query2 = clone $base_query1;


                    //Response Time
                    // query to retrieve the average time between application date and emptied date  for each year,  where the application date year matches the specified quarter and optionally by service provider ID.
                    $time = DB::select(DB::raw(" SELECT AVG(AGE(e.emptied_date, a.application_date)) AS time FROM fsm.applications AS a
                            JOIN fsm.emptyings AS e ON a.id = e.application_id WHERE EXTRACT(YEAR FROM a.application_date) = $select_year
                            AND ( (a.application_date BETWEEN '$quarter->starttime' AND '$quarter->endtime')
                                OR (DATE(a.application_date) = '$quarter->starttime')
                                OR  (DATE(a.application_date) = '$quarter->endtime') )
                            AND $whereServiceProviderID
                        "));
                    $response = $time[0]->time;

                    //Inclusion
                     // query to retrieve the total application count for buildings within low-income communities,  where the application date year matches the specified quarter and optionally by service provider
                    $inclusion = DB::select(DB::raw("SELECT SUM(application_count) AS total_application_count
                        FROM (SELECT COUNT(emptyings.application_id) AS application_count
                            FROM building_info.buildings AS buildings
                            JOIN  layer_info.low_income_communities AS communities
                            ON ST_Within(buildings.geom, communities.geom)
                            LEFT JOIN fsm.emptyings AS emptyings
                            ON emptyings.application_id IN ( SELECT a.id FROM fsm.applications AS a WHERE buildings.bin = a.bin and EXTRACT(year FROM a.application_date) = $select_year and $whereServiceProviderID  AND (
                                (a.application_date BETWEEN '$quarter->starttime' AND '$quarter->endtime')
                                OR (DATE(a.application_date) = '$quarter->starttime')
                                OR (DATE(a.application_date) = '$quarter->endtime')
                            ))  GROUP BY buildings.geom) AS subquery"));
                    $inclusionValue = $inclusion[0]->total_application_count;

                    //FSCR
                    $fscr = $this->fscr($select_year);

                    // query to retrieve the total volume of sludge emptied,  where the application date year matches  the specified quarter and optionally by service provider
                    $sludgeCount = DB::select(DB::raw("SELECT SUM(volume_of_sludge) AS sCount
                                    FROM fsm.emptyings  LEFT JOIN fsm.applications as a ON a.id = emptyings.application_id
                                    WHERE  EXTRACT(YEAR FROM a.application_date) = $select_year and $whereServiceProviderID AND (
                                    (a.application_date BETWEEN '$quarter->starttime' AND '$quarter->endtime')
                                                OR(DATE(a.application_date) = '$quarter->starttime')
                                                OR(DATE(a.application_date) = '$quarter->endtime'))
                                    "));
                    $sludgeCount = $sludgeCount[0]->scount;

                    // Count the number of applications 
                    $applicationCount = $base_query2->count('a.id');

                    // Count the number of emptying 
                    $noOfEmptying= $base_query2->count('e.application_id');

                    // Count the number of emptying that reached to TP
                    $noOfEmptyingReachedToTreatment= $base_query2->count('s.application_id');

                     // Count the number of feedback
                    $noOfFeedback= $base_query2->count('f.application_id');

                     // Count the number of feedback where ppe is true
                    $noOfPpeWear= $base_query2->where('f.wear_ppe',true)
                                        ->count('f.application_id');
                    
                    // Count the number of feedback where service quality is true
                    $noOfFsmServiceQuality=$base_query1->where('f.fsm_service_quality',true)
                                        ->count('f.application_id'); 
                        
                    $quarterCounts[$quarter->quarterid] = [
                        'response' => $response,
                        'applicationCount' => $applicationCount,
                        'noOfEmptying' => $noOfEmptying,
                        'noOfEmptyingReachedToTreatment' => $noOfEmptyingReachedToTreatment,
                        'noOfFeedback' => $noOfFeedback,
                        'noOfPpeWear' => $noOfPpeWear,
                        'noOfFsmServiceQuality' => $noOfFsmServiceQuality,
                        'inclusionValue' => $inclusionValue,
                        'fscr' => $fscr,
                        'sludgeCount' => $sludgeCount
                    ];
                
                }

                  // Query to retrieve key performance indicators along with their targets and quarter name for the selected year
                $query = "SELECT t.target,q.quartername, k.indicator,q.quarterid FROM
                fsm.kpi_targets t LEFT JOIN fsm.quarters q ON t.year = q.year LEFT JOIN fsm.key_performance_indicators k ON t.indicator_id = k.id WHERE t.year = $select_year AND t.deleted_at IS NULL ORDER BY  t.year, k.indicator,q.quarterid ; ";
        
                $kpiResults = DB::select($query);
            
                $keyPerformanceData = [];
                foreach ($kpiResults as $result) 
                {
                    $name = $result->quartername;
                    $indicator = $result->indicator;
                    $target = $result->target ?? 0;
        
                    $commonStructure = [
                        "year" => $select_year,
                        "quartername" => $name,
                        "indicator" => $indicator,
                        "target" => $target,
                        "serviceprovider" => $service_provider_id,
                    ];
    
                    switch ($indicator) 
                    {
                        case 'Application Response Efficiency':
                            $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['applicationCount'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['noOfEmptying'] / $quarterCounts[$result->quarterid]['applicationCount']) * 100);
                            break;
                        case 'Customer Satisfaction':
                            $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['noOfFeedback'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['noOfFsmServiceQuality'] / $quarterCounts[$result->quarterid]['noOfFeedback']) * 100);
                            break;
                        case 'PPE Compliance':
                            $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['noOfFeedback'] == 0) ? 0: ceil(($quarterCounts[$result->quarterid]['noOfPpeWear'] / $quarterCounts[$result->quarterid]['noOfFeedback']) * 100);
                            break;
                        case 'Safe Desludging':
                            $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['noOfEmptying'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['noOfEmptyingReachedToTreatment'] / $quarterCounts[$result->quarterid]['noOfEmptying']) * 100);
                            break;
                        case 'Inclusion':
                            $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['applicationCount'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['inclusionValue'] /$quarterCounts[$result->quarterid]['applicationCount']) * 100);
                            break;
                        case 'Faecal Sludge Collection Ratio (FSCR)':
                                $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['fscr'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['sludgeCount'] /$quarterCounts[$result->quarterid]['fscr']) * 100);
                            break;  
                        
                        case 'Response Time':
                    
                            $time = Carbon::parse($quarterCounts[$result->quarterid]['response']);
                            $total_hours = $time->diffInHours();
                            $commonStructure["achievement"] = $total_hours?$total_hours :0;
                            break;    
                    }
                    $keyPerformanceData[] = $commonStructure;
                }
          
            }
        else
            {
                $yearlyCounts = []; 
                $kpiResultsByYear = [];
                        
                foreach($years as $year)
                    {
                         // Clone the base query for further use
                        $base_query2 = clone $base_query;
                        $base_query1 = clone $base_query;

                        // Count the number of applications where the application date year matches the years
                        $applicationCount = $base_query2->where(DB::raw('extract(year from a.application_date)'), $year)->count('a.id');

                        // Count the number of emptying where the application date year matches the years
                        $noOfEmptying= $base_query2->where(DB::raw('extract(year from a.application_date)'), $year)->count('e.application_id');

                        // Count the number of emptying that reached to TP where the application date year matches the years
                        $noOfEmptyingReachedToTreatment=$base_query2->where(DB::raw('extract(year from a.application_date)'), $year)->count('s.application_id');

                        // Count the number of feedback where the application date year matches the years
                        $noOfFeedback=$base_query2->where(DB::raw('extract(year from a.application_date)'), $year)->count('f.application_id');

                        // Count the number of feedback where ppe is true and the application date year matches the years
                        $noOfPpeWear=$base_query2->where(DB::raw('extract(year from a.application_date)'), $year)->where('f.wear_ppe',true)->count('f.application_id');

                        // Count the number of feedback where service quality is true and the application date year matches the years
                        $noOfFsmServiceQuality=$base_query1->where(DB::raw('extract(year from a.application_date)'), $year)->where('f.fsm_service_quality',true)->count('f.application_id'); 

                        //Response Time
                        // query to retrieve the average time between application date and emptied date  for each year,  where the application date year matches the years and optionally by service provider ID.
                        $response = DB::select(DB::raw(" SELECT  EXTRACT(YEAR FROM a.application_date) AS year, AVG(AGE(e.emptied_date, a.application_date)) AS time FROM fsm.applications AS a JOIN fsm.emptyings AS e ON a.id = e.application_id WHERE EXTRACT (YEAR FROM a.application_date) = $year and $whereServiceProviderID GROUP BY year "));
                    
                        //Inclusion
                         // query to retrieve the total application count for buildings within low-income communities,  where the application date year matches the year and optionally by service provider
                        $inclusion = DB::select(DB::raw("SELECT SUM(application_count) AS total_application_count
                            FROM (SELECT COUNT(emptyings.application_id) AS application_count FROM building_info.buildings AS buildings JOIN layer_info.low_income_communities AS communities
                            ON ST_Within(buildings.geom, communities.geom) LEFT JOIN
                            fsm.emptyings AS emptyings  ON emptyings.application_id IN ( SELECT a.id FROM fsm.applications AS a
                                WHERE buildings.bin = a.bin and EXTRACT(year FROM a.application_date) = $year and $whereServiceProviderID
                            ) GROUP BY buildings.geom) AS subquery; "));
                        $inclusionValue = $inclusion[0]->total_application_count;

                        //FSCR
                        $fscr = $this->fscr($year);

                        // query to retrieve the total volume of sludge emptied,  where the application date year matches the year and optionally by service provider
                        $sludgeCount = DB::select(DB::raw(" SELECT SUM(volume_of_sludge) AS sCount  FROM fsm.emptyings LEFT JOIN fsm.applications as a ON a.id = emptyings.application_id
                            WHERE EXTRACT(YEAR FROM a.application_date) = $year  and $whereServiceProviderID "));
                        $sludgeCount = $sludgeCount[0]->scount;

                        $yearlyCounts[$year] = [
                            'applicationCount' => $applicationCount,
                            'noOfEmptying' => $noOfEmptying,
                            'noOfEmptyingReachedToTreatment' => $noOfEmptyingReachedToTreatment,
                            'noOfFeedback' => $noOfFeedback,
                            'noOfPpeWear' => $noOfPpeWear,
                            'noOfFsmServiceQuality' => $noOfFsmServiceQuality,
                            'inclusionValue' => $inclusionValue,
                            'fscr'=> $fscr,
                            'response'=>$response,
                            'sludgeCount' =>$sludgeCount
                        ];
                    }
                // Query to retrieve key performance indicators along with their targets for the  years
                $kpiResults = DB::table('fsm.key_performance_indicators AS i')
                    ->leftJoin('fsm.kpi_targets AS t', 'i.id', '=', 't.indicator_id')
                    ->select('i.indicator', 'i.id', 't.target', 't.year')->get(); 

                foreach ($kpiResults as $result) 
                    {
                        $indicator = $result->indicator;
                        $target = $result->target ?? 0;
                        $resultYear = $result->year;

                            if (isset($yearlyCounts[$resultYear])) 
                                {
                                    $commonStructure = [
                                        "year" => $resultYear,
                                        "indicator" => $indicator,
                                        "target" => $target,
                                        "serviceprovider" => $service_provider_id,
                                    ];
                    
                                    switch ($indicator) 
                                    {
                                        case 'Application Response Efficiency':
                                            $commonStructure["achievement"] = $yearlyCounts[$resultYear]['applicationCount'] == 0 ? 0 : ceil(($yearlyCounts[$resultYear]['noOfEmptying'] / $yearlyCounts[$resultYear]['applicationCount']) * 100);
                                            break;
                                        case 'Customer Satisfaction':
                                            $commonStructure["achievement"] = $yearlyCounts[$resultYear]['noOfFeedback'] == 0 ? 0 : ceil(($yearlyCounts[$resultYear]['noOfFsmServiceQuality'] / $yearlyCounts[$resultYear]['noOfFeedback']) * 100);
                                            break;
                                        case 'PPE Compliance':
                                            $commonStructure["achievement"] = $yearlyCounts[$resultYear]['noOfFeedback'] == 0 ? 0: ceil(($yearlyCounts[$resultYear]['noOfPpeWear'] / $yearlyCounts[$resultYear]['noOfFeedback']) * 100);
                                            break;
                                        case 'Safe Desludging':
                                            $commonStructure["achievement"] = ($yearlyCounts[$resultYear]['noOfEmptying'] == 0) ? 0 : ceil(($yearlyCounts[$resultYear]['noOfEmptyingReachedToTreatment'] / $yearlyCounts[$resultYear]['noOfEmptying']) * 100);
                                            break;
                                        case 'Inclusion':
                                            $commonStructure["icon"] = '<i class="fa-solid fa-house-circle-check"></i>'; 
                                            $commonStructure["achievement"] = ($yearlyCounts[$resultYear]['applicationCount'] == 0) ? 0 : ceil(($yearlyCounts[$resultYear]['inclusionValue'] / $yearlyCounts[$resultYear]['applicationCount']) * 100);
                                            break;
                                        case 'Faecal Sludge Collection Ratio (FSCR)':
                                            $commonStructure["icon"] = '<i class="fa-solid fa-house-circle-check"></i>'; 
                                                $commonStructure["achievement"] = ($yearlyCounts[$resultYear]['fscr'] == 0)? 0 : ceil(($yearlyCounts[$resultYear]['sludgeCount']/ $yearlyCounts[$resultYear]['fscr'])* 100);
                                                break;    
                                        case 'Response Time':
                                            $commonStructure["icon"] = '<i class="fa-solid fa-house-circle-check"></i>'; 

                                            if (!empty($yearlyCounts[$resultYear]['response'])) {
                                                // Extract the response from the array
                                                $response = $yearlyCounts[$resultYear]['response'];
                                                $firstResponse = $response[0];
                                                $time = Carbon::parse($firstResponse->time);
                                                $total_hours = $time->diffInHours();
                                                $commonStructure["achievement"] = $total_hours;
                                            } else {
                                                $commonStructure["achievement"] = 0;
                                            }
                                            
                                                break;    
                                    }   
                                    $keyPerformanceData[] = $commonStructure;
                                }

                    }
       
            }

            return [ $keyPerformanceData,  $cards_kpi];
    }

    /**
     * Display the key performance indicators (KPIs) dashboard.
     *
     * @param int|null $select_year
     * @param int|null $service_provider_id
     * @return \Illuminate\View\View
     */
    public function index($select_year= null, $service_provider_id= null)
    {
        $page_title = __("Key Performance Indicators (KPIs) Dashboard");
          // Fetch unique years for filtering
          $years = KpiTarget::pluck('year')->unique()->sortDesc();
        $year = request()->input('year', '');
         // Fetch the company name of the logged-in service provider
        $company_name = ServiceProvider::where('id', Auth::user()->service_provider_id)->value('company_name');
        // Fetch all operational service providers 
        $serviceProviders = ServiceProvider::Operational()->orderBy('id')->pluck('company_name', 'id');
        // Fetch key performance data based on selected year and service provider
        $keyPerformanceData = $this->data(request()->year, request()->service_provider );
   
            // Fetch charts data based on the selected year
        if($year != '')
        {
            $applicationResponseEfficiencyCharts = $this->kpiDashboardService->getApplicationResponseEfficiencyQuarter( $keyPerformanceData);
            $safeDesludgingCharts = $this->kpiDashboardService->getSafeDesludgingQuarter( $keyPerformanceData);
            $ppeComplianceCharts = $this->kpiDashboardService->getPpeComplianceQuarter( $keyPerformanceData);
            $customerSatisfactionCharts = $this->kpiDashboardService->getcustomerSatisfactionQuarter( $keyPerformanceData);
            $fscrCharts = $this->kpiDashboardService->getFscrChartsQuarter( $keyPerformanceData);
            $responseTimeCharts = $this->kpiDashboardService->getResponseTimeQuarter( $keyPerformanceData);
            $inclusionCharts = $this->kpiDashboardService->getInclusionQuarter( $keyPerformanceData);
        }
        else
        {
            $applicationResponseEfficiencyCharts = $this->kpiDashboardService->getApplicationResponseEfficiency( $keyPerformanceData);
            $safeDesludgingCharts = $this->kpiDashboardService->getSafeDesludging( $keyPerformanceData);
            $customerSatisfactionCharts = $this->kpiDashboardService->getCustomerSatisfaction( $keyPerformanceData);
            $ppeComplianceCharts = $this->kpiDashboardService->getPpeCompliance( $keyPerformanceData);
            $fscrCharts = $this->kpiDashboardService->getFscr( $keyPerformanceData);
            $responseTimeCharts = $this->kpiDashboardService->getResponseTime( $keyPerformanceData);
            $inclusionCharts = $this->kpiDashboardService->getInclusion( $keyPerformanceData);
        }
        return view('fsm/kpi-dashboard.index', compact('page_title','keyPerformanceData','years', 'year','serviceProviders', 'company_name',
            'applicationResponseEfficiencyCharts',
        'safeDesludgingCharts','customerSatisfactionCharts', 'ppeComplianceCharts', 'fscrCharts', 'responseTimeCharts', 'inclusionCharts' ));

    }


    /**
     * Generate a report based on the selected year and service provider.
     *
     * @param string|null $select_year The selected year, or null if not specified.
     * @param string|null $service_provider_id The ID of the service provider, or null if not specified.
     * @return \Illuminate\Http\Response A downloadable PDF report.
     */
    public function generateReport($select_year, $service_provider_id)
    {
        // If select_year or service_provider_id is "null", set them to actual null values.
        if($select_year == "null")
        {
            $select_year = null;
        }
        if($service_provider_id == "null")
        {
            $service_provider_id = null;
        }
      
         // Fetch key performance data based on the selected year and service provider.
        $keyPerformanceData= $this->data($select_year , $service_provider_id );
        $keyPerformanceData = $keyPerformanceData[0];
        $distinctYears = [];
        foreach ($keyPerformanceData as $data) 
        {
            if (isset($data['year'])) {
                $distinctYears[$data['year']] = true;
            }
        }
        $distinctYears = array_keys($distinctYears);
        $distinctKpi = [];
        foreach ($keyPerformanceData as $data) 
        {
            if (isset($data['indicator'])) {
                $distinctKpi[$data['indicator']] = true;
            }
        }
        $distinctKpi = array_keys($distinctKpi);
         // Generate and download a PDF report based on the fetched data.
       return PDF::loadView('fsm.kpi-dashboard.kpiReport',compact('keyPerformanceData', 'distinctYears','distinctKpi'))->download('KPI Report.pdf');
    }   

  
    public function storekpi()
    {
        $year = date('Y');

        // Check if quarters for the current year already exist
        $existingQuarters = DB::table('fsm.quarters')
            ->where('year', $year)
            ->exists();

        if ($existingQuarters) {
            Log::info("Quarters for year $year already exist.");
            return ;
        }
        try {
            DB::statement("
                INSERT INTO fsm.quarters (quartername, starttime, endtime, year)
                SELECT
                    'Q' || row_number() OVER (ORDER BY start_date) AS quartername,
                    start_date AS starttime,
                    (start_date + INTERVAL '3 months - 1 day') + INTERVAL '23 hours 59 minutes 59 seconds' AS endtime,
                    $year AS year
                FROM (
                    SELECT
                        (start_date)::DATE AS start_date
                    FROM generate_series(
                        '$year-01-01'::DATE,
                        '$year-10-01'::DATE,
                        INTERVAL '3 months'
                    ) AS start_date
                ) AS quarters;
            ");

            return ;

        } catch (\Exception $e) {
            Log::error('Database error: ' . $e->getMessage());
            return ;
        }
    }
}