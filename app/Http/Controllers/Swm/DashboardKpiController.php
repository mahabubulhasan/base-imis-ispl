<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Services\Swm\Dashboard\SwmDashboardOrchestrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardKpiController extends Controller
{
    public function __construct(protected SwmDashboardOrchestrator $orchestrator)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW Dashboard and KPIs', ['only' => ['index', 'data', 'modules', 'wardGeometries']]);
    }

    public function index(Request $request)
    {
        $page_title = __('Dashboard and KPIs');
        $dashboard = $this->orchestrator->build($request->input('to_month'));

        return view('swm.dashboard.index', compact('page_title', 'dashboard'));
    }

    public function data(Request $request)
    {
        return response()->json($this->orchestrator->build($request->input('to_month')));
    }

    public function modules(Request $request)
    {
        $dashboard = $this->orchestrator->build($request->input('to_month'));

        return view('swm.dashboard.partials.modules', compact('dashboard'));
    }

    public function wardGeometries()
    {
        $rows = DB::table('layer_info.wards')
            ->whereNotNull('geom')
            ->orderBy('ward')
            ->selectRaw('ward, ST_AsGeoJSON(geom) AS geom_json')
            ->get();

        $features = [];
        foreach ($rows as $row) {
            $geometry = json_decode($row->geom_json, true);
            if (! is_array($geometry)) {
                continue;
            }
            $features[] = [
                'type' => 'Feature',
                'properties' => ['ward' => $row->ward],
                'geometry' => $geometry,
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
