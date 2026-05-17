<?php

namespace App\Services\Swm\Dashboard;

use App\Services\Maps\MapsService;
use App\Services\Swm\Dashboard\Contracts\SwmDashboardModuleInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class SwmDashboardOrchestrator
{
    public function __construct(
        protected DashboardReportingPeriodResolver $periodResolver,
    ) {
    }

    public function build(?string $toMonthInput): array
    {
        $period = $this->periodResolver->resolve($toMonthInput);
        $modules = [];
        $moduleViews = [];
        $charts = [];

        foreach (config('swm_dashboard.modules', []) as $key => $config) {
            if (! ($config['enabled'] ?? false)) {
                continue;
            }

            $class = $config['class'] ?? null;
            if (! $class || ! class_exists($class)) {
                continue;
            }

            $module = app($class);
            if (! $module instanceof SwmDashboardModuleInterface) {
                continue;
            }

            if (! $this->userCanViewModule($module)) {
                continue;
            }

            $payload = $module->build($period);
            $modules[$key] = array_merge($payload, [
                'label' => $module->label(),
            ]);
            $moduleViews[$key] = $config['view'] ?? null;
            $charts = array_merge($charts, $this->extractCharts($payload));
        }

        return [
            'period' => [
                'to_month' => $period->toMonthString(),
                'period_end' => $period->periodEndDateString(),
                'max_to_month' => $this->periodResolver->latestAllowedToMonthString(),
                'reporting_days' => $period->reportingDays(),
                'household_days' => $period->window(DashboardReportingPeriod::SOURCE_HOUSEHOLD)->days,
                'landfill_days' => $period->window(DashboardReportingPeriod::SOURCE_LANDFILL)->days,
                'complaint_days' => $period->window(DashboardReportingPeriod::SOURCE_COMPLAINT)->days,
            ],
            'modules' => $modules,
            'moduleViews' => $moduleViews,
            'charts' => $charts,
            'map' => $this->mapConfig(),
        ];
    }

    protected function mapConfig(): array
    {
        $geoserverUrl = rtrim((string) config('constants.GEOSERVER_URL'), '/');

        return [
            'workspace' => config('constants.GEOSERVER_WORKSPACE'),
            'geoserverUrl' => $geoserverUrl !== '' ? $geoserverUrl.'/' : '',
            'authKey' => config('constants.AUTH_KEY'),
            'bbox' => app(MapsService::class)->getCityBboxString(),
            'wardsLayer' => 'wards_layer',
            'wardGeometriesUrl' => route('swm.dashboard-kpis.ward-geometries'),
        ];
    }

    protected function userCanViewModule(SwmDashboardModuleInterface $module): bool
    {
        $permission = $module->permission();
        if ($permission === null) {
            return true;
        }

        $user = Auth::user();
        if (! $user instanceof Authenticatable) {
            return false;
        }

        return $user->can($permission);
    }

    protected function extractCharts(array $modulePayload): array
    {
        $charts = [];

        foreach ($modulePayload['submodules'] ?? [] as $submodule) {
            foreach ($submodule['blocks'] ?? [] as $block) {
                if (($block['type'] ?? '') !== 'charts') {
                    continue;
                }
                foreach ($block['items'] ?? [] as $item) {
                    if (! empty($item['id'])) {
                        $charts[] = $item;
                    }
                }
            }
        }

        return $charts;
    }
}
