<?php
// Last Modified: 2026-05-01
// Developed By: Streams Tech Ltd.
// Description: Controller for building CRUD operations and related data endpoints.

namespace App\Http\Controllers\BuildingInfo;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use App\Models\BuildingInfo\SanitationSystemTechnology;
use App\Models\BuildingInfo\StructureType;
use App\Models\BuildingInfo\FunctionalUse;
use App\Models\BuildingInfo\UseCategory;
use App\Models\BuildingInfo\Building;
use App\Models\BuildingInfo\Household;
use App\Models\BuildingInfo\Owner;
use App\Models\BuildingInfo\SanitationSystem;
use App\Models\Fsm\Containment;
use App\Models\Fsm\ContainmentType;
use App\Helpers\KeywordMatcher;

use App\Models\Fsm\Ctpt;
use App\Models\UtilityInfo\Drain;
use App\Models\BuildingInfo\BuildContain;
use App\Models\BuildingInfo\WaterSource;
use App\Models\UtilityInfo\SewerLine;
use App\Models\UtilityInfo\Roadline;
use App\Models\LayerInfo\Ward;
use App\Models\LayerInfo\Lic;
use App\Models\UtilityInfo\WaterSupplys;
use App\Models\Fsm\ContaimentType;
use App\Models\TaxPaymentInfo\TaxPayment;
use App\Models\TaxPaymentInfo\TaxPaymentStatus;
use App\Enums\LicStatus;
use App\Services\BuildingInfo\BuildingFormDataService;
use App\Services\BuildingInfo\BuildingStructureService;
use App\Http\Requests\BuildingInfo\BuildingRequest;
use DOMDocument;
use DomXpath;
use DB;
use Redirect;
use Carbon\Carbon;
use App\Models\Fsm\BuildToilet;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected BuildingStructureService $buildingStructureService;
    protected BuildingFormDataService $buildingFormDataService;
    private $points;

    public function __construct(
        BuildingStructureService $buildingStructureService,
        BuildingFormDataService $buildingFormDataService
    )
    {
        $this->middleware('auth');
        $this->middleware('permission:List Building Structures', ['only' => ['index']]);
        $this->middleware('permission:View Building Structure', ['only' => ['show']]);
        $this->middleware('permission:Add Building Structure', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Building Structure', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Building Structure', ['only' => ['destroy']]);
        $this->middleware('permission:Export Building Structures', ['only' => ['export']]);
        /**
         * creating a service class instance
         */
        $this->buildingStructureService = $buildingStructureService;
        $this->buildingFormDataService = $buildingFormDataService;
    }
    public function getData(Request $request)
    {
        return ($this->buildingStructureService->fetchData($request));
    }
    public function index()
    {
        $page_title = __("Buildings");

        $structure_type = StructureType::orderBy('type', 'asc')->pluck('type', 'id')->all();
        $water_sources = WaterSource::orderBy('source', 'asc')->pluck('source', 'id')->all();

        $sanitation_systems = SanitationSystem::orderBy('sanitation_system', 'asc')->whereNotIn('id', [11])->pluck('sanitation_system', 'id')->all();

        $functional_use = FunctionalUse::orderBy('name')->pluck('name', 'id')->all();
        $floorCount = Building::select('floor_count')
            ->whereNotNull('floor_count')
            ->whereNull('deleted_at')
            ->orderBy('floor_count', 'asc')
            ->pluck('floor_count', 'floor_count');
        $ward = Ward::orderBy('ward', 'asc')->pluck('ward', 'ward')->all();
        $toiletPresence =  Building::pluck('toilet_status')->get('*');
        // Capitalize the first letter of each word in the arrays
        $structure_type = array_map('ucwords', $structure_type);
        $water_sources = array_map('ucwords', $water_sources);
        return view('building-info.buildings.index', compact('page_title', 'structure_type', 'functional_use', 'sanitation_systems', 'water_sources', 'ward', 'toiletPresence', 'floorCount'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = __("Add Building");
        $formData = $this->buildingFormDataService->getCreateFormData();
        return view('building-info.buildings.create', array_merge(
            ['page_title' => $page_title],
            $formData
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BuildingRequest $request)
    {
        return ($this->buildingStructureService->storeBuildingData($request));
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $page_title = __("Building Details");

        $building = Building::find($id);
        $statusLIH = LicStatus::getDescription($building->is_lih);
        $status = LicStatus::getDescription($building->is_lic);
        $containment = $building->containments[0] ?? null;
        $folderPathJpg = public_path('/storage/emptyings/houses/'. $building->bin . '.jpg');
        $folderPathJpeg = public_path('/storage/emptyings/houses/'. $building->bin . '.jpgeg');
        $imagePathJpg = 'storage/emptyings/houses/' . $building->bin . '.jpg';
        $imagePathJpeg = 'storage/emptyings/houses/' . $building->bin . '.jpeg';
        if(file_exists($folderPathJpg) == true)
        {
            $imageSrc = asset($imagePathJpg);
        }
        elseif(file_exists($folderPathJpeg) == true)
        {
            $imageSrc = asset($imagePathJpeg);
        }
        else
        {
            $imageSrc = false;
        }
        if ($building) {
            return view('building-info.buildings.show', compact('page_title', 'building', 'containment', 'status', 'statusLIH','imageSrc'));
        } else {
            return view('errors.404');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $page_title = __("Edit Building");
        $editData = $this->buildingFormDataService->getEditFormData($id);
        if (!$editData) {
            abort(404);
        }

        return view('building-info.buildings.edit', array_merge(
            ['page_title' => $page_title],
            $editData
        ));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(BuildingRequest $request, $id)
    {
        return ($this->buildingStructureService->updateBuildingData($request, $id));
    }
    public function history($id)
    {
        $building = Building::find($id);
        if ($building) {
            $page_title =  __("Building History");
            return view('building-info.buildings.history', compact('page_title', 'building'));
        } else {
            abort(404);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $building = Building::find($id);
        if ($building) {
            if ($building->containments()->exists()) {
                return redirect('building-info/buildings')->with('error', __("Failed to delete Building, it is associated with Containment Information"));
            } else {
                DB::transaction(function () use ($building) {
                    if (!empty($building->tax_code)) {
                        TaxPayment::where('tax_code', $building->tax_code)->delete();
                        TaxPaymentStatus::where('tax_code', $building->tax_code)->delete();
                    }

                    $building->delete();
                });

                return redirect('building-info/buildings')->with('success', __("Building Deleted Successfully"));
            }
        } else {
            return redirect('building-info/buildings')->with('error', __("Failed to Delete Building"));
        }
    }
    public function export()
    {
        return ($this->buildingStructureService->fetchExport());
    }
    public function getHouseNumbers()
    {
        return ($this->buildingStructureService->fetchHouseNumber());
    }

    //counts the total number of building on the road using road code ,using ajax the house address value if provided
    public function checkHouse(Request $request)
    {
        $roadCode = $request->input('road_code');
        $buildingCount = Building::where('road_code', $roadCode)->count();
        return response()->json([
            'exists' => $buildingCount > 0,
            'count' => $buildingCount,
        ]);
    }



    public function getHouseNumbersAll()
    {
        return ($this->buildingStructureService->fetchHouseNumberAll());
    }
    public function listContainments($id)
    {
        $building = Building::find($id);

        if ($building) {
            $title = __('Containments Connected to Building') . ': ' . $building->bin;
            $containments = $building->containments->toArray();

            $popContentsHtml = empty($containments)
                ? __('No Containment Found.')
                : $this->popUpContentHtml($containments);

            return [
                'title' => $title,
                'popContentsHtml' => $popContentsHtml,
            ];
        }
    }

    public function popUpContentHtml($containments)
    {
        $tbody = '<tbody>';
        foreach ($containments as $row1) {
            $tbody .= '<tr>';
            $tbody .= '<td>' . $row1['id'] . '</td>';
            $tbody .= '<td>' . $row1['containment_type']['type'] . '</td>';
            $tbody .= '<td class="text-center">
                        <a title="' . __('Containment Detail') . '"
                           href="' . action("Fsm\ContainmentController@show", ['containment' => $row1['id']]) . '"
                           class="btn btn-info btn-sm mb-1">
                           <i class="fa fa-info-circle" aria-hidden="true"></i>
                        </a>
                       </td>';
            $tbody .= '</tr>';
        }
        $tbody .= '</tbody>';

        $thead = '<thead>';
        $thead .= '<tr>';
        $thead .= '<th>' . __('Containment ID') . '</th>';
        $thead .= '<th>' . __('Containment Type') . '</th>';
        $thead .= '<th>' . __('Actions') . '</th>';
        $thead .= '</tr>';
        $thead .= '</thead>';

        $html = '<table class="table table-bordered">';
        $html .= $thead;
        $html .= $tbody;
        $html .= '</table>';

        return $html;
    }


    public function getContainmentTypes(Request $request)
    {
        $sanitationSystemId = $request->input('sanitation_system_id');
        $containmentTypes = ContainmentType::where('sanitation_system_id', $sanitationSystemId)->get();
        return response()->json($containmentTypes);
    }

    public function getCTPTHouseNumbers ()
    {
        return ($this->buildingStructureService->fetchCTPTHouseNumber());
    }


    public function getUseCategories($functionalUseId)
    {
        $useCategories = UseCategory::where('functional_use_id', $functionalUseId)
            ->orderBy('id')
            ->pluck('name','id');

        return response()->json($useCategories);
    }

    public function getSanitationSystem()
    {
        $building = Building::find(request()->bin);

        $sewer_code = $building->sewer_code ?? "No Sewer Code";
        $drain_code = $building->drain_code ?? "No Drain Code";
        $containment_ids = implode(',',$building->containments()->get()->pluck('id')->toArray()) ?? "No Containment Connected";
        $containment_infos = [];
        foreach($building->containments()->get() as $containment)
        {
            array_push($containment_infos, $containment->containmentType->type . " (" . $containment->id .") <br>");
        }
        $containments = $containment_infos ? implode('',$containment_infos) : "No Containment Connected<br>";
        $data = "Building Toilet Connection:" . $building->SanitationSystem->sanitation_system . "<br> Containment Info:<br>".  $containments . "Drain Code: " . $drain_code ."<br>Sewer Code:" . $sewer_code;
        if ($building) {
            return response()->json([
                'success' => true,
                'data' => $data,
                ]);
        }
    }

    public function getBuildingHouseholds($bin)
    {
        $households = Household::whereNull('deleted_at')->where('bin', $bin)->get();
        $format = request()->query('format', 'html');
        if ($households->isEmpty() && $format == 'json') {
             return response()->json(['message' => 'Household not found for the provided BIN.'], 404);
        }

        if($format == 'html') {
            return view('building-info.households.partials.household_info', compact('households'))->render();
        }

        return response()->json($households);
    }

    /**
     * Searchable, paginated household options for the building form's
     * SWM household select2 (server-side source). Returns the select2
     * JSON shape: { results: [{id, text}], pagination: { more } }.
     */
    public function getHouseholdOptions(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $page = max(1, (int) $request->query('page', 1));
        $limit = 15;

        $query = Household::query()->whereNull('deleted_at');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('household_id', 'ilike', '%'.$search.'%')
                    ->orWhere('household_owner_name', 'ilike', '%'.$search.'%');
            });
        }

        $total = $query->count();
        $households = $query
            ->orderBy('household_id')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get(['household_id', 'household_owner_name']);

        $results = $households->map(fn ($h) => [
            'id' => $h->household_id,
            'text' => $h->household_owner_name
                ? $h->household_id.' - '.$h->household_owner_name
                : (string) $h->household_id,
        ])->all();

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $page * $limit < $total],
        ]);
    }

    public function getLicOptions(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $page = max(1, (int) $request->query('page', 1));
        $limit = 15;

        $query = Lic::query()->whereNull('deleted_at');
        if ($search !== '') {
            $query->where('community_name', 'ilike', '%'.$search.'%');
        }

        $total = $query->count();
        $lics = $query
            ->orderBy('community_name')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get(['id', 'community_name']);

        $results = $lics->map(fn ($lic) => [
            'id' => $lic->id,
            'text' => (string) $lic->community_name,
        ])->all();

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $page * $limit < $total],
        ]);
    }
}
