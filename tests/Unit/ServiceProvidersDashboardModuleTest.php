<?php

namespace Tests\Unit;

use App\Models\Swm\Organization;
use App\Models\Swm\OrganizationType;
use App\Models\Swm\Worker;
use App\Models\Swm\WorkType;
use App\Models\User;
use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\Modules\ServiceProvidersDashboardModule;
use App\Services\Swm\Dashboard\ReportingWindow;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ServiceProvidersDashboardModuleTest extends TestCase
{
    use RefreshDatabase;

    private ServiceProvidersDashboardModule $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->module = app(ServiceProvidersDashboardModule::class);
    }

    public function test_build_counts_operational_orgs_and_active_workers(): void
    {
        $period = $this->testPeriod();
        $org = $this->createOrganization(['organization_category' => OrganizationType::CODE_GOVERNMENT]);
        $workType = $this->createWorkType('Collector');
        $this->createWorker($org, $workType, [
            'gender' => 'male',
            'age' => 30,
            'employment_type' => 'permanent',
            'education_level' => 'ssc',
            'service_wards' => [1, 2],
        ]);

        $inactiveOrg = $this->createOrganization(['name' => 'Inactive Org', 'status' => false]);
        $this->createWorker($inactiveOrg, $workType, ['status' => 'active']);

        $this->createWorker($org, $workType, ['status' => 'inactive']);

        $result = $this->module->build($period);
        $tiles = $this->tilesFromResult($result);

        $this->assertSame('1', $tiles[__('Total Organizations')]);
        $this->assertSame('1', $tiles[__('Total Workers')]);
    }

    public function test_excludes_records_created_after_period_end(): void
    {
        $period = $this->testPeriod();
        $org = $this->createOrganization(['created_at' => '2026-05-01 00:00:00']);
        $workType = $this->createWorkType('Driver');
        $this->createWorker($org, $workType, ['created_at' => '2026-05-01 00:00:00']);

        $result = $this->module->build($period);
        $tiles = $this->tilesFromResult($result);

        $this->assertSame('0', $tiles[__('Total Organizations')]);
        $this->assertSame('0', $tiles[__('Total Workers')]);
    }

    public function test_chart_shapes_for_distributions(): void
    {
        $period = $this->testPeriod();
        $org = $this->createOrganization(['organization_category' => OrganizationType::CODE_PRIVATE]);
        $workType = $this->createWorkType('Supervisor');
        $this->createWorker($org, $workType, [
            'gender' => 'female',
            'age' => 22,
            'employment_type' => 'contract',
            'education_level' => 'bachelor',
            'service_wards' => [3],
        ]);

        $result = $this->module->build($period);
        $charts = $this->chartsById($result);

        $this->assertSame('doughnut', $charts['swmChartOrgCategory']['type']);
        $this->assertContains(__('Private'), $charts['swmChartOrgCategory']['labels']);

        $this->assertSame('bar', $charts['swmChartWorkersByType']['type']);
        $this->assertSame(['Supervisor'], $charts['swmChartWorkersByType']['labels']);
        $this->assertSame([1], $charts['swmChartWorkersByType']['datasets'][0]['data']);

        $this->assertSame('stackedBar', $charts['swmChartWorkersWardOrg']['type']);
        $this->assertSame(['3'], $charts['swmChartWorkersWardOrg']['labels']);
        $this->assertNotEmpty($charts['swmChartWorkersWardOrg']['datasets']);

        $this->assertSame('doughnut', $charts['swmChartWorkerGender']['type']);
        $this->assertContains(__('Female'), $charts['swmChartWorkerGender']['labels']);

        $this->assertSame('bar', $charts['swmChartWorkerAge']['type']);
        $this->assertContains('18-24', $charts['swmChartWorkerAge']['labels']);

        $this->assertSame('doughnut', $charts['swmChartWorkerEmploymentType']['type']);
        $this->assertContains(__('Contract'), $charts['swmChartWorkerEmploymentType']['labels']);

        $this->assertSame('bar', $charts['swmChartWorkerEducation']['type']);
        $this->assertContains(__('Bachelor'), $charts['swmChartWorkerEducation']['labels']);
    }

    public function test_gender_chart_does_not_double_count_empty_gender(): void
    {
        $period = $this->testPeriod();
        $org = $this->createOrganization();
        $workType = $this->createWorkType('Collector');
        $this->createWorker($org, $workType, ['gender' => 'male']);
        $this->createWorker($org, $workType, ['gender' => '']);

        $result = $this->module->build($period);
        $charts = $this->chartsById($result);
        $genderChart = $charts['swmChartWorkerGender'];

        $this->assertSame([__('Male'), __('N/A')], $genderChart['labels']);
        $this->assertSame([1, 1], $genderChart['datasets'][0]['data']);
    }

    public function test_scopes_metrics_to_authenticated_organization(): void
    {
        $period = $this->testPeriod();
        $orgA = $this->createOrganization(['name' => 'Org A']);
        $orgB = $this->createOrganization(['name' => 'Org B']);
        $workType = $this->createWorkType('Operator');
        $this->createWorker($orgA, $workType);
        $this->createWorker($orgB, $workType);

        $user = User::factory()->create(['swm_organization_id' => $orgA->id]);
        Auth::login($user);

        $result = $this->module->build($period);
        $tiles = $this->tilesFromResult($result);

        $this->assertSame('1', $tiles[__('Total Organizations')]);
        $this->assertSame('1', $tiles[__('Total Workers')]);

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
        if (isset($overrides['organization_category'])) {
            $code = $overrides['organization_category'];
            unset($overrides['organization_category']);
            $overrides['organization_type_id'] = OrganizationType::query()
                ->where('code', $code)
                ->value('id');
        }

        return Organization::forceCreate(array_merge([
            'name' => 'Test Org',
            'email' => 'org@example.com',
            'address' => 'Address',
            'contact_person_name' => 'Contact',
            'contact_number' => '9800000000',
            'status' => true,
            'organization_type_id' => OrganizationType::query()
                ->where('code', OrganizationType::CODE_PRIVATE)
                ->value('id'),
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

    /**
     * @return array<string, string>
     */
    private function tilesFromResult(array $result): array
    {
        $tiles = [];
        foreach ($result['submodules'][0]['blocks'] ?? [] as $block) {
            if (($block['type'] ?? '') !== 'tiles') {
                continue;
            }
            foreach ($block['items'] ?? [] as $item) {
                $tiles[$item['label']] = $item['value'];
            }
        }

        return $tiles;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function chartsById(array $result): array
    {
        $charts = [];
        foreach ($result['submodules'][0]['blocks'] ?? [] as $block) {
            if (($block['type'] ?? '') !== 'charts') {
                continue;
            }
            foreach ($block['items'] ?? [] as $item) {
                if (! empty($item['id'])) {
                    $charts[$item['id']] = $item;
                }
            }
        }

        return $charts;
    }
}
