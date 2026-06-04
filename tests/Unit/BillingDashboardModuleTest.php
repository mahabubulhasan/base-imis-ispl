<?php

namespace Tests\Unit;

use App\Models\BuildingInfo\Household;
use App\Models\Swm\BillCollectionPayment;
use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\Modules\BillingDashboardModule;
use App\Services\Swm\Dashboard\ReportingWindow;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingDashboardModuleTest extends TestCase
{
    use RefreshDatabase;

    private BillingDashboardModule $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->module = app(BillingDashboardModule::class);
    }

    public function test_build_includes_billing_submodule_with_blocks(): void
    {
        $result = $this->module->build($this->testPeriod());
        $keys = array_column($result['submodules'], 'key');

        $this->assertSame(['billing'], $keys);

        $blocks = $result['submodules'][0]['blocks'] ?? [];
        $types = array_column($blocks, 'type');

        $this->assertSame(['tiles', 'charts', 'table'], $types);
    }

    public function test_tiles_and_revenue_for_selected_month(): void
    {
        $period = $this->testPeriod();
        $site = $this->createHousehold([
            'waste_charge' => 500,
            'using_this_service_since' => '2026-04-01',
            'ward' => '5',
        ]);

        BillCollectionPayment::query()->create([
            'household_id' => $site->id,
            'holding_number' => $site->holding_number,
            'customer_id' => $site->household_id,
            'amount' => 300,
            'due_paid' => 100,
            'payment_for_month' => '2026-04-01',
            'payment_time' => '2026-04-15 10:00:00',
            'payment_method' => 'cash',
        ]);

        $tiles = $this->tilesFromResult($this->module->build($period));

        $this->assertSame('500', $tiles[__('Total Billed Amount (Taka)')]);
        $this->assertSame('100', $tiles[__('Due for This Month (Taka)')]);
        $this->assertSame('400', $tiles[__('Total Bill Collected (Taka)']);
        $this->assertSame('100', $tiles[__('Total Due (Taka)']);
    }

    public function test_bill_collection_by_ward_chart_sums_selected_month(): void
    {
        $period = $this->testPeriod();
        $wardFive = $this->createHousehold(['ward' => '5']);
        $wardSeven = $this->createHousehold(['ward' => '7']);

        BillCollectionPayment::query()->create([
            'household_id' => $wardFive->id,
            'holding_number' => $wardFive->holding_number,
            'customer_id' => $wardFive->household_id,
            'amount' => 300,
            'due_paid' => 100,
            'payment_for_month' => '2026-04-01',
            'payment_time' => '2026-04-15 10:00:00',
            'payment_method' => 'cash',
        ]);

        BillCollectionPayment::query()->create([
            'household_id' => $wardSeven->id,
            'holding_number' => $wardSeven->holding_number,
            'customer_id' => $wardSeven->household_id,
            'amount' => 200,
            'due_paid' => 0,
            'payment_for_month' => '2026-04-01',
            'payment_time' => '2026-04-16 10:00:00',
            'payment_method' => 'cash',
        ]);

        BillCollectionPayment::query()->create([
            'household_id' => $wardFive->id,
            'holding_number' => $wardFive->holding_number,
            'customer_id' => $wardFive->household_id,
            'amount' => 50,
            'due_paid' => 0,
            'payment_for_month' => '2026-03-01',
            'payment_time' => '2026-03-15 10:00:00',
            'payment_method' => 'cash',
        ]);

        $charts = $this->chartsFromResult($this->module->build($period));
        $chart = $charts['swmChartBillingCollectionByWard'];

        $this->assertSame('bar', $chart['type']);
        $this->assertSame(__('Bill Collection by Ward'), $chart['title']);
        $this->assertSame(['5', '7'], $chart['labels']);
        $this->assertSame([400.0, 200.0], $chart['datasets'][0]['data']);
    }

    public function test_revenue_trend_chart_has_twelve_months(): void
    {
        $period = $this->testPeriod();
        $charts = $this->chartsFromResult($this->module->build($period));

        $trend = $charts['swmChartBillingRevenueTrend'];
        $this->assertSame('line', $trend['type']);
        $this->assertCount(12, $trend['labels']);
    }

    public function test_payment_method_chart_counts_cumulative_through_period(): void
    {
        $period = $this->testPeriod();
        $site = $this->createHousehold();

        BillCollectionPayment::query()->create([
            'household_id' => $site->id,
            'holding_number' => $site->holding_number,
            'customer_id' => $site->household_id,
            'amount' => 100,
            'due_paid' => 0,
            'payment_for_month' => '2026-03-01',
            'payment_time' => '2026-03-10 10:00:00',
            'payment_method' => 'cash',
        ]);

        BillCollectionPayment::query()->create([
            'household_id' => $site->id,
            'holding_number' => $site->holding_number,
            'customer_id' => $site->household_id,
            'amount' => 50,
            'due_paid' => 0,
            'payment_for_month' => '2026-04-01',
            'payment_time' => '2026-04-10 10:00:00',
            'payment_method' => 'bank_transfer',
        ]);

        BillCollectionPayment::query()->create([
            'household_id' => $site->id,
            'holding_number' => $site->holding_number,
            'customer_id' => $site->household_id,
            'amount' => 50,
            'due_paid' => 0,
            'payment_for_month' => '2026-05-01',
            'payment_time' => '2026-05-10 10:00:00',
            'payment_method' => 'cash',
        ]);

        $charts = $this->chartsFromResult($this->module->build($period));
        $donut = $charts['swmChartBillingPaymentMethod'];

        $this->assertSame('doughnut', $donut['type']);
        $cashIndex = array_search(__('Cash'), $donut['labels'], true);
        $this->assertNotFalse($cashIndex);
        $this->assertSame(1, $donut['datasets'][0]['data'][$cashIndex]);
    }

    public function test_table_includes_household_with_three_or_more_due_months(): void
    {
        $period = $this->testPeriod();
        $site = $this->createHousehold([
            'holding_number' => 'HN-9001',
            'household_owner_name' => 'Arrears Owner',
            'ward' => '7',
            'contact_number' => '01700000000',
            'waste_charge' => 200,
            'using_this_service_since' => '2026-01-01',
        ]);

        $result = $this->module->build($period);
        $tableBlock = $this->tableBlockFromResult($result);

        $this->assertNotEmpty($tableBlock['rows']);
        $holdingNumbers = array_column($tableBlock['rows'], 'holding_number');
        $this->assertContains('HN-9001', $holdingNumbers);
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
    private function createHousehold(array $overrides = []): Household
    {
        static $counter = 0;
        $counter++;

        return Household::forceCreate(array_merge([
            'household_id' => 'HH-TEST-'.$counter,
            'household_owner_name' => 'Test Owner '.$counter,
            'contact_number' => '9800000000',
            'bin' => 'BIN-'.$counter,
            'ward' => '1',
            'holding_number' => 'HN-'.$counter,
            'waste_charge' => 100,
            'using_this_service_since' => '2026-01-01',
            'status' => Household::STATUS_ACTIVE,
            'is_owner' => true,
            'is_lic' => false,
            'segregation_practiced' => false,
            'waste_bin_provided' => false,
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ], $overrides));
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

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function tableBlockFromResult(array $result): array
    {
        foreach ($result['submodules'][0]['blocks'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'table') {
                return $block;
            }
        }

        return [];
    }
}
