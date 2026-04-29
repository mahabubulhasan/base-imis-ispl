<?php

namespace Tests\Unit;

use App\Services\Swm\BillCollectionPaymentService;
use App\Services\Swm\SwmDashboardKpiService;
use App\Services\Swm\Queries\ComplaintStatusCounts;
use App\Services\Swm\Queries\HouseholdKpiCoreAggregate;
use App\Services\Swm\Queries\VehicleFleetStats;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

/**
 * DTO mapping tests (no DB) plus documented fixed-query budget for {@see SwmDashboardKpiService::buildDashboardData()}.
 *
 * Insight: Aggregates (`coreAggregate`, `statusCountsInRange`, merged vehicle stats) replace many per-metric
 * household/complaint round-trips. Billing still loads all households and sums payments; inner due queries are
 * suppressed in the live budget test via a mocked {@see BillCollectionPaymentService::marginalDueForMonth()}.
 *
 * Query report: the PostgreSQL live test prints a multi-line report to STDOUT (all captured SQL + budget breakdown).
 * Disable with: SWM_KPI_QUERY_REPORT=0 phpunit ...
 */
class SwmKpiAggregatesDtoTest extends TestCase
{
    /**
     * Expected SQL round-trips for one {@see SwmDashboardKpiService::buildDashboardData()} call,
     * excluding queries fired inside {@see BillCollectionPaymentService::marginalDueForMonth()} (payment lookups).
     *
     * If you change the service, update this number and {@see self::DASHBOARD_FIXED_QUERY_SECTIONS} together.
     */
    public const DASHBOARD_FIXED_QUERY_BUDGET = 20;

    /**
     * @var array<string, int> Human-readable breakdown (messages for maintainers / assertion failures).
     */
    private const DASHBOARD_FIXED_QUERY_SECTIONS = [
        'bootstrap: coreAggregate() + statusCountsInRange()' => 2,
        'existingKpis: Sts count + Landfill count' => 2,
        'coverageMetrics: averageDailyWasteByWard + buildings count' => 2,
        'billingMetrics: payments sum + activeForBilling get (mock skips due-loop DB)' => 2,
        'complaintMetrics: (reuses complaintStatus; 0 extra)' => 0,
        'serviceProviderMetrics: org + worker + fleet merge + 3× grouped/get summaries' => 6,
        'serviceFacilityMetrics: STS list + Landfill list' => 2,
        'cityStatistics: wards count + city area selectOne' => 2,
        'wardStatistics: wardStatisticsRows' => 1,
        'vehicleStatistics: grouped by vehicle type' => 1,
    ];

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @param  list<string>  $sqlLog
     */
    private function formatDashboardQueryReport(int $actualCount, int $expectedCount, array $sqlLog): string
    {
        $lines = [
            sprintf('buildDashboardData() — SQL round-trips: %d (budget %d) %s', $actualCount, $expectedCount, $actualCount === $expectedCount ? 'OK' : 'MISMATCH'),
            '',
            'Expected section budget (fixed queries; billing due-loop mocked):',
        ];
        foreach (self::DASHBOARD_FIXED_QUERY_SECTIONS as $label => $n) {
            $lines[] = sprintf('  [%d] %s', $n, $label);
        }
        $lines[] = sprintf('  --- total %d', array_sum(self::DASHBOARD_FIXED_QUERY_SECTIONS));
        $lines[] = '';
        $lines[] = 'Captured statements (order of execution):';
        $i = 1;
        foreach ($sqlLog as $sql) {
            $trimmed = preg_replace('/\s+/', ' ', trim($sql));
            if (strlen($trimmed) > 220) {
                $trimmed = substr($trimmed, 0, 217).'...';
            }
            $lines[] = sprintf('  %2d. %s', $i++, $trimmed);
        }

        return implode("\n", $lines);
    }

    private function printQueryReport(string $report): void
    {
        if (getenv('SWM_KPI_QUERY_REPORT') === '0') {
            return;
        }
        $banner = "\n".str_repeat('=', 72)."\n SWM KPI dashboard — query report\n".str_repeat('=', 72)."\n";
        $out = $banner.$report."\n".str_repeat('=', 72)."\n";
        if (defined('STDOUT') && is_resource(STDOUT)) {
            fwrite(STDOUT, $out);
            fflush(STDOUT);
        }
    }

    public function test_dashboard_fixed_query_sections_sum_matches_budget_with_insight_message(): void
    {
        $sum = array_sum(self::DASHBOARD_FIXED_QUERY_SECTIONS);

        $breakdownLines = [];
        foreach (self::DASHBOARD_FIXED_QUERY_SECTIONS as $label => $count) {
            $breakdownLines[] = sprintf('  - %s: %d', $label, $count);
        }
        $insight = "SWM dashboard fixed query budget insight:\n"
            ."Each line is one round-trip batch expectation for buildDashboardData (billing due-loop internals excluded).\n"
            .implode("\n", $breakdownLines)."\n"
            .sprintf('  => total %d (budget constant %d)', $sum, self::DASHBOARD_FIXED_QUERY_BUDGET);

        $this->assertSame(
            self::DASHBOARD_FIXED_QUERY_BUDGET,
            $sum,
            "DASHBOARD_FIXED_QUERY_SECTIONS must sum to DASHBOARD_FIXED_QUERY_BUDGET.\n".$insight
        );
    }

    /**
     * Live count against the app DB (PostgreSQL only: aggregates use FILTER).
     * Skips when driver is not pgsql or DB is unreachable.
     */
    public function test_build_dashboard_data_fixed_query_count_matches_budget_on_postgres(): void
    {
        $driver = (string) config('database.connections.'.config('database.default').'.driver');
        if ($driver !== 'pgsql') {
            $this->markTestSkipped(
                'Insight: KPI SQL uses PostgreSQL FILTER aggregates; set DB to pgsql to assert live query count. '
                .'Current driver: '.$driver
            );
        }

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database not available for live query count: '.$e->getMessage());
        }

        $billing = Mockery::mock(BillCollectionPaymentService::class);
        $billing->shouldReceive('marginalDueForMonth')
            ->zeroOrMoreTimes()
            ->andReturn('0.00');
        $this->instance(BillCollectionPaymentService::class, $billing);

        $count = 0;
        $sqlLog = [];
        DB::listen(function ($query) use (&$count, &$sqlLog) {
            $count++;
            $sqlLog[] = $query->sql;
        });

        $service = app(SwmDashboardKpiService::class);
        $service->buildDashboardData(null, null);

        $expected = self::DASHBOARD_FIXED_QUERY_BUDGET;
        $report = $this->formatDashboardQueryReport($count, $expected, $sqlLog);
        $this->printQueryReport($report);

        $insight = "Assertion failed. Full report was printed above (STDOUT).\n"
            ."Live dashboard query count: {$count} (expected fixed budget {$expected}).\n"
            ."Update DASHBOARD_FIXED_QUERY_BUDGET and DASHBOARD_FIXED_QUERY_SECTIONS if the change is intentional.\n"
            ."First 5 statements:\n"
            .implode("\n", array_slice(array_map(fn ($s) => '  - '.$s, $sqlLog), 0, 5));

        $this->assertSame($expected, $count, $insight);
    }

    public function test_household_kpi_core_aggregate_from_null_row_is_zeroed(): void
    {
        $agg = HouseholdKpiCoreAggregate::fromDatabaseRow(null);

        $this->assertSame(0, $agg->householdCount, 'householdCount should be 0 for null DB row (empty aggregate)');
        $this->assertSame(0.0, $agg->totalDailyWasteVolumeKg);
        $this->assertSame(0.0, $agg->formalCollectedKg);
        $this->assertSame(0, $agg->coveredHouseholdCount);
        $this->assertSame(0, $agg->segregatedCount);
        $this->assertSame(0, $agg->segregationNotYesCount);
        $this->assertSame(0, $agg->totalFamilyMembers);
        $this->assertSame(0, $agg->distinctHoldingsCount);
        $this->assertSame(0.0, $agg->avgDailyWasteVolume);
    }

    public function test_household_kpi_core_aggregate_maps_database_row(): void
    {
        $row = (object) [
            'household_count' => 10,
            'total_daily_waste_volume_kg' => '100.5',
            'formal_collected_kg' => '40',
            'covered_household_count' => 6,
            'segregated_count' => 3,
            'segregation_not_yes_count' => 7,
            'total_family_members' => 42,
            'distinct_holdings_count' => 8,
            'avg_daily_waste_volume' => '2.25',
        ];

        $agg = HouseholdKpiCoreAggregate::fromDatabaseRow($row);

        $this->assertSame(10, $agg->householdCount, 'core aggregate maps household_count from query row');
        $this->assertSame(100.5, $agg->totalDailyWasteVolumeKg);
        $this->assertSame(40.0, $agg->formalCollectedKg);
        $this->assertSame(6, $agg->coveredHouseholdCount);
        $this->assertSame(3, $agg->segregatedCount);
        $this->assertSame(7, $agg->segregationNotYesCount);
        $this->assertSame(42, $agg->totalFamilyMembers);
        $this->assertSame(8, $agg->distinctHoldingsCount);
        $this->assertSame(2.25, $agg->avgDailyWasteVolume);
    }

    public function test_complaint_status_counts_from_null_row(): void
    {
        $c = ComplaintStatusCounts::fromDatabaseRow(null);

        $this->assertSame(0, $c->total, 'ComplaintStatusCounts null row => all zeros');
        $this->assertSame(0, $c->resolved);
        $this->assertSame(0, $c->pending);
        $this->assertSame(0, $c->inProcess);
        $this->assertSame(0, $c->closed);
    }

    public function test_vehicle_fleet_stats_from_row(): void
    {
        $v = VehicleFleetStats::fromDatabaseRow((object) ['vehicle_count' => 5, 'total_capacity' => '12.5']);

        $this->assertSame(5, $v->activeVehicleCount, 'VehicleFleetStats maps vehicle_count');
        $this->assertSame(12.5, $v->totalCapacity, 'total_capacity coerced to float');
    }
}
