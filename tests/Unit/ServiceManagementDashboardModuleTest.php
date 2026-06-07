<?php

namespace Tests\Unit;

use App\Models\Swm\LandfillLog;
use App\Models\Swm\Organization;
use App\Models\Swm\OrganizationType;
use App\Models\Swm\StsLog;
use App\Models\Swm\Vehicle;
use App\Models\Swm\VehicleType;
use App\Models\Swm\WasteProcessingLog;
use App\Models\Swm\Worker;
use App\Models\Swm\WorkType;
use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\Modules\ServiceManagementDashboardModule;
use App\Services\Swm\Dashboard\ReportingWindow;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceManagementDashboardModuleTest extends TestCase
{
    use RefreshDatabase;

    private ServiceManagementDashboardModule $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->module = app(ServiceManagementDashboardModule::class);
    }

    public function test_build_includes_three_submodules(): void
    {
        $result = $this->module->build($this->testPeriod());
        $keys = array_column($result['submodules'], 'key');

        $this->assertSame(['sts_loading', 'landfill_loading', 'waste_processing'], $keys);
    }

    public function test_sts_daily_average_uses_completed_logs_only(): void
    {
        $period = $this->testPeriod();
        $vehicle = $this->createVehicle($this->createOrganization());

        StsLog::query()->create([
            'vehicle_id' => $vehicle->id,
            'sts_name' => 'STS A',
            'quantity_ton' => 30,
            'source_wards' => ['1'],
            'entry_at' => '2026-04-15 10:00:00',
            'operation_date' => '2026-04-15',
            'operation_status' => StsLog::STATUS_COMPLETED,
        ]);

        StsLog::query()->create([
            'vehicle_id' => $vehicle->id,
            'sts_name' => 'STS A',
            'quantity_ton' => 100,
            'source_wards' => ['1'],
            'entry_at' => '2026-04-16 10:00:00',
            'operation_date' => '2026-04-16',
            'operation_status' => StsLog::STATUS_PENDING,
        ]);

        $tiles = $this->tilesFromSubmodule($this->module->build($period), 'sts_loading');

        // 30 ton completed only; pending log excluded
        $this->assertSame(
            '30.00',
            $tiles[__('Total Loading at STS (through :month) (Ton)', ['month' => 'Apr 2026'])],
        );
        // 30 ton / 30 days in April
        $this->assertSame(
            '1.00',
            $tiles[__('Average Daily Loading at STS (through :month) (Ton)', ['month' => 'Apr 2026'])],
        );
    }

    public function test_sts_loading_includes_prior_months_through_selected_month(): void
    {
        $period = $this->mayTestPeriod();
        $vehicle = $this->createVehicle($this->createOrganization());

        StsLog::query()->create([
            'vehicle_id' => $vehicle->id,
            'sts_name' => 'STS A',
            'quantity_ton' => 20,
            'source_wards' => ['1'],
            'entry_at' => '2026-03-15 10:00:00',
            'operation_date' => '2026-03-15',
        ]);

        StsLog::query()->create([
            'vehicle_id' => $vehicle->id,
            'sts_name' => 'STS A',
            'quantity_ton' => 30,
            'source_wards' => ['1'],
            'entry_at' => '2026-04-15 10:00:00',
            'operation_date' => '2026-04-15',
        ]);

        $tiles = $this->tilesFromSubmodule($this->module->build($period), 'sts_loading');

        $this->assertSame(
            '50.00',
            $tiles[__('Total Loading at STS (through :month) (Ton)', ['month' => 'May 2026'])],
        );
    }

    public function test_landfill_monthly_total_prefers_weighbridge_weight(): void
    {
        $period = $this->testPeriod();
        $vehicle = $this->createVehicle($this->createOrganization());

        LandfillLog::query()->create([
            'vehicle_id' => $vehicle->id,
            'landfill_name' => 'LF A',
            'quantity_ton' => 5,
            'weighbridge_weight_ton' => 12,
            'source_wards' => ['2'],
            'source_sts_ids' => [],
            'entry_at' => '2026-04-10 10:00:00',
            'operation_date' => '2026-04-10',
            'operation_status' => LandfillLog::STATUS_COMPLETED,
        ]);

        $tiles = $this->tilesFromSubmodule($this->module->build($period), 'landfill_loading');

        $this->assertSame(
            '5.00',
            $tiles[__('Total Loading at Landfill (through :month)', ['month' => 'Apr 2026'])],
        );
        $this->assertSame(
            '0.17',
            $tiles[__('Average Daily Loading at Landfill (through :month) (Ton)', ['month' => 'Apr 2026'])],
        );
        $this->assertSame(
            '5.00',
            $tiles[__('Average Monthly Loading at Landfill (through :month) (Ton)', ['month' => 'Apr 2026'])],
        );
    }

    public function test_landfill_loading_includes_prior_months_through_selected_month(): void
    {
        $period = $this->mayTestPeriod();
        $vehicle = $this->createVehicle($this->createOrganization());

        LandfillLog::query()->create([
            'vehicle_id' => $vehicle->id,
            'landfill_name' => 'LF A',
            'quantity_ton' => 8,
            'source_wards' => ['2'],
            'source_sts_ids' => [],
            'entry_at' => '2026-03-10 10:00:00',
            'operation_date' => '2026-03-10',
        ]);

        LandfillLog::query()->create([
            'vehicle_id' => $vehicle->id,
            'landfill_name' => 'LF A',
            'quantity_ton' => 5,
            'source_wards' => ['2'],
            'source_sts_ids' => [],
            'entry_at' => '2026-04-10 10:00:00',
            'operation_date' => '2026-04-10',
        ]);

        $tiles = $this->tilesFromSubmodule($this->module->build($period), 'landfill_loading');

        $this->assertSame(
            '13.00',
            $tiles[__('Total Loading at Landfill (through :month)', ['month' => 'May 2026'])],
        );
    }

    public function test_waste_processing_tiles_kpis_and_charts_for_reporting_month(): void
    {
        $period = $this->testPeriod();

        WasteProcessingLog::query()->create([
            'entry_at' => '2026-04-01 10:00:00',
            'report_date' => '2026-04-15',
            'reporting_month' => '2026-04-01',
            'waste_received_ton' => 100,
            'organic_waste_composted_ton' => 20,
            'inorganic_waste_recycled_ton' => 10,
            'waste_incinerated_ton' => 5,
            'waste_burned_open_air_ton' => 5,
            'residual_waste_landfilled_ton' => 60,
        ]);

        $result = $this->module->build($period);

        $this->assertSame(['tiles', 'kpis', 'charts'], $this->blockTypesFromSubmodule($result, 'waste_processing'));

        $tileItems = $this->tileItemsFromSubmodule($result, 'waste_processing');
        $this->assertCount(3, $tileItems);
        $this->assertSame(
            __('Average Monthly Waste Received for Processing (through :month) (Ton)', ['month' => 'Apr 2026']),
            $tileItems[0]['label'],
        );
        $this->assertSame('100.00', $tileItems[0]['value']);
        $this->assertSame(
            __('Average Daily Waste Received for Processing (through :month) (Ton)', ['month' => 'Apr 2026']),
            $tileItems[1]['label'],
        );
        $this->assertSame('3.33', $tileItems[1]['value']);
        $this->assertSame(
            __('Total Waste Received for Processing (through :month) (Ton)', ['month' => 'Apr 2026']),
            $tileItems[2]['label'],
        );
        $this->assertSame('100.00', $tileItems[2]['value']);

        $kpis = $this->kpisFromSubmodule($result, 'waste_processing');
        $this->assertCount(6, $kpis);
        $this->assertSame('20.00', $kpis[__('Composting Rate')]['value']);
        $this->assertSame('%', $kpis[__('Composting Rate')]['unit']);
        $this->assertTrue($kpis[__('Composting Rate')]['showFrequency']);
        $this->assertSame('30.00', $kpis[__('Resource Recovery Rate')]['value']);
        $this->assertSame('60.00', $kpis[__('Residual Waste Landfilling Rate')]['value']);

        $charts = $this->chartsFromSubmodule($result, 'waste_processing');
        $this->assertSame('doughnut', $charts['swmChartSmWasteDistribution']['type']);
        $this->assertSame('stackedBar', $charts['swmChartSmWasteTrend']['type']);
        $this->assertCount(12, $charts['swmChartSmWasteTrend']['labels']);
    }

    public function test_waste_processing_kpis_include_prior_months_through_selected_month(): void
    {
        $period = $this->mayTestPeriod();

        WasteProcessingLog::query()->create([
            'entry_at' => '2026-04-01 10:00:00',
            'report_date' => '2026-04-15',
            'reporting_month' => '2026-04-01',
            'waste_received_ton' => 100,
            'organic_waste_composted_ton' => 20,
            'inorganic_waste_recycled_ton' => 10,
            'waste_incinerated_ton' => 5,
            'waste_burned_open_air_ton' => 5,
            'residual_waste_landfilled_ton' => 60,
        ]);

        $result = $this->module->build($period);
        $tiles = $this->tilesFromSubmodule($result, 'waste_processing');
        $kpis = $this->kpisFromSubmodule($result, 'waste_processing');

        $this->assertSame(
            '100.00',
            $tiles[__('Total Waste Received for Processing (through :month) (Ton)', ['month' => 'May 2026'])],
        );
        $this->assertSame('20.00', $kpis[__('Composting Rate')]['value']);
        $this->assertSame('30.00', $kpis[__('Resource Recovery Rate')]['value']);
        $this->assertSame('60.00', $kpis[__('Residual Waste Landfilling Rate')]['value']);
    }

    public function test_chart_types_for_sts_loading(): void
    {
        $period = $this->testPeriod();
        $vehicle = $this->createVehicle($this->createOrganization());

        StsLog::query()->create([
            'vehicle_id' => $vehicle->id,
            'sts_name' => 'STS North',
            'quantity_ton' => 4,
            'source_wards' => ['3'],
            'entry_at' => '2026-04-20 10:00:00',
            'operation_date' => '2026-04-20',
        ]);

        $stsCharts = $this->chartsFromSubmodule($this->module->build($period), 'sts_loading');

        $this->assertSame('line', $stsCharts['swmChartSmStsReceiptsTrend']['type']);
        $this->assertSame('bar', $stsCharts['swmChartSmReceiptsBySts']['type']);
        $this->assertSame(
            __('Waste Loading at STS (through :month)', ['month' => 'Apr 2026']),
            $stsCharts['swmChartSmReceiptsBySts']['title'],
        );
    }

    private function testPeriod(): DashboardReportingPeriod
    {
        $periodEnd = Carbon::parse('2026-04-30 23:59:59');

        return new DashboardReportingPeriod(
            Carbon::parse('2026-04-01'),
            $periodEnd,
            [
                DashboardReportingPeriod::SOURCE_HOUSEHOLD => ReportingWindow::empty($periodEnd),
                DashboardReportingPeriod::SOURCE_LANDFILL => ReportingWindow::empty($periodEnd),
                DashboardReportingPeriod::SOURCE_COMPLAINT => ReportingWindow::empty($periodEnd),
            ],
        );
    }

    private function mayTestPeriod(): DashboardReportingPeriod
    {
        $periodEnd = Carbon::parse('2026-05-31 23:59:59');

        return new DashboardReportingPeriod(
            Carbon::parse('2026-05-01'),
            $periodEnd,
            [
                DashboardReportingPeriod::SOURCE_HOUSEHOLD => ReportingWindow::empty($periodEnd),
                DashboardReportingPeriod::SOURCE_LANDFILL => ReportingWindow::empty($periodEnd),
                DashboardReportingPeriod::SOURCE_COMPLAINT => ReportingWindow::empty($periodEnd),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createOrganization(array $overrides = []): Organization
    {
        return Organization::forceCreate(array_merge([
            'name' => 'Test Org',
            'email' => 'org@example.com',
            'address' => 'Address',
            'contact_person_name' => 'Contact',
            'contact_number' => '9800000000',
            'status' => true,
            'organization_type_id' => OrganizationType::ID_PRIVATE,
            'created_at' => '2026-01-15 00:00:00',
            'updated_at' => '2026-01-15 00:00:00',
        ], $overrides));
    }

    private function createWorkType(string $name): WorkType
    {
        return WorkType::query()->create([
            'name' => $name,
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createWorker(Organization $org, WorkType $workType, array $overrides = []): Worker
    {
        return Worker::query()->create(array_merge([
            'organization_id' => $org->id,
            'work_type_id' => $workType->id,
            'name' => 'Worker',
            'mobile' => '9811111111',
            'status' => 'active',
            'created_at' => '2026-02-01 00:00:00',
            'updated_at' => '2026-02-01 00:00:00',
        ], $overrides));
    }

    private function createVehicle(Organization $org): Vehicle
    {
        $workType = $this->createWorkType('Driver');
        $worker = $this->createWorker($org, $workType);
        $vehicleType = VehicleType::query()->create([
            'name' => 'Truck',
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ]);

        return Vehicle::query()->create([
            'organization_id' => $org->id,
            'vehicle_type_id' => $vehicleType->id,
            'vehicle_number' => 'VEH-'.uniqid(),
            'driver_worker_id' => $worker->id,
            'dumping_place_kind' => 'sts',
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ]);
    }

    /**
     * @return list<string>
     */
    private function blockTypesFromSubmodule(array $result, string $submoduleKey): array
    {
        foreach ($result['submodules'] as $submodule) {
            if (($submodule['key'] ?? '') !== $submoduleKey) {
                continue;
            }

            return array_map(
                fn (array $block) => $block['type'] ?? '',
                $submodule['blocks'] ?? [],
            );
        }

        return [];
    }

    /**
     * @return array<string, string>
     */
    private function tilesFromSubmodule(array $result, string $submoduleKey): array
    {
        $tiles = [];
        foreach ($result['submodules'] as $submodule) {
            if (($submodule['key'] ?? '') !== $submoduleKey) {
                continue;
            }
            foreach ($submodule['blocks'] ?? [] as $block) {
                if (($block['type'] ?? '') !== 'tiles') {
                    continue;
                }
                foreach ($block['items'] ?? [] as $item) {
                    $tiles[$item['label']] = $item['value'];
                }
            }
        }

        return $tiles;
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function tileItemsFromSubmodule(array $result, string $submoduleKey): array
    {
        $items = [];
        foreach ($result['submodules'] as $submodule) {
            if (($submodule['key'] ?? '') !== $submoduleKey) {
                continue;
            }
            foreach ($submodule['blocks'] ?? [] as $block) {
                if (($block['type'] ?? '') !== 'tiles') {
                    continue;
                }
                foreach ($block['items'] ?? [] as $item) {
                    $items[] = [
                        'label' => $item['label'],
                        'value' => $item['value'],
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function kpisFromSubmodule(array $result, string $submoduleKey): array
    {
        $kpis = [];
        foreach ($result['submodules'] as $submodule) {
            if (($submodule['key'] ?? '') !== $submoduleKey) {
                continue;
            }
            foreach ($submodule['blocks'] ?? [] as $block) {
                if (($block['type'] ?? '') !== 'kpis') {
                    continue;
                }
                foreach ($block['items'] ?? [] as $item) {
                    $kpis[$item['name']] = $item;
                }
            }
        }

        return $kpis;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function chartsFromSubmodule(array $result, string $submoduleKey): array
    {
        $charts = [];
        foreach ($result['submodules'] as $submodule) {
            if (($submodule['key'] ?? '') !== $submoduleKey) {
                continue;
            }
            foreach ($submodule['blocks'] ?? [] as $block) {
                if (($block['type'] ?? '') !== 'charts') {
                    continue;
                }
                foreach ($block['items'] ?? [] as $item) {
                    if (! empty($item['id'])) {
                        $charts[$item['id']] = $item;
                    }
                }
            }
        }

        return $charts;
    }
}
