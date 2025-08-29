<?php
namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use App\Models\BuildingInfo\Building;
use App\Models\LayerInfo\Ward;
use App\Models\UtilityInfo\Roadline;
use App\Services\Fsm\PublicApplicationService;
use Illuminate\Http\Request;

class PublicApplicationController extends Controller
{

    private $_applicationService;

    public function __construct(PublicApplicationService $applicationService)
    {
        $this->_applicationService = $applicationService;
    }

    public function getForm()
    {
        $wards = Ward::orderBy('ward')->pluck('ward', 'ward')->toArray();
        return view('fsm-application', compact('wards'));
    }

    public function submitForm(Request $request)
    {
        return $this->_applicationService->createApplication($request);
    }

    public function getRoadNames()
    {
        $query = Roadline::all()->toQuery();
        if (request()->search) {
            $query->where('name', 'ilike', '%' . request()->search . '%')
                ->orWhere('code', 'ilike', '%' . request()->search . '%');
        }

        $total = $query->count();


        $limit = 10;
        if (request()->page) {
            $page = request()->page;
        } else {
            $page = 1;
        }
        ;
        $start_from = ($page - 1) * $limit;

        $total_pages = ceil($total / $limit);
        if ($page < $total_pages) {
            $more = true;
        } else {
            $more = false;
        }
        $roads = $query->offset($start_from)
            ->limit($limit)
            ->get();
        $json = [];
        foreach ($roads as $road) {
            $json[] = ['id' => $road['code'], 'text' => $road['name'] ?? $road['code']];
        }

        return response()->json(['results' => $json, 'pagination' => ['more' => $more]]);
    }
}
