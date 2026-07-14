<?php

namespace App\Services\Swm;

use App\Models\BuildingInfo\Household;
use App\Models\Swm\BillCollectionPayment;
use App\Services\Formatting\Currency;
use App\Services\Formatting\CurrencyFormatter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class BillCollectionBillingStatusService
{
    public function __construct(
        protected BillCollectionPaymentService $billCollectionPaymentService,
        protected CurrencyFormatter $currencyFormatter,
    ) {
    }

    /**
     * Global summary cards (not affected by table filters).
     *
     * - bill_collected_this_month: sum of payments for the current calendar month only.
     * - due_for_this_month: sum of marginal due for the current calendar month only (per site).
     * - total_due: sum of cumulative outstanding through the current calendar month (balanceThroughMonth).
     * - total_revenue_collected: sum of payments in current calendar year (payment_for_month year = now).
     *
     * @return array{bill_collected_this_month: string, due_for_this_month: string, total_due: string, total_revenue_collected: string}
     */
    public function summary(): array
    {
        $currentMonthStart = Carbon::now()->subMonthNoOverflow()->startOfMonth();

        $dueThisMonthMarginal = '0.00';
        $totalDueCumulative = '0.00';

        Household::query()
            ->whereNull('deleted_at')
            ->activeStatus()
            ->orderBy('id')
            ->chunkById(500, function ($sites) use ($currentMonthStart, &$dueThisMonthMarginal, &$totalDueCumulative) {
                foreach ($sites as $site) {
                    if (! $site instanceof Household) {
                        continue;
                    }
                    $marginal = $this->billCollectionPaymentService->marginalDueForMonth($site, $currentMonthStart);
                    $dueThisMonthMarginal = bcadd($dueThisMonthMarginal, $marginal, 2);
                    $cum = $this->dueThroughMonth($site, $currentMonthStart);
                    if ($cum !== null && is_numeric($cum)) {
                        $totalDueCumulative = bcadd($totalDueCumulative, (string) $cum, 2);
                    }
                }
            });

        $sumThisMonth = BillCollectionPayment::query()
            ->whereNull('deleted_at')
            ->whereDate('payment_for_month', $currentMonthStart->toDateString())
            ->sum(DB::raw('amount + COALESCE(due_paid, 0)'));

        /** Summary card: calendar year-to-date on payment_for_month (switch here if product wants all-time). */
        $sum = BillCollectionPayment::query()
            ->whereNull('deleted_at')
            ->whereYear('payment_for_month', (int) Carbon::now()->year)
            ->sum(DB::raw('amount + COALESCE(due_paid, 0)'));
        $revenue = $this->currencyFormatter->format(Currency::TK, (string) $sum);

        return [
            'bill_collected_this_month' => $this->currencyFormatter->format(Currency::TK, (string) $sumThisMonth),
            'due_for_this_month' => $this->currencyFormatter->format(Currency::TK, $dueThisMonthMarginal),
            'total_due' => $this->currencyFormatter->format(Currency::TK, $totalDueCumulative),
            'total_revenue_collected' => $revenue,
        ];
    }

    /**
     * @param  array<string, mixed>  $data  Request + DataTables params
     */
    public function getStatusTable(array $data)
    {
        $data = is_array($data) ? $data : [];

        $monthTo = $this->resolveMonthTo($data);
        $billedMonthTo = $this->toBilledMonth($monthTo);
        $query = $this->baseStatusQuery($monthTo);

        return DataTables::of($query)
            ->filter(function (Builder $q) use ($data) {
                $this->applyTableFilters($q, $data);
            }, false)
            ->orderColumn('holding_number', 'building_info.households.holding_number $1')
            ->orderColumn('household_id', 'building_info.households.household_id $1')
            ->orderColumn('household_owner_name', 'building_info.households.household_owner_name $1')
            ->orderColumn('father_or_husband_name', 'building_info.households.father_or_husband_name $1')
            ->orderColumn('area_mohalla_name', 'building_info.households.area_mohalla_name $1')
            ->orderColumn('sub_location', 'building_info.households.area_mohalla_name $1')
            ->orderColumn('ward', 'building_info.households.ward $1')
            ->orderColumn('contact_number', 'building_info.households.contact_number $1')
            ->orderColumn('current_month_paid', 'current_month_paid $1')
            ->orderColumn('previous_due_paid', 'previous_due_paid $1')
            ->orderColumn('revenue_collected', 'revenue_collected $1')
            ->addColumn('household_owner_name', function (Household $site) {
                return (string) ($site->household_owner_name ?? '');
            })
            ->addColumn('father_or_husband_name', function (Household $site) {
                return (string) ($site->father_or_husband_name ?? '');
            })
            ->addColumn('area_mohalla_name', function (Household $site) {
                return (string) ($site->area_mohalla_name ?? '');
            })
            ->addColumn('sub_location', function (Household $site) {
                return (string) ($site->area_mohalla_name ?? '');
            })
            ->addColumn('ward', function (Household $site) {
                $w = $site->ward;

                return ($w !== null && $w !== '') ? (string) $w : '';
            })
            ->addColumn('contact_number', function (Household $site) {
                return (string) ($site->contact_number ?? '');
            })
            ->addColumn('current_service_fee', function (Household $site) {
                return $this->currencyFormatter->format(Currency::TK, (string) ($site->waste_charge ?? 0));
            })
            ->addColumn('due_current_month', function (Household $site) use ($billedMonthTo) {
                return $this->currencyFormatter->format(Currency::TK, $this->currentMonthDueFromOutstandingMap($site, $billedMonthTo));
            })
            ->addColumn('due_months_of', function (Household $site) use ($billedMonthTo) {
                return $this->monthsWithMarginalDueLabelsInRange($site, $this->dueRangeStart($site, $billedMonthTo), $billedMonthTo);
            })
            ->addColumn('total_due_amount', function (Household $site) use ($billedMonthTo) {
                return $this->currencyFormatter->format(Currency::TK, $this->billingStatusAmounts($site, $billedMonthTo)['payable']);
            })
            ->addColumn('previous_due_amount', function (Household $site) use ($billedMonthTo) {
                return $this->currencyFormatter->format(Currency::TK, $this->billingStatusAmounts($site, $billedMonthTo)['previous_due']);
            })
            ->addColumn('current_month_paid', function (Household $site) {
                return $this->currencyFormatter->format(Currency::TK, (string) ($site->current_month_paid ?? 0));
            })
            ->addColumn('previous_due_paid', function (Household $site) {
                return $this->currencyFormatter->format(Currency::TK, (string) ($site->previous_due_paid ?? 0));
            })
            ->editColumn('revenue_collected', function (Household $site) {
                return $this->currencyFormatter->format(Currency::TK, (string) ($site->revenue_collected ?? 0));
            })
            ->addColumn('remaining_due', function (Household $site) use ($billedMonthTo) {
                return $this->currencyFormatter->format(Currency::TK, $this->billingStatusAmounts($site, $billedMonthTo)['closing']);
            })
            ->rawColumns([])
            ->make(true);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{rows: list<array<string, string>>, month_to: Carbon}
     */
    public function getStatusRowsForExport(array $data): array
    {
        $data = is_array($data) ? $data : [];
        $monthTo = $this->resolveMonthTo($data);
        $billedMonthTo = $this->toBilledMonth($monthTo);
        $query = $this->baseStatusQuery($monthTo);
        $this->applyTableFilters($query, $data);

        $rows = [];
        $serial = 1;
        foreach ($query->orderBy('building_info.households.holding_number')->orderBy('building_info.households.household_id')->get() as $site) {
            if (! $site instanceof Household) {
                continue;
            }
            $amounts = $this->billingStatusAmounts($site, $billedMonthTo);

            $rows[] = [
                'sl' => (string) $serial++,
                'holding_number' => (string) ($site->holding_number ?? ''),
                'household_id' => (string) ($site->household_id ?? ''),
                'household_owner_name' => (string) ($site->household_owner_name ?? ''),
                'father_or_husband_name' => (string) ($site->father_or_husband_name ?? ''),
                'area_mohalla_name' => (string) ($site->area_mohalla_name ?? ''),
                'sub_location' => (string) ($site->area_mohalla_name ?? ''),
                'ward' => ($site->ward !== null && $site->ward !== '') ? (string) $site->ward : '',
                'contact_number' => (string) ($site->contact_number ?? ''),
                'current_service_fee' => $this->currencyFormatter->format(Currency::TK, (string) ($site->waste_charge ?? 0)),
                'previous_due_amount' => $this->currencyFormatter->format(Currency::TK, $amounts['previous_due']),
                'due_current_month' => $this->currencyFormatter->format(Currency::TK, $amounts['current_due']),
                'total_due_amount' => $this->currencyFormatter->format(Currency::TK, $amounts['payable']),
                'due_months_of' => $this->monthsWithMarginalDueLabelsInRange($site, $this->dueRangeStart($site, $billedMonthTo), $billedMonthTo),
                'current_month_paid' => $this->currencyFormatter->format(Currency::TK, (string) ($site->current_month_paid ?? 0)),
                'previous_due_paid' => $this->currencyFormatter->format(Currency::TK, (string) ($site->previous_due_paid ?? 0)),
                'revenue_collected' => $this->currencyFormatter->format(Currency::TK, (string) ($site->revenue_collected ?? 0)),
                'remaining_due' => $this->currencyFormatter->format(Currency::TK, $amounts['closing']),
            ];
        }

        return [
            'rows' => $rows,
            'month_to' => $monthTo,
        ];
    }

    /**
     * Latest selectable report end month (current calendar month). Selecting the
     * current month yields the same figures as last month, since no payment's
     * payment_for_month can ever reach the current month under the transaction_month
     * derivation rule — this cap is permissive, not a behavior change to the numbers.
     */
    public function maxAllowedMonthTo(): Carbon
    {
        return Carbon::now()->startOfMonth();
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function resolveMonthTo(array $data): Carbon
    {
        $maxMonthTo = $this->maxAllowedMonthTo();

        return $this->clampMonthTo(
            $this->parseMonthStart($data['month_to'] ?? null) ?? $maxMonthTo->copy()
        );
    }

    protected function clampMonthTo(Carbon $month): Carbon
    {
        $month = $month->copy()->startOfMonth();
        $max = $this->maxAllowedMonthTo();

        return $month->gt($max) ? $max->copy() : $month;
    }

    protected function baseStatusQuery(Carbon $monthTo): Builder
    {
        $toDate = $monthTo->toDateString();

        $revenueSub = BillCollectionPayment::query()
            ->select('household_id')
            ->selectRaw('SUM(amount) as current_month_paid')
            ->selectRaw('SUM(COALESCE(due_paid, 0)) as previous_due_paid')
            ->selectRaw('SUM(amount + COALESCE(due_paid, 0)) as revenue_collected')
            ->whereNull('deleted_at')
            ->whereDate('transaction_month', '<=', $toDate)
            ->groupBy('household_id');

        return Household::query()
            ->select('building_info.households.*')
            ->whereNull('building_info.households.deleted_at')
            ->where('building_info.households.status', Household::STATUS_ACTIVE)
            ->leftJoinSub($revenueSub->toBase(), 'rev', 'building_info.households.id', '=', 'rev.household_id')
            ->addSelect(DB::raw('COALESCE(rev.current_month_paid, 0) as current_month_paid'))
            ->addSelect(DB::raw('COALESCE(rev.previous_due_paid, 0) as previous_due_paid'))
            ->addSelect(DB::raw('COALESCE(rev.revenue_collected, 0) as revenue_collected'));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function dueThroughMonth(Household $site, Carbon $asOfMonth): ?string
    {
        $balance = $this->billCollectionPaymentService->balanceThroughMonth($site, $asOfMonth);
        if (! is_array($balance)) {
            return null;
        }

        $due = $balance['due'] ?? null;
        if ($due === null) {
            return null;
        }

        return (string) $due;
    }

    /**
     * Billed (payment_for_month-equivalent) month for a transaction-axis month, mirroring
     * BillCollectionPaymentService::storeOrUpdate()'s transaction_month -> payment_for_month
     * derivation. month_to in this report is transaction-axis (like the payment form's
     * "Transaction Month" field); every due/obligation figure needs the billed month
     * instead, since payment_for_month always lags transaction_month by one month.
     */
    protected function toBilledMonth(Carbon $transactionMonth): Carbon
    {
        return $transactionMonth->copy()->subMonthNoOverflow()->startOfMonth();
    }

    /**
     * Start of a household's FIFO/obligation window: its own billing anchor, falling back
     * to {@see $rangeEnd} if the household has no anchor set. Always anchor-based — there is
     * no report-wide "month from" filter for due/obligation figures, only a "through" ceiling.
     */
    protected function dueRangeStart(Household $site, Carbon $rangeEnd): Carbon
    {
        $anchor = $this->billCollectionPaymentService->billingAnchor($site);

        return $anchor?->copy()->startOfMonth() ?? $rangeEnd->copy()->startOfMonth();
    }

    /**
     * Payable / previous / current / closing amounts for one household row.
     *
     * - closing: FIFO net outstanding from billing anchor through the billed month
     * - payable: closing plus collections in the report's transaction window (through month_to)
     *
     * @param  Carbon  $billedMonthTo  already-derived billed month (see {@see toBilledMonth()})
     * @return array{payable: string, previous_due: string, current_due: string, closing: string}
     */
    protected function billingStatusAmounts(Household $site, Carbon $billedMonthTo): array
    {
        $revenueInPeriod = (string) ($site->revenue_collected ?? '0.00');
        $closing = $this->fifoOutstandingThroughMonth($site, $billedMonthTo);
        $payable = $this->maxMoney(bcadd($closing, $revenueInPeriod, 2));
        $currentDue = $this->currentMonthDueFromOutstandingMap($site, $billedMonthTo);
        $previousDue = $this->maxMoney(bcsub($closing, $currentDue, 2));

        return [
            'payable' => $payable,
            'previous_due' => $previousDue,
            'current_due' => $currentDue,
            'closing' => $closing,
        ];
    }

    /**
     * Sum of per-month FIFO remaining balances from billing anchor through {@see $monthTo}.
     */
    protected function fifoOutstandingThroughMonth(Household $site, Carbon $monthTo): string
    {
        if ($site->waste_charge === null) {
            return '0.00';
        }

        $rangeStart = $this->dueRangeStart($site, $monthTo);
        if ($rangeStart->gt($monthTo)) {
            return '0.00';
        }

        return $this->sumMarginalDueInRange($site, $rangeStart, $monthTo);
    }

    protected function maxMoney(string $amount): string
    {
        return bccomp($amount, '0', 2) < 0 ? '0.00' : $amount;
    }

    /**
     * Comma-separated Mon, yy labels for months in {@see $rangeStart}..{@see $rangeEnd} (inclusive)
     * where marginal due is greater than zero.
     */
    protected function monthsWithMarginalDueLabelsInRange(Household $site, Carbon $rangeStart, Carbon $rangeEnd): string
    {
        $rangeStart = $rangeStart->copy()->startOfMonth();
        $rangeEnd = $rangeEnd->copy()->startOfMonth();
        if ($rangeStart->gt($rangeEnd)) {
            return '';
        }

        $remainingByMonth = $this->billCollectionPaymentService->outstandingByMonthInRange($site, $rangeStart, $rangeEnd);
        $labels = [];
        foreach ($remainingByMonth as $ym => $remaining) {
            if (bccomp((string) $remaining, '0', 2) > 0) {
                $m = Carbon::createFromFormat('Y-m', $ym)->startOfMonth();
                $labels[] = $m->format('M y');
            }
        }

        return implode(', ', $labels);
    }

    protected function sumMarginalDueInRange(Household $site, Carbon $rangeStart, Carbon $rangeEnd): string
    {
        $rangeStart = $rangeStart->copy()->startOfMonth();
        $rangeEnd = $rangeEnd->copy()->startOfMonth();
        if ($rangeStart->gt($rangeEnd)) {
            return '0.00';
        }

        $sum = '0.00';
        $remainingByMonth = $this->billCollectionPaymentService->outstandingByMonthInRange($site, $rangeStart, $rangeEnd);
        foreach ($remainingByMonth as $remaining) {
            $sum = bcadd($sum, (string) $remaining, 2);
        }

        return $sum;
    }

    protected function currentMonthDueFromOutstandingMap(Household $site, Carbon $monthStart): string
    {
        $monthStart = $monthStart->copy()->startOfMonth();
        $ym = $monthStart->format('Y-m');
        $remainingByMonth = $this->billCollectionPaymentService->outstandingByMonthInRange($site, $monthStart, $monthStart);

        return (string) ($remainingByMonth[$ym] ?? '0.00');
    }

    protected function applyTableFilters(Builder $query, array $data): void
    {
        if (! is_array($data)) {
            return;
        }

        $holdingNumbers = $this->normalizeStringList($data['holding_numbers'] ?? null);
        if ($holdingNumbers !== []) {
            $query->whereIn('building_info.households.holding_number', $holdingNumbers);
        } elseif (! empty($data['holding_number'])) {
            $query->where('building_info.households.holding_number', 'ILIKE', '%'.trim((string) $data['holding_number']).'%');
        }

        $siteIds = $this->normalizeIdList($data['customer_site_ids'] ?? null);
        if ($siteIds !== []) {
            $query->whereIn('building_info.households.id', $siteIds);
        } elseif (! empty($data['household_id'])) {
            $query->where('building_info.households.household_id', 'ILIKE', '%'.trim((string) $data['household_id']).'%');
        }

        $isOwner = $data['is_owner'] ?? null;
        if ($isOwner !== null && $isOwner !== '') {
            $query->where('building_info.households.is_owner', (bool) (int) $isOwner);
        }
        if (! empty($data['van_puller_id'])) {
            $query->where('building_info.households.van_puller_id', (int) $data['van_puller_id']);
        }
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    protected function normalizeStringList($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        if (! is_array($value)) {
            $value = [$value];
        }
        $out = [];
        foreach ($value as $v) {
            $s = is_string($v) ? trim($v) : (is_scalar($v) ? trim((string) $v) : '');
            if ($s !== '') {
                $out[] = $s;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param  mixed  $value
     * @return list<int>
     */
    protected function normalizeIdList($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        if (! is_array($value)) {
            $value = [$value];
        }
        $out = [];
        foreach ($value as $v) {
            $id = (int) $v;
            if ($id > 0) {
                $out[] = $id;
            }
        }

        return array_values(array_unique($out));
    }

    protected function parseMonthStart(?string $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return Carbon::createFromFormat('Y-m', $value)->startOfMonth();
        } catch (\Throwable) {
            return null;
        }
    }

}
