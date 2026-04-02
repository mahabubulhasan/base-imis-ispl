<?php
// Last Modified: 12-03-2026
// Developed By: Streams Tech Ltd.
// Description: Handles index, view, and delete actions for pending applications in FSM.

namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use App\Models\Fsm\Application;
use App\Services\Fsm\PendingApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;

class PendingApplicationController extends Controller
{
    protected PendingApplicationService $pendingApplicationService;

    public function __construct(PendingApplicationService $pendingApplicationService)
    {
        $this->pendingApplicationService = $pendingApplicationService;
    }

    public function index(): View
    {
        return view('fsm.pending-applications.index', [
            'pageTitle' => __('Pending Application'),
        ]);
    }

    public function getData(Request $request)
    {
        return $this->pendingApplicationService->getDatatable($request);
    }

    public function show(int $id): View
    {
        $pendingApplication = Application::findOrFail($id);

        return view('fsm.pending-applications.show', [
            'pageTitle' => __('Pending Application Details'),
            'pendingApplication' => $pendingApplication,
            'detailRows' => $this->pendingApplicationService->getShowData($pendingApplication),
            'indexAction' => $this->pendingApplicationService->getIndexRoute(),
        ]);
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        try {
            $pendingApplication = Application::findOrFail($id);
            $pendingApplication->delete();

            return redirect(route('pending-application.index'))
                ->with('success', __('Pending Application deleted successfully.'));
        } catch (\Throwable $e) {
            return redirect(route('pending-application.index'))
                ->with('error', __('Failed to delete Pending Application.'));
        }
    }
}
