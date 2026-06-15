<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuildingInfo\BuildingRequest;
use App\Services\BuildingInfo\BuildingFormDataService;
use App\Services\BuildingInfo\BuildingStructureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class BuildingController extends Controller
{
    protected BuildingFormDataService $buildingFormDataService;
    protected BuildingStructureService $buildingStructureService;

    public function __construct(
        BuildingStructureService $buildingStructureService,
        BuildingFormDataService $buildingFormDataService
    )
    {
        $this->buildingStructureService = $buildingStructureService;
        $this->buildingFormDataService = $buildingFormDataService;
    }

    public function formMetadata(): JsonResponse
    {
        try {
            return response()->json([
                'status' => 200,
                'message' => __('Building form metadata fetched successfully.'),
                'data' => $this->buildingFormDataService->getFormMetadata(),
            ])->header('Cache-Control', 'private, max-age=3600');
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function editData($bin): JsonResponse
    {
        try {
            $editData = $this->buildingFormDataService->getApiEditData($bin);

            if (!$editData) {
                return response()->json([
                    'status' => 404,
                    'message' => __('Building not found.'),
                    'data' => null,
                ], 404);
            }

            return response()->json([
                'status' => 200,
                'message' => __('Building edit form data fetched successfully.'),
                'data' => $editData,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createData(): JsonResponse
    {
        try {
            return response()->json([
                'status' => 200,
                'message' => __('Building create form data fetched successfully.'),
                'data' => $this->buildingFormDataService->getCreateFormData(),
            ])
                ->header('Deprecation', 'true')
                ->header('Link', '</api/building-info/buildings/form-metadata>; rel="successor-version"');
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(BuildingRequest $request): JsonResponse
    {
        try {
            $response = $this->buildingStructureService->storeBuildingData($request);

            if ($response instanceof RedirectResponse) {
                $session = $response->getSession();
                if ($session && $session->has('error')) {
                    return response()->json([
                        'status' => 422,
                        'message' => $session->get('error'),
                        'errors' => (object) [],
                        'data' => null,
                    ], 422);
                }

                return response()->json([
                    'status' => 200,
                    'message' => __('Building created successfully'),
                    'data' => [
                        'bin' => $request->bin ?? null,
                    ],
                ]);
            }

            return response()->json([
                'status' => 200,
                'message' => __('Building created successfully'),
                'data' => $response,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'message' => __('The given data was invalid.'),
                'errors' => $e->errors(),
                'data' => null,
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * Update a building by BIN.
     */
    public function update(BuildingRequest $request, $bin): JsonResponse
    {
        try {
            $response = $this->buildingStructureService->updateBuildingData($request, $bin);

            if ($response instanceof RedirectResponse) {
                $session = $response->getSession();
                if ($session && $session->has('error')) {
                    return response()->json([
                        'status' => 422,
                        'message' => $session->get('error'),
                        'errors' => (object) [],
                        'data' => null,
                    ], 422);
                }
                return response()->json([
                    'status' => 200,
                    'message' => __('Building Information updated successfully'),
                    'data' => null,
                ]);
            }

            return response()->json([
                'status' => 200,
                'message' => __('Building Information updated successfully'),
                'data' => $response,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'message' => __('The given data was invalid.'),
                'errors' => $e->errors(),
                'data' => null,
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}

