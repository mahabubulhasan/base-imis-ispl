<?php

namespace App\Http\Controllers\PublicHealth;

use App\Enums\HotspotDisease;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PublicHealth\Hotspots;
use App\Http\Requests\PublicHealth\HotspotRequest;
use App\Services\PublicHealth\HotspotServiceClass;
use App\Models\LayerInfo\Ward;
use DB;


class HotspotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected HotspotServiceClass $hotspotServiceClass;
    public function __construct(HotspotServiceClass $hotspotServiceClass)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Hotspot Identifications', ['only' => ['index']]);
        $this->middleware('permission:View Hotspot Identification', ['only' => ['show']]);
        $this->middleware('permission:Add Hotspot Identification', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Hotspot Identification', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Hotspot Identification', ['only' => ['destroy']]);
        $this->middleware('permission:Export Hotspot Identifications', ['only' => ['export']]);
        $this->middleware('permission:View Hotspot Identification History', ['only' => ['history']]);
        $this->hotspotServiceClass = $hotspotServiceClass;

    }

    public function index()
    {
        $page_title = __("Waterborne Hotspot");
        $wards = Ward::orderBy('ward', 'asc')->pluck('ward', 'ward')->all();
        $hotspotLocation = Hotspots::pluck('hotspot_location', 'hotspot_location')->all();
        $enumValues = HotspotDisease::toEnumArray();
        return view('public-health.hotspots.index', compact('page_title','wards','enumValues','hotspotLocation'));
    }



    public function getData(Request $request)
    {
        return $this->hotspotServiceClass->fetchData($request);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = __("Add Waterborne Hotspot");
        $maxDate = date('Y') + 1;
        $minDate = date('Y') - 4;
        $wards = Ward::orderBy('ward', 'asc')->pluck('ward', 'ward')->all();
        $diesase = HotspotDisease::asSelectArray();
        $enumValues = HotspotDisease::toEnumArray();
        return view('public-health.hotspots.create', compact('page_title','wards', 'maxDate', 'minDate','diesase', 'enumValues'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(HotspotRequest $request)
    {
        return $this->hotspotServiceClass->storeData($request);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->hotspotServiceClass->showData($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $Hotspots = Hotspots::find($id);
        $wards = Ward::orderBy('ward', 'asc')->pluck('ward', 'ward')->all();
        $geomArr = DB::select("SELECT ST_X(ST_AsText(ST_Centroid(ST_Centroid(geom)))) AS long, ST_Y(ST_AsText(ST_Centroid(ST_Centroid(geom)))) AS lat, ST_AsText(geom) AS geom FROM public_health.waterborne_hotspots WHERE id = $id");
        $geom = ($geomArr[0]->geom);
        $lat = $geomArr[0]->lat;
        $long = $geomArr[0]->long;
        $notes = $Hotspots->notes;
        $diesase = HotspotDisease::asSelectArray();
        $enumValues = HotspotDisease::toEnumArray();
        if ($Hotspots) {
            $page_title = __("Edit Waterborne Hotspot");
            return view('public-health.hotspots.edit', compact('page_title', 'wards', 'Hotspots', 'geom', 'lat', 'long','diesase', 'enumValues'));
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
    public function update(HotspotRequest $request, $id)
    {
        return $this->hotspotServiceClass->updateData($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $Hotspots = Hotspots::find($id);
        if ($Hotspots) {
                $Hotspots->delete();
                return redirect('publichealth/hotspots')->with('success', __('Waterborne Hotspot deleted successfully.'));
        } else {
            return redirect('publichealth/hotspots')->with('error', __('Failed to delete Hotspot Identification.'));
        }
    }
    /**
    * Display the history of a Hotspots record.
    *
    * @param  int  $id The ID of the Hotspots record
    * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
    */
    public function history($id)
    {
        $Hotspots = Hotspots::find($id);
        if ($Hotspots) {
            $page_title = __("Waterborne Hotspot History");
            return view('public-health.hotspots.history', compact('page_title', 'Hotspots'));
        } else {
            abort(404);
        }
    }
    /**
    * Export data using the hotspot service class.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return mixed
    */
    public function export(Request $request)
    {     $data = $request->all();
        return $this->hotspotServiceClass->exportData($data);
    }
}
