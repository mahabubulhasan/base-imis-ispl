<?php

namespace App\Services\Swm\Dashboard\Billing;

use App\Models\BuildingInfo\Household;
use App\Models\Swm\BillCollectionPayment;
use App\Services\Swm\BillCollectionPaymentService;
use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\SwmDashboardAxisKeys;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class BillingDashboardMetrics
{
    private const TABLE_ROW_LIMIT = 50;

    private const TOP_ARREARS_WARDS = 10;

    public function __construct(
        protected BillCollectionPaymentService $paymentService,
    ) {
    }

    /**
     * @return array{
     *     total_billed_amount: string,
     *     due_for_this_month: string,
     *     total_due: string,
     *     total_revenue_collected: string,
     *     total_payable: string,
     *     total_paid: string,
     *     total_previous_due: string,
     *     total_previous_due_paid: string,
     *     billed_household_count: int,
     *     default_count: int,
     *     ward_arrears: array<string, string>,
     *     table_rows: list<array<string, mixed>>,
     * }
     */
    public function aggregate(DashboardReportingPeriod $period): array
    {
        $monthStart = $period->toMonth->copy()->startOfMonth();
        $monthDate = $monthStart->toDateString();

        $totalBilledAmount = '0.00';
        $dueForThisMonth = '0.00';
        $totalDue = '0.00';
        $totalPayable = '0.00';
        $totalPaid = '0.00';
        $totalPreviousDue = '0.00';
        $totalPreviousDuePaid = '0.00';
        $billedCount = 0;
        $defaultCount = 0;
        /** @var array<string, string> $wardArrears */
        $wardArrears = [];
        /** @var list<array<string, mixed>> $tableCandidates */
        $tableCandidates = [];

        $paymentsByHousehold = $this->paymentsForMonthKeyed($monthDate);

        Household::query()
            ->whereNull('deleted_at')
            ->activeStatus()
            ->whereNotNull('waste_charge')
            ->orderBy('id')
            ->chunkById(500, function ($sites) use (
                $monthStart,
                $paymentsByHousehold,
                &$totalBilledAmount,
                &$dueForThisMonth,
                &$totalDue,
                &$totalPayable,
                &$totalPaid,
                &$totalPreviousDue,
                &$totalPreviousDuePaid,
                &$billedCount,
                &$defaultCount,
                &$wardArrears,
                &$tableCandidates,
            ) {
                foreach ($sites as $site) {
                    if (! $site instanceof Household) {
                        continue;
                    }

                    $billedCount++;

                    $billableUnits = $this->paymentService->marginalBillableUnitCount($site, $monthStart);
                    if ($billableUnits > 0) {
                        $totalBilledAmount = bcadd(
                            $totalBilledAmount,
                            bcmul((string) $site->waste_charge, (string) $billableUnits, 2),
                            2,
                        );
                    }

                    $balance = $this->paymentService->balanceThroughMonth($site, $monthStart);
                    $closingDue = (string) ($balance['due'] ?? '0.00');
                    $currentDue = $this->paymentService->marginalDueForMonth($site, $monthStart);
                    $previousDue = $this->maxZero(bcsub($closingDue, $currentDue, 2));

                    $dueForThisMonth = bcadd($dueForThisMonth, $currentDue, 2);
                    $totalDue = bcadd($totalDue, $closingDue, 2);
                    $totalPayable = bcadd($totalPayable, bcadd($previousDue, $currentDue, 2), 2);
                    $totalPreviousDue = bcadd($totalPreviousDue, $previousDue, 2);

                    if (bccomp($closingDue, '0', 2) > 0) {
                        $defaultCount++;
                    }

                    $paymentRow = $paymentsByHousehold->get($site->id);
                    $previousPaid = (string) ($paymentRow->previous_due_paid ?? '0');
                    $revenue = (string) ($paymentRow->revenue_collected ?? '0');

                    $totalPaid = bcadd($totalPaid, $revenue, 2);
                    $totalPreviousDuePaid = bcadd($totalPreviousDuePaid, $previousPaid, 2);

                    $ward = $this->normalizeWard($site->ward);
                    if ($ward !== '') {
                        $wardArrears[$ward] = bcadd($wardArrears[$ward] ?? '0.00', $closingDue, 2);
                    }

                    $dueMonths = $this->countDueMonths($site, $monthStart);
                    if ($dueMonths >= 3) {
                        $tableCandidates[] = [
                            'holding_number' => (string) ($site->holding_number ?? ''),
                            'household_owner_name' => (string) ($site->household_owner_name ?? ''),
                            'ward' => $ward,
                            'fixed_service_fee' => (string) ($site->waste_charge ?? '0'),
                            'due_months' => $dueMonths,
                            'closing_due' => $closingDue,
                            'contact_number' => (string) ($site->contact_number ?? ''),
                            '_sort_closing' => (float) $closingDue,
                        ];
                    }
                }
            });

        usort($tableCandidates, static fn (array $a, array $b) => $b['_sort_closing'] <=> $a['_sort_closing']);
        $tableRows = array_slice(array_map(static function (array $row) {
            unset($row['_sort_closing']);

            return $row;
        }, $tableCandidates), 0, self::TABLE_ROW_LIMIT);

        return [
            'total_billed_amount' => $totalBilledAmount,
            'due_for_this_month' => $dueForThisMonth,
            'total_due' => $totalDue,
            'total_revenue_collected' => $this->sumRevenueForMonth($monthDate),
            'total_payable' => $totalPayable,
            'total_paid' => $totalPaid,
            'total_previous_due' => $totalPreviousDue,
            'total_previous_due_paid' => $totalPreviousDuePaid,
            'billed_household_count' => $billedCount,
            'default_count' => $defaultCount,
            'ward_arrears' => $wardArrears,
            'table_rows' => $tableRows,
        ];
    }

    /**
     * Bill collected per ward, cumulative through the end of the given month.
     *
     * @return array<string, float>
     */
    public function billCollectionByWard(Carbon $endMonth): array
    {
        $monthDate = $endMonth->copy()->startOfMonth()->toDateString();

        $rows = BillCollectionPayment::query()
            ->leftJoin('building_info.households as h', 'swm.bill_collection_payments.household_id', '=', 'h.id')
            ->whereNull('swm.bill_collection_payments.deleted_at')
            ->where(function ($query) {
                $query->whereNull('h.deleted_at')
                    ->orWhereNull('h.id');
            })
            ->whereDate('swm.bill_collection_payments.payment_for_month', '<=', $monthDate)
            ->selectRaw('
                h.ward as ward,
                COALESCE(SUM(swm.bill_collection_payments.amount + COALESCE(swm.bill_collection_payments.due_paid, 0)), 0)::float as collected
            ')
            ->groupBy('h.ward')
            ->orderBy('h.ward')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $ward = $this->normalizeWard($row->ward);
            if ($ward === '') {
                $ward = SwmDashboardAxisKeys::WARD_AXIS_UNKNOWN_KEY;
            }
            $out[$ward] = round((float) $row->collected, 2);
        }

        return $out;
    }

    /**
     * @return array<string, float>
     */
    public function averageFeeByWard(): array
    {
        $rows = Household::query()
            ->whereNull('deleted_at')
            ->activeStatus()
            ->whereNotNull('waste_charge')
            ->whereNotNull('ward')
            ->selectRaw('ward, AVG(waste_charge)::float as avg_fee')
            ->groupBy('ward')
            ->orderBy('ward')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $ward = $this->normalizeWard($row->ward);
            if ($ward !== '') {
                $out[$ward] = round((float) $row->avg_fee, 2);
            }
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    public function topArrearsWards(array $wardArrears, int $limit = self::TOP_ARREARS_WARDS): array
    {
        uasort($wardArrears, static fn (string $a, string $b) => bccomp($b, $a, 2));

        return array_slice($wardArrears, 0, $limit, true);
    }

    /**
     * @return array<string, float> month key Y-m-01 => revenue
     */
    public function revenueByMonth(Carbon $endMonth, int $months = 12): array
    {
        $endMonth = $endMonth->copy()->startOfMonth();
        $startMonth = $endMonth->copy()->subMonths($months - 1);

        $rows = BillCollectionPayment::query()
            ->whereNull('deleted_at')
            ->whereDate('payment_for_month', '>=', $startMonth->toDateString())
            ->whereDate('payment_for_month', '<=', $endMonth->toDateString())
            ->selectRaw("
                date_trunc('month', payment_for_month)::date as month_key,
                COALESCE(SUM(amount + COALESCE(due_paid, 0)), 0)::float as revenue
            ")
            ->groupByRaw("date_trunc('month', payment_for_month)")
            ->orderByRaw("date_trunc('month', payment_for_month)")
            ->get();

        $indexed = [];
        foreach ($rows as $row) {
            $key = Carbon::parse($row->month_key)->format('Y-m-01');
            $indexed[$key] = (float) $row->revenue;
        }

        $out = [];
        for ($m = $startMonth->copy(); $m->lte($endMonth); $m->addMonth()) {
            $key = $m->format('Y-m-01');
            $out[$key] = $indexed[$key] ?? 0.0;
        }

        return $out;
    }

    /**
     * @return array<string, float> payment_method key => collected amount (Taka)
     */
    public function paymentMethodCollectedThroughMonth(Carbon $endMonth): array
    {
        $endDate = $endMonth->copy()->startOfMonth()->toDateString();

        $rows = BillCollectionPayment::query()
            ->whereNull('deleted_at')
            ->whereDate('payment_for_month', '<=', $endDate)
            ->selectRaw('payment_method, COALESCE(SUM(amount + COALESCE(due_paid, 0)), 0)::float as collected')
            ->groupBy('payment_method')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row->payment_method] = (float) $row->collected;
        }

        return $out;
    }

    protected function paymentsForMonthKeyed(string $monthDate): Collection
    {
        return BillCollectionPayment::query()
            ->whereNull('deleted_at')
            ->whereDate('payment_for_month', $monthDate)
            ->select('household_id')
            ->selectRaw('SUM(amount) as current_month_paid')
            ->selectRaw('SUM(COALESCE(due_paid, 0)) as previous_due_paid')
            ->selectRaw('SUM(amount + COALESCE(due_paid, 0)) as revenue_collected')
            ->groupBy('household_id')
            ->get()
            ->keyBy('household_id');
    }

    protected function sumRevenueForMonth(string $monthDate): string
    {
        $sum = BillCollectionPayment::query()
            ->whereNull('deleted_at')
            ->whereDate('payment_for_month', $monthDate)
            ->sum(DB::raw('amount + COALESCE(due_paid, 0)'));

        return number_format((float) $sum, 2, '.', '');
    }

    protected function countDueMonths(Household $site, Carbon $monthStart): int
    {
        $anchor = $this->paymentService->billingAnchor($site);
        $rangeStart = $anchor && $anchor->lte($monthStart)
            ? $anchor->copy()->startOfMonth()
            : $monthStart->copy()->startOfMonth();

        if ($site->waste_charge === null) {
            return 0;
        }

        $remainingByMonth = $this->paymentService->outstandingByMonthInRange(
            $site,
            $rangeStart,
            $monthStart->copy()->startOfMonth(),
        );

        $count = 0;
        foreach ($remainingByMonth as $remaining) {
            if (bccomp((string) $remaining, '0', 2) > 0) {
                $count++;
            }
        }

        return $count;
    }

    protected function normalizeWard(mixed $ward): string
    {
        if ($ward === null || $ward === '') {
            return '';
        }

        return trim((string) $ward);
    }

    protected function maxZero(string $value): string
    {
        if (bccomp($value, '0', 2) < 0) {
            return '0.00';
        }

        return $value;
    }
}
