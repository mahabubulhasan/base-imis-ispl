<?php
// Last Modified: 2026-05-01
// Developed By: Streams Tech Ltd.
// Description: API endpoints for sludge collection operations consumed by mobile clients.
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fsm\SludgeCollectionRequest;
use App\Models\Fsm\Application;
use App\Models\Fsm\SludgeCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;


class SludgeCollectionController extends Controller
{
    /**
     * Save sludge collection details from mobile app.
     *
     * @param SludgeCollectionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(SludgeCollectionRequest $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('Unauthenticated.')
            ], 401);
        }

        if (!$user->can('Add Sludge Collection')) {
            return response()->json([
                'status' => false,
                'message' => __('Unauthorized: You do not have permission to add sludge collection data.')
            ], 403);
        }

        DB::beginTransaction();

        try {
            $applicationId = $request->application_id ?: null;

            $sludgeCollection = new SludgeCollection();
            $sludgeCollection->application_id = $applicationId;
            $sludgeCollection->volume_of_sludge = $request->volume_of_sludge ?? null;
            $sludgeCollection->date = $request->date ?: null;
            $sludgeCollection->no_of_trips = $request->no_of_trips ?: null;
            $sludgeCollection->entry_time = $request->entry_time ?: null;
            $sludgeCollection->exit_time = $request->exit_time ?: null;
            $sludgeCollection->treatment_plant_id = $user->hasRole('Treatment Plant - Admin')
                ? $user->treatment_plant_id
                : ($request->treatment_plant_id ?: null);
            $sludgeCollection->service_provider_id = $request->service_provider_id ?? null;
            $sludgeCollection->desludging_vehicle_id = $request->desludging_vehicle_id ?? null;
            $sludgeCollection->user_id = $user->id;
            $sludgeCollection->save();

            if ($applicationId) {
                $application = Application::find($applicationId);

                if (!$application) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => __('Application not found for the given application id.')
                    ], 404);
                }

                $application->sludge_collection_status = true;
                $application->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Sludge collection created successfully.'),
                'data' => [
                    'id' => $sludgeCollection->id,
                    'application_id' => $sludgeCollection->application_id,
                ]
            ], 201);
        } catch (Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}