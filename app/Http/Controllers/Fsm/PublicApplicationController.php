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

    public function getBuildingDataByTaxId(Request $request)
    {
        try {
            $taxId = $request->get('tax_id');
            
            if (!$taxId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tax ID is required'
                ], 400);
            }

            // Query building by tax_code with relationships
            // Tax ID in database is stored in format: "11-080-0319-00" (with dashes)
            // Load Owners relationship, but load roadlines conditionally to avoid SQL errors when road_code is null/empty
            $building = Building::with(['Owners'])
                ->where('tax_code', $taxId)
                ->whereNull('deleted_at')
                ->first();

            if (!$building) {
                return response()->json([
                    'success' => false,
                    'message' => 'No building found with this Tax ID'
                ], 404);
            }

            // Get owner information
            $owner = $building->Owners;
            
            // Get road information - only load if road_code exists and is valid (not null, empty, or 0)
            $roadline = null;
            if (!empty($building->road_code) && $building->road_code !== '0' && $building->road_code !== 0) {
                try {
                    $roadline = $building->roadlines;
                } catch (\Exception $e) {
                    // If road_code doesn't exist in roads table, set to null
                    $roadline = null;
                }
            }
            
            // Build address from house_number, house_locality, and road name
            $addressParts = array_filter([
                $building->house_number,
                $building->house_locality,
                $roadline ? $roadline->name : null
            ]);
            $address = implode(', ', $addressParts);

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => [
                    'customer_name' => $owner ? $owner->owner_name : null,
                    'customer_contact' => $owner ? $owner->owner_contact : null,
                    'holding_owner_name' => $owner ? $owner->owner_name : null,
                    'ward' => $building->ward,
                    'road_code' => $building->road_code,
                    'road_name_text' => $roadline ? $roadline->name : null,
                    'address' => $address
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching building data: ' . $e->getMessage()
            ], 500);
        }
    }
}
