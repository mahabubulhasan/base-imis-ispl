<?php

namespace Tests\Unit;

use App\Models\Swm\AttendanceLog;
use App\Models\Swm\Landfill;
use App\Models\Swm\LandfillLog;
use App\Models\Swm\Organization;
use App\Models\Swm\Sts;
use App\Models\Swm\StsLog;
use App\Models\Swm\Vehicle;
use App\Models\Swm\VehicleType;
use App\Models\Swm\WasteProcessingLog;
use App\Models\Swm\Worker;
use App\Models\Swm\WorkType;
use App\Models\User;
use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\Modules\ServiceManagementDashboardModule;
use App\Services\Swm\Dashboard\ReportingWindow;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
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

    public function test_build_includes_four_submodules(): void
    {
        $result = $this->module->build($this->testPeriod());
        $keys = array_column($result['submodules'], 'key');

        $this->assertSame(['attendance', 'sts_loading', 'landfill_loading', 'waste_processing'], $keys);
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

        // 30 ton / 30 days in April
        $this->assertSame('1.00', $tiles[__('Daily STS Receipts (Ton)')]);
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

        $this->assertSame('12.00', $tiles[__('Monthly Landfill Receipts (Ton)')]);
        $this->assertSame('0.40', $tiles[__('Daily Landfill Receipts (Ton)')]);
    }

    public function test_waste_processing_tiles_and_charts_for_reporting_month(): void
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
        $tiles = $this->tilesFromSubmodule($result, 'waste_processing');
        $charts = $this->chartsFromSubmodule($result, 'waste_processing');

        $this->assertSame('100.00', $tiles[__('Quantity of Waste Received in Last Month')]);
        $this->assertSame('20.0 %', $tiles[__('Composting Rate')]);
        $this->assertSame('30.0 %', $tiles[__('Resource Recovery Rate')]);
        $this->assertSame('doughnut', $charts['swmChartSmWasteDistribution']['type']);
        $this->assertSame('stackedArea', $charts['swmChartSmWasteTrend']['type']);
        $this->assertCount(12, $charts['swmChartSmWasteTrend']['labels']);
    }

    public function test_chart_types_for_attendance_and_sts(): void
    {
        $period = $this->testPeriod();
        $org = $this->createOrganization(['name' => 'Org Alpha']);
        $workType = $this->createWorkType('Collector');
        $worker = $this->createWorker($org, $workType);

        AttendanceLog::query()->create([
            'organization_id' => $org->id,
            'worker_id' => $worker->id,
            'department' => 'Sanitation',
            'entry_at' => '2026-04-15 08:00:00',
            'attendance_status' => AttendanceLog::STATUS_PRESENT,
            'check_in_at' => '2026-04-15 08:00:00',
            'check_out_at' => '2026-04-15 16:00:00',
        ]);

        $vehicle = $this->createVehicle($org);
        StsLog::query()->create([
            'vehicle_id' => $vehicle->id,
            'sts_name' => 'STS North',
            'quantity_ton' => 4,
            'source_wards' => ['3'],
            'entry_at' => '2026-04-20 10:00:00',
            'operation_date' => '2026-04-20',
            'operation_status' => StsLog::STATUS_COMPLETED,
        ]);

        $result = $this->module->build($period);
        $attendanceCharts = $this->chartsFromSubmodule($result, 'attendance');
        $stsCharts = $this->chartsFromSubmodule($result, 'sts_loading');

        $this->assertSame('line', $attendanceCharts['swmChartSmAttendanceTrend']['type']);
        $this->assertSame('bar', $attendanceCharts['swmChartSmAttendanceByOrg']['type']);
        $this->assertSame('horizontalBar', $attendanceCharts['swmChartSmAttendanceByDept']['type']);
        $this->assertSame('line', $stsCharts['swmChartSmStsReceiptsTrend']['type']);
        $this->assertSame('horizontalBar', $stsCharts['swmChartSmReceiptsBySts']['type']);
        $this->assertSame('stackedBar', $stsCharts['swmChartSmWardToSts']['type']);
    }

    public function test_landfill_catchment_network_payload(): void
    {
        $period = $this->testPeriod();
        $sts = $this->createSts(['name' => 'STS One']);
        Landfill::query()->create([
            'name' => 'Main Landfill',
            'operator_name' => 'Op',
            'contact_number' => '9800000001',
            'source_sts_ids' => [$sts->id],
            'source_wards' => [5],
            'operational_status' => 'active',
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ]);

        $charts = $this->chartsFromSubmodule($this->module->build($period), 'landfill_loading');
        $network = $charts['swmChartSmLandfillCatchment'];

        $this->assertSame('network', $network['type']);
        $this->assertNotEmpty($network['nodes']);
        $this->assertNotEmpty($network['edges']);
    }

    public function test_attendance_scoped_to_authenticated_organization(): void
    {
        $period = $this->testPeriod();
        $orgA = $this->createOrganization(['name' => 'Org A']);
        $orgB = $this->createOrganization(['name' => 'Org B']);
        $workType = $this->createWorkType('Driver');

        $workerA = $this->createWorker($orgA, $workType);
        $workerB = $this->createWorker($orgB, $workType);

        foreach ([$workerA, $workerB] as $worker) {
            AttendanceLog::query()->create([
                'organization_id' => $worker->organization_id,
                'worker_id' => $worker->id,
                'entry_at' => '2026-04-10 08:00:00',
                'attendance_status' => AttendanceLog::STATUS_PRESENT,
                'check_in_at' => '2026-04-10 08:00:00',
                'check_out_at' => '2026-04-10 17:00:00',
            ]);
        }

        Auth::login(User::factory()->create(['swm_organization_id' => $orgA->id]));

        $charts = $this->chartsFromSubmodule($this->module->build($period), 'attendance');
        $orgChart = $charts['swmChartSmAttendanceByOrg'];

        $this->assertSame(['Org A'], $orgChart['labels']);

        Auth::logout();
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
            'organization_category' => Organization::CATEGORY_PRIVATE,
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
     * @param  array<string, mixed>  $overrides
     */
    private function createSts(array $overrides = []): Sts
    {
        return Sts::query()->create(array_merge([
            'name' => 'STS Default',
            'operator_name' => 'Operator',
            'contact_number' => '9800000002',
            'operational_status' => 'active',
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ], $overrides));
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
