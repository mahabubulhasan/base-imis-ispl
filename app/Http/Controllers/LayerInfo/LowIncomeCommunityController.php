<?php

namespace App\Http\Controllers\LayerInfo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LayerInfo\LowIncomeCommunity;
use App\Models\BuildingInfo\Building;
use App\Http\Requests\LayerInfo\LowIncomeCommunityRequest;
use App\Services\LayerInfo\LowIncomeCommunityServiceClass;
use DB;


class LowIncomeCommunityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected LowIncomeCommunityServiceClass $lowIncomeCommunityServiceClass;
    public function __construct(LowIncomeCommunityServiceClass $lowIncomeCommunityServiceClass)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Low Income Communities', ['only' => ['index']]);
        $this->middleware('permission:View Low Income Community', ['only' => ['show']]);
        $this->middleware('permission:Add Low Income Community', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Low Income Community', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Low Income Community', ['only' => ['destroy']]);
        $this->middleware('permission:Export Low Income Communities', ['only' => ['export']]);
        $this->lowIncomeCommunityServiceClass = $lowIncomeCommunityServiceClass;

    }

    public function index()
    {
        $page_title = __("Low Income Community");
        return view('layer-info.low-income-communities.index', compact('page_title'));
    }



    public function getData(Request $request)
    {
        return $this->lowIncomeCommunityServiceClass->fetchData($request);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = __("Add Low Income Community");
        return view('layer-info.low-income-communities.create', compact('page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LowIncomeCommunityRequest $request)
    {
        return $this->lowIncomeCommunityServiceClass->storeData($request);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->lowIncomeCommunityServiceClass->showData($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $lic = LowIncomeCommunity::find($id);
        $geomArr = DB::select("SELECT ST_X(ST_AsText(ST_Centroid(ST_Centroid(geom)))) AS long, ST_Y(ST_AsText(ST_Centroid(ST_Centroid(geom)))) AS lat, ST_AsText(geom) AS geom FROM layer_info.low_income_communities WHERE id = $id");
        $geom = ($geomArr[0]->geom);
        $lat = $geomArr[0]->lat;
        $long = $geomArr[0]->long;
        if ($lic) {
            $page_title = __("Edit Low Income Community");
            return view('layer-info.low-income-communities.edit', compact('page_title', 'lic', 'geom', 'lat', 'long'));
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
    public function update(LowIncomeCommunityRequest $request, $id)
    {
        return $this->lowIncomeCommunityServiceClass->updateData($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $lic = LowIncomeCommunity::find($id);
        if ($lic) {
                $building_count = Building::where('lic_id',$lic->id)->count();
                if($building_count == 0)
                {
                    $lic->delete();
                    return redirect('layer-info/low-income-communities')->with('success',__('Low Income Community deleted successfully.'));
                }
                else
                {
                    return redirect('layer-info/low-income-communities')->with('error',__('Cannot delete Low Income Community that has associated Buildings.'));
                }
            } else {
                return redirect('layer-info/low-income-communities')->with('error',__('Failed to delete Low Income Community.'));
            }
{
    $lic = LowIncomeCommunity::find($id);
    if ($lic) {
        $building_count = Building::where('lic_id', $lic->id)->count();
        if ($building_count == 0) {
            $lic->delete();
            return redirect('layer-info/low-income-communities')->with('success', __('Low Income Community deleted successfully.'));
        } else {
            return redirect('layer-info/low-income-communities')->with('error', __('Cannot delete Low Income Community that has associated Buildings.'));
        }
    } else {
        return redirect('layer-info/low-income-communities')->with('error', __('Failed to delete Low Income Community.'));
    }
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
        $lic = LowIncomeCommunity::find($id);
        if ($lic) {
            $page_title = __("Low Income Community History");
            return view('layer-info.low-income-communities.history', compact('page_title', 'lic'));
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
        return $this->lowIncomeCommunityServiceClass->exportData($data);
    }
}
