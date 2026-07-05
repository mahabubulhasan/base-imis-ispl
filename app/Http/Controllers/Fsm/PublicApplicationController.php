<?php
// Last Modified: March 24, 2026
// Developed By: Streams Tech Ltd.
// Description: Handles public FSM application workflows and tax-ID based autofill endpoints.
namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use App\Models\LayerInfo\Ward;
use App\Models\TaxPaymentInfo\TaxPayment;
use App\Models\TaxPaymentInfo\TaxPaymentStatus;
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
        // Check if it's an AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            try {
                $result = $this->_applicationService->createApplication($request);

                // Check if result is a redirect (success or error)
                if ($result instanceof \Illuminate\Http\RedirectResponse) {
                    $session = $result->getSession();

                    // Check for success message
                    if ($session && $session->has('success')) {
                        $response = [
                            'success' => true,
                            'message' => $session->get('success')
                        ];

                        // Include application_id if it was stored in session
                        if ($session->has('created_application_id')) {
                            $response['application_id'] = $session->get('created_application_id');
                        }

                        return response()->json($response);
                    }

                    // Check for error message
                    if ($session && $session->has('error')) {
                        return response()->json([
                            'success' => false,
                            'message' => $session->get('error')
                        ], 400);
                    }

                    // Check for validation errors
                    $errors = $session ? $session->get('errors') : null;
                    if ($errors) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Validation failed',
                            'errors' => $errors->toArray()
                        ], 422);
                    }
                }

                // Default success response
                return response()->json([
                    'success' => true,
                    'message' => 'Your application submitted successfully, Thank You.'
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing your application: ' . $e->getMessage()
                ], 500);
            }
        }

        // Non-AJAX request - use original behavior
        return $this->_applicationService->createApplication($request);
    }

    public function getWardsData()
    {
        try {
            $wards = Ward::orderBy('ward')->pluck('ward')->toArray();

            return response()->json([
                'success' => true,
                'wards' => $wards
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load wards data: ' . $e->getMessage()
            ], 500);
        }
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
                    'message' => 'Tax Code is required'
                ], 400);
            }

            $taxPaymentStatusTable = (new TaxPaymentStatus())->getTable();

            $taxPayment = TaxPayment::query()
                ->leftJoin($taxPaymentStatusTable, 'taxpayment_info.tax_payments.tax_code', '=', $taxPaymentStatusTable . '.tax_code')
                ->select(
                    'taxpayment_info.tax_payments.owner_name',
                    'taxpayment_info.tax_payments.owner_contact',
                    $taxPaymentStatusTable . '.ward'
                )
                ->where('taxpayment_info.tax_payments.tax_code', $taxId)
                ->first();

            if (!$taxPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tax payment record found with this Tax Code'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'owner_name' => $taxPayment->owner_name,
                    'owner_contact' => $taxPayment->owner_contact,
                    'ward' => $taxPayment->ward
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
