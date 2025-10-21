<?php

namespace App\Http\Controllers\PublicHealth;
use App\Enums\HotspotDisease;

use App\Http\Controllers\Controller;
use App\Services\PublicHealth\WaterborneService;

use App\Models\PublicHealth\YearlyWaterborne;
use App\Http\Requests\PublicHealth\YearlyWaterborneRequest;
use Illuminate\Http\Request;
use App\Models\LayerInfo\Ward;

use DB;

class YearlyWaterborneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected WaterborneService $waterborneServiceClass;
    public function __construct(WaterborneService $waterborneServiceClass)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Yearly Waterborne Cases', ['only' => ['index']]);
        $this->middleware('permission:View Yearly Waterborne Cases', ['only' => ['show']]);
        $this->middleware('permission:Add Yearly Waterborne Cases', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Yearly Waterborne Cases', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Yearly Waterborne Cases', ['only' => ['destroy']]);
        $this->middleware('permission:Export Yearly Waterborne Cases', ['only' => ['export']]);
        $this->middleware('permission:View Yearly Waterborne Case History', ['only' => ['history']]);
        $this->waterborneServiceClass = $waterborneServiceClass;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = __('Waterborne Cases Information');
        $years = YearlyWaterborne::distinct()->pluck('year','year');
        $enumValues = HotspotDisease::toEnumArray();
        return view('public-health.waterborne.index', compact('page_title','years','enumValues'));
    }
    
    /**
    * Fetches data using the WaterborneServiceClass based on the provided request.
    *
    * @param Request $request The request object containing the data fetch criteria.
    * @return mixed The fetched data.
    */
    public function getData(Request $request)
    {

        return $this->waterborneServiceClass->fetchData($request);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = __("Add Waterborne Cases Information");
        $maxDate = date('Y') + 1;
        $minDate = date('Y') - 4;
        $wards = Ward::orderBy('ward', 'asc')->pluck('ward', 'ward')->all();
        $diesase = HotspotDisease::asSelectArray();
                $enumValues = HotspotDisease::toEnumArray();

        return view('public-health.waterborne.create', compact('page_title', 'wards', 'maxDate', 'minDate','diesase', 'enumValues'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(YearlyWaterborneRequest $request)
    {

        return $this->waterborneServiceClass->storeData($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        return $this->waterborneServiceClass->showData($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $Waterborne = YearlyWaterborne::find($id);
        $wards = Ward::orderBy('ward', 'asc')->pluck('ward', 'ward')->all();
        $year = $Waterborne->year;
        $notes = $Waterborne->notes;
        $diesase = HotspotDisease::asSelectArray();
        $enumValues = HotspotDisease::toEnumArray();

        if ($Waterborne) {
            $page_title = __("Edit Waterborne Cases Information");
            return view('public-health.waterborne.edit', compact('page_title', 'wards', 'Waterborne','year','diesase', 'enumValues'));
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
    public function update(YearlyWaterborneRequest $request, $id)
    {
        return $this->waterborneServiceClass->updateData($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $Waterborne = YearlyWaterborne::find($id);

        if ($Waterborne) {
            $Waterborne->delete();
            return redirect('publichealth/waterborne')->with('success', __('Waterborne Cases Information deleted successfully.'));
        } else {
            return redirect('publichealth/waterborne')->with('error', __('Failed to delete Waterborne Cases Information.'));
        }
    }

    public function history($id)
    {
        $Waterborne = YearlyWaterborne::find($id);
        if ($Waterborne) {
            $page_title = __("Waterborne Cases Information History");
            return view('public-health.waterborne.history', compact('page_title', 'Waterborne'));
        } else {
            abort(404);
        }
    }

    public function export(Request $request)
    {   $data = $request->all();

        return $this->waterborneServiceClass->exportData($data);
    }
}
