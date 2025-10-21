<?php
// Last Modified Date: 07-05-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Controllers\PublicHealth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PublicHealth\WaterSamples;
use App\Http\Requests\PublicHealth\WaterSamplesRequest;
use App\Services\PublicHealth\WaterSamplesService;
use DB;
use App\Enums\WaterSamplesResult;
use Carbon\Carbon;


class WaterSamplesController extends Controller
{
    protected WaterSamplesService $waterSamplesService;
    public function __construct(WaterSamplesService $waterSamplesService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Water Samples', ['only' => ['index']]);
        $this->middleware('permission:View Water Samples', ['only' => ['show']]);
        $this->middleware('permission:Add Water Samples', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Water Samples', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Water Samples', ['only' => ['destroy']]);
        $this->middleware('permission:View Water Samples History', ['only' => ['history']]);
        $this->middleware('permission:Export Water Samples to CSV', ['only' => ['export']]);
        $this->waterSamplesService = $waterSamplesService;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $page_title = __("Water Samples");
        $water_coliform_test_result = WaterSamplesResult::toEnumArray();
        return view('public-health/water-samples.index', compact('page_title', 'water_coliform_test_result'));
    }

    public function getData(WaterSamplesRequest  $request)
    {
        //$data = $request->all();
        return $this->waterSamplesService->getAllData($request);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = __("Add Water Samples");
        $water_coliform_test_result = WaterSamplesResult::toEnumArray();
        return view('public-health/water-samples.create', compact('page_title', 'water_coliform_test_result'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(WaterSamplesRequest $request)
    {
        $data = $request->all();
        $this->waterSamplesService->storeOrUpdate($id = null, $data);
        return redirect('publichealth/water-samples')->with('success', __('Water Samples created successfully.'));
    }

   

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $waterSamples = WaterSamples::find($id);
        $water_coliform_test_result = WaterSamplesResult::toEnumArray();
       
        $geomInfo = DB::select("SELECT 
        ST_X(ST_AsText('" . $waterSamples['geom'] . "')) AS longitude, 
        ST_Y(ST_AsText('" . $waterSamples['geom'] . "')) AS latitude;");

        $long = $geomInfo[0]->longitude;
        $lat = $geomInfo[0]->latitude;
        $geom = $long . ',' . $lat;

        if ($waterSamples) {
            $page_title = __("Edit Water Samples");
            return view('public-health/water-samples.edit', compact('page_title', 'waterSamples','water_coliform_test_result', 'geom', 'long', 'lat'));
        } else {
            abort(404);
        }
    }

     /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $waterSamples = WaterSamples::find($id);
        if ($waterSamples) {
            $page_title = __("Water Samples Details");
            $date = Carbon::parse($waterSamples->sample_date)->format('m/d/Y');
            return view('public-health/water-samples.show', compact('page_title', 'waterSamples','date'));
        } else {
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(WaterSamplesRequest $request, $id)
    {
        $waterSamples = WaterSamples::find($id);
        if ($waterSamples) {
           
            $data = $request->all();
            $this->waterSamplesService->storeOrUpdate($id,$data);
            return redirect('publichealth/water-samples')->with('success', __('Water Samples updated successfully.'));
        } else {
            return redirect('publichealth/water-samples')->with('error', __('Failed to update Water Samples.'));
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
        $waterSamples = WaterSamples::find($id);
        if ($waterSamples) {
            $waterSamples->delete();
            return redirect('publichealth/water-samples')->with('success', __('Water Samples Information deleted successfully.'));
        } else { 
            return redirect('publichealth/water-samples')->with('error', __('Failed to delete Water Samples Information.'));
        }
       
    }
    
    public function history($id)
    {
        $waterSamples = WaterSamples::find($id);
        if ($waterSamples) {
            $page_title = __("Water Samples History");
            return view('public-health/water-samples.history', compact('page_title', 'waterSamples'));
        } else {
            abort(404);
        }
    }

    /**
     * Export a listing of the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $data = $request->all();
        return $this->waterSamplesService->download($data);
        
    }

}
