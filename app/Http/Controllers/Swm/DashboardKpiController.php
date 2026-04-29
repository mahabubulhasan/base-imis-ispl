<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Services\Swm\SwmDashboardKpiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardKpiController extends Controller
{
    public function __construct(protected SwmDashboardKpiService $swmDashboardKpiService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW Dashboard and KPIs', ['only' => [
            'index',
            'complaintsByTypeChart',
            'complaintsByWardChart',
            'workersByTypeChart',
            'vehiclesByTypeChart',
            'billingByMonthChart',
            'householdsByWardChart',
            'householdCoverageByWardChart',
            'householdsVsVanPullersByWardChart',
        ]]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'month_from' => ['nullable', 'date_format:Y-m'],
            'month_to' => ['nullable', 'date_format:Y-m'],
        ]);

        $page_title = __('Dashboard and KPIs');
        $dashboard = $this->swmDashboardKpiService->buildDashboardData(
            $request->input('month_from'),
            $request->input('month_to')
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['dashboard' => $dashboard]);
        }

        return view('swm.dashboard.index', compact('page_title', 'dashboard'));
    }

    public function complaintsByTypeChart(Request $request): JsonResponse
    {
        return response()->json(
            $this->swmDashboardKpiService->complaintByTypeChart(
                $request->input('month_from'),
                $request->input('month_to')
            )
        );
    }

    public function complaintsByWardChart(Request $request): JsonResponse
    {
        return response()->json(
            $this->swmDashboardKpiService->complaintByWardChart(
                $request->input('month_from'),
                $request->input('month_to')
            )
        );
    }

    public function workersByTypeChart(): JsonResponse
    {
        return response()->json($this->swmDashboardKpiService->workersByTypeChart());
    }

    public function vehiclesByTypeChart(): JsonResponse
    {
        return response()->json($this->swmDashboardKpiService->vehiclesByTypeChart());
    }

    public function billingByMonthChart(Request $request): JsonResponse
    {
        return response()->json(
            $this->swmDashboardKpiService->billingByMonthChart(
                $request->input('month_from'),
                $request->input('month_to')
            )
        );
    }

    public function householdsByWardChart(): JsonResponse
    {
        return response()->json($this->swmDashboardKpiService->householdsByWardChart());
    }

    public function householdCoverageByWardChart(): JsonResponse
    {
        return response()->json($this->swmDashboardKpiService->householdCoverageByWardChart());
    }

    public function householdsVsVanPullersByWardChart(): JsonResponse
    {
        return response()->json($this->swmDashboardKpiService->householdsVsVanPullersByWardChart());
    }
}
