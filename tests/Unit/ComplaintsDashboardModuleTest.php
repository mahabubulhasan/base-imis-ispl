<?php

namespace Tests\Unit;

use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\Modules\ComplaintsDashboardModule;
use App\Services\Swm\Dashboard\ReportingWindow;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComplaintsDashboardModuleTest extends TestCase
{
    use RefreshDatabase;

    private ComplaintsDashboardModule $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedMunicipalWards([1, 2, 3, 4, 5]);
        $this->module = app(ComplaintsDashboardModule::class);
    }

    public function test_build_includes_overview_submodule_with_blocks_in_order(): void
    {
        $result = $this->module->build($this->testPeriod());
        $keys = array_column($result['submodules'], 'key');

        $this->assertSame(['overview'], $keys);

        $blocks = $result['submodules'][0]['blocks'] ?? [];
        $types = array_column($blocks, 'type');

        $this->assertSame(['tiles', 'charts'], $types);
    }

    public function test_tiles_status_duplicate_and_resolution_percent(): void
    {
        $period = $this->testPeriod();
        $this->seedComplaintsForPeriod();

        $result = $this->module->build($period);
        $tiles = $this->tilesFromResult($result);
        $tileItems = $this->tileItemsFromResult($result);

        $this->assertSame(__('Complaint Resolution'), $tileItems[0]['label']);
        $this->assertSame('50.0 %', $tiles[__('Complaint Resolution')]);
        $this->assertSame('4', $tiles[__('Total Complaints Received')]);
        $this->assertSame('2', $tiles[__('Resolved Complaints')]);
        $this->assertSame('1', $tiles[__('Pending Complaints')]);
        $this->assertSame('1', $tiles[__('Others Complaints')]);
        $this->assertStringContainsString('%', $tiles[__('Duplicate-Complaint Rate')]);
    }

    public function test_charts_include_expected_ids_and_twelve_month_trend(): void
    {
        $period = $this->testPeriod();
        $this->seedComplaintsForPeriod();

        $charts = $this->chartsFromResult($this->module->build($period));

        $byType = $charts['swmChartComplaintsByType'];
        $this->assertCount(count(config('swm_complaints.complaint_types', [])), $byType['labels']);
        $this->assertTrue($byType['options']['staticCategoryAxis'] ?? false);

        $this->assertArrayHasKey('swmChartComplaintsByType', $charts);
        $this->assertArrayHasKey('swmChartComplaintsByWard', $charts);
        $this->assertArrayHasKey('swmChartComplaintsStatusByWard', $charts);
        $this->assertArrayHasKey('swmChartComplaintsChannel', $charts);
        $this->assertArrayHasKey('swmChartComplaintsResolutionByType', $charts);
        $this->assertArrayHasKey('swmChartComplaintsTypeByWard', $charts);
        $this->assertArrayHasKey('swmChartComplaintsTrend12m', $charts);

        $trend = $charts['swmChartComplaintsTrend12m'];
        $this->assertSame('line', $trend['type']);
        $this->assertCount(12, $trend['labels']);

        $heatmap = $charts['swmChartComplaintsTypeByWard'];
        $this->assertSame('stackedBar', $heatmap['type']);
        $this->assertFalse($heatmap['fullWidth']);
        $this->assertSame(280, $heatmap['height']);
        $this->assertSame(__('Complaint Type by Ward'), $heatmap['title']);
        $expectedWardLabels = ['1', '2', '3', '4', '5', __('Unknown')];
        $this->assertSame($expectedWardLabels, $heatmap['labels']);
        $this->assertCount(2, $heatmap['datasets']);

        $statusByWard = $charts['swmChartComplaintsStatusByWard'];
        $this->assertSame('stackedBar', $statusByWard['type']);
        $this->assertFalse($statusByWard['fullWidth']);
        $this->assertSame(280, $statusByWard['height']);
        $this->assertSame(__('Complaint Status by Ward'), $statusByWard['title']);
        $this->assertSame($expectedWardLabels, $statusByWard['labels']);
        $this->assertSame(__('Resolved'), $statusByWard['datasets'][0]['label']);
        $this->assertSame(__('Pending'), $statusByWard['datasets'][1]['label']);
        $this->assertSame(__('Others'), $statusByWard['datasets'][2]['label']);
        $this->assertSame([1, 1, 0, 0, 0, 0], $statusByWard['datasets'][0]['data']);
        $this->assertSame([1, 0, 0, 0, 0, 0], $statusByWard['datasets'][1]['data']);
        $this->assertSame([0, 0, 0, 0, 0, 1], $statusByWard['datasets'][2]['data']);
    }

    public function test_empty_complaint_window_returns_zeros(): void
    {
        $periodEnd = Carbon::parse('2026-04-30 23:59:59');
        $period = new DashboardReportingPeriod(
            Carbon::parse('2026-04-01'),
            $periodEnd,
            [
                DashboardReportingPeriod::SOURCE_HOUSEHOLD => ReportingWindow::empty($periodEnd),
                DashboardReportingPeriod::SOURCE_LANDFILL => ReportingWindow::empty($periodEnd),
                DashboardReportingPeriod::SOURCE_COMPLAINT => ReportingWindow::empty($periodEnd),
            ],
        );

        $tiles = $this->tilesFromResult($this->module->build($period));

        $this->assertSame('0', $tiles[__('Total Complaints Received')]);
    }

    private function testPeriod(): DashboardReportingPeriod
    {
        $periodEnd = Carbon::parse('2026-04-30 23:59:59');
        $epoch = Carbon::parse('2026-01-01')->startOfDay();

        return new DashboardReportingPeriod(
            Carbon::parse('2026-04-01'),
            $periodEnd,
            [
                DashboardReportingPeriod::SOURCE_HOUSEHOLD => ReportingWindow::empty($periodEnd),
                DashboardReportingPeriod::SOURCE_LANDFILL => ReportingWindow::empty($periodEnd),
                DashboardReportingPeriod::SOURCE_COMPLAINT => ReportingWindow::between($epoch, $periodEnd),
            ],
        );
    }

    /**
     * @param  list<int>  $wards
     */
    private function seedMunicipalWards(array $wards): void
    {
        foreach ($wards as $ward) {
            DB::table('layer_info.wards')->insertOrIgnore([
                'ward' => $ward,
            ]);
        }
    }

    private function seedComplaintsForPeriod(): void
    {
        $rows = [
            [
                'complaint_id' => 'CMP-TEST-001',
                'date_time' => '2026-03-15 10:00:00',
                'name' => 'A',
                'contact_number' => '01',
                'complaint_type' => 'waste_not_collected',
                'complaint_details' => 'd1',
                'submitted_through' => 'hotline',
                'complaint_status' => 'resolved',
                'ward_no' => '1',
                'duplicate_complaint' => false,
                'resolution_time_days' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'complaint_id' => 'CMP-TEST-002',
                'date_time' => '2026-04-10 10:00:00',
                'name' => 'B',
                'contact_number' => '02',
                'complaint_type' => 'waste_not_collected',
                'complaint_details' => 'd2',
                'submitted_through' => 'phone_call',
                'complaint_status' => 'resolved',
                'ward_no' => '2',
                'duplicate_complaint' => true,
                'resolution_time_days' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'complaint_id' => 'CMP-TEST-003',
                'date_time' => '2026-04-12 10:00:00',
                'name' => 'C',
                'contact_number' => '03',
                'complaint_type' => 'others',
                'complaint_details' => 'd3',
                'submitted_through' => 'complaint_form',
                'complaint_status' => 'pending',
                'ward_no' => '1',
                'duplicate_complaint' => false,
                'resolution_time_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'complaint_id' => 'CMP-TEST-004',
                'date_time' => '2026-04-20 10:00:00',
                'name' => 'D',
                'contact_number' => '04',
                'complaint_type' => 'others',
                'complaint_details' => 'd4',
                'submitted_through' => 'survey',
                'complaint_status' => 'escalated',
                'ward_no' => null,
                'duplicate_complaint' => false,
                'resolution_time_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($rows as $row) {
            DB::table('swm.complaints')->insert($row);
        }
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, string>
     */
    private function tilesFromResult(array $result): array
    {
        $tiles = [];
        foreach ($result['submodules'][0]['blocks'] ?? [] as $block) {
            if (($block['type'] ?? '') !== 'tiles') {
                continue;
            }
            foreach ($block['items'] ?? [] as $tile) {
                $tiles[$tile['label']] = $tile['value'];
            }
        }

        return $tiles;
    }

    /**
     * @param  array<string, mixed>  $result
     * @return list<array{label: string, value: string}>
     */
    private function tileItemsFromResult(array $result): array
    {
        $items = [];
        foreach ($result['submodules'][0]['blocks'] ?? [] as $block) {
            if (($block['type'] ?? '') !== 'tiles') {
                continue;
            }
            foreach ($block['items'] ?? [] as $tile) {
                $items[] = [
                    'label' => $tile['label'],
                    'value' => $tile['value'],
                ];
            }
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, array<string, mixed>>
     */
    private function chartsFromResult(array $result): array
    {
        $charts = [];
        foreach ($result['submodules'][0]['blocks'] ?? [] as $block) {
            if (($block['type'] ?? '') !== 'charts') {
                continue;
            }
            foreach ($block['items'] ?? [] as $chart) {
                $charts[$chart['id']] = $chart;
            }
        }

        return $charts;
    }
}
