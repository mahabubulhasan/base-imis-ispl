<?php
// Last Modified: April 8, 2026
// Developed By: Streams Tech Ltd.
// Description: Delegates public FSM application validation and persistence to the shared pending application workflow.

namespace App\Services\Fsm;

use Illuminate\Http\Request;

class PublicApplicationService
{
    protected PendingApplicationService $pendingApplicationService;

    public function __construct(PendingApplicationService $pendingApplicationService)
    {
        $this->pendingApplicationService = $pendingApplicationService;
    }

    public function createApplication(Request $request)
    {
        return $this->pendingApplicationService->createPendingApplication(
            $request,
            'client-fsm-application.form',
            __('Your application submitted successfully, Thank You.')
        );
    }
}
