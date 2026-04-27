<?php

namespace App\Services\Swm;

use App\Models\Swm\BillCollectionPayment;
use App\Models\BuildingInfo\Household;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class BillCollectionBillingStatusService
{
    public function __construct(protected BillCollectionPaymentService $billCollectionPaymentService)
    {
    }

    /**
     * Global summary cards (not affected by table filters).
     *
     * - due_for_this_month: sum of marginal due for the current calendar month only (per site).
     * - total_due: sum of cumulative outstanding through the current calendar month (balanceThroughMonth).
     * - total_revenue_collected: sum of payments in current calendar year (payment_for_month year = now).
     *
     * @return array{due_for_this_month: string, total_due: string, total_revenue_collected: string}
     */
    public function summary(): array
    {
        $currentMonthStart = Carbon::now()->startOfMonth();

        $dueThisMonthMarginal = '0.00';
        $totalDueCumulative = '0.00';

        Household::query()
            ->whereNull('deleted_at')
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

        /** Summary card: calendar year-to-date on payment_for_month (switch here if product wants all-time). */
        $sum = BillCollectionPayment::query()
            ->whereNull('deleted_at')
            ->whereYear('payment_for_month', (int) Carbon::now()->year)
            ->sum('amount');
        $revenue = number_format((float) $sum, 2, '.', '');

        return [
            'due_for_this_month' => $dueThisMonthMarginal,
            'total_due' => $totalDueCumulative,
            'total_revenue_collected' => $revenue,
        ];
    }

    /**
     * @param  array<string, mixed>  $data  Request + DataTables params
     */
    public function getStatusTable(array $data)
    {
        $data = is_array($data) ? $data : [];

        $monthFrom = $this->parseMonthStart($data['month_from'] ?? null) ?? Carbon::now()->startOfMonth();
        $monthTo = $this->parseMonthStart($data['month_to'] ?? null) ?? Carbon::now()->startOfMonth();
        if ($monthFrom->gt($monthTo)) {
            [$monthFrom, $monthTo] = [$monthTo->copy(), $monthFrom->copy()];
        }

        $fromDate = $monthFrom->toDateString();
        $toDate = $monthTo->toDateString();

        $revenueSub = BillCollectionPayment::query()
            ->select('household_id')
            ->selectRaw('SUM(amount) as revenue_collected')
            ->whereNull('deleted_at')
            ->whereDate('payment_for_month', '>=', $fromDate)
            ->whereDate('payment_for_month', '<=', $toDate)
            ->groupBy('household_id');

        $query = Household::query()
            ->select('building_info.households.*')
            ->whereNull('building_info.households.deleted_at')
            ->leftJoinSub($revenueSub->toBase(), 'rev', 'building_info.households.id', '=', 'rev.household_id')
            ->addSelect(DB::raw('COALESCE(rev.revenue_collected, 0) as revenue_collected'));

        $currentMonthStart = Carbon::now()->startOfMonth();

        return DataTables::of($query)
            ->filter(function (Builder $q) use ($data) {
                $this->applyTableFilters($q, $data);
            }, false)
            ->orderColumn('holding_number', 'building_info.households.holding_number $1')
            ->orderColumn('household_id', 'building_info.households.household_id $1')
            ->orderColumn('revenue_collected', 'revenue_collected $1')
            ->addColumn('due_current_month', function (Household $site) use ($currentMonthStart) {
                return $this->formatMoney($this->billCollectionPaymentService->marginalDueForMonth($site, $currentMonthStart));
            })
            ->addColumn('due_months_of', function (Household $site) use ($monthFrom, $monthTo) {
                return $this->monthsWithMarginalDueLabelsInRange($site, $monthFrom, $monthTo);
            })
            ->addColumn('total_due_amount', function (Household $site) use ($monthTo) {
                return $this->formatMoney($this->dueThroughMonth($site, $monthTo));
            })
            ->editColumn('revenue_collected', function (Household $site) {
                return number_format((float) ($site->revenue_collected ?? 0), 2, '.', '');
            })
            ->rawColumns([])
            ->make(true);
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
     * Comma-separated Y-m for months in {@see $rangeStart}..{@see $rangeEnd} (inclusive) where marginal due is greater than zero.
     */
    protected function monthsWithMarginalDueLabelsInRange(Household $site, Carbon $rangeStart, Carbon $rangeEnd): string
    {
        $rangeStart = $rangeStart->copy()->startOfMonth();
        $rangeEnd = $rangeEnd->copy()->startOfMonth();
        if ($rangeStart->gt($rangeEnd)) {
            return '';
        }

        $labels = [];
        $m = $rangeStart->copy();
        $guard = 0;
        while ($m->lte($rangeEnd) && $guard < 240) {
            $guard++;
            if (bccomp($this->billCollectionPaymentService->marginalDueForMonth($site, $m), '0', 2) > 0) {
                $labels[] = $m->format('Y-m');
            }
            $m->addMonth();
        }

        return implode(', ', $labels);
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

    protected function formatMoney(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format((float) $value, 2, '.', '');
    }
}
