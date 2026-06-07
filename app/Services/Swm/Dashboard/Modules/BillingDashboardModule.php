<?php

namespace App\Services\Swm\Dashboard\Modules;

use App\Services\Swm\Dashboard\Concerns\BuildsCountChartAxisLabels;
use App\Services\Swm\Dashboard\Billing\BillingDashboardMetrics;
use App\Services\Swm\Dashboard\Contracts\SwmDashboardModuleInterface;
use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\SwmDashboardFormatter;
use Carbon\Carbon;

class BillingDashboardModule implements SwmDashboardModuleInterface
{
    use BuildsCountChartAxisLabels;

    public function __construct(
        protected SwmDashboardFormatter $formatter,
        protected BillingDashboardMetrics $metrics,
    ) {
    }

    public function key(): string
    {
        return 'billing';
    }

    public function label(): string
    {
        return __('Billing');
    }

    public function permission(): ?string
    {
        return null;
    }

    public function build(DashboardReportingPeriod $period): array
    {
        return [
            'submodules' => [
                $this->billingSubmodule($period),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function billingSubmodule(DashboardReportingPeriod $period): array
    {
        $agg = $this->metrics->aggregate($period);

        return [
            'key' => 'billing',
            'title' => __('Billing'),
            'blocks' => [
                [
                    'type' => 'tiles',
                    'items' => $this->tileItems($agg, $period),
                ],
                [
                    'type' => 'charts',
                    'subsection' => __('Visualizations'),
                    'items' => [
                        $this->revenueTrendChart($period),
                        $this->billCollectionByWardChart($period),
                        $this->paymentMethodChart($period),
                        $this->averageFeeByWardChart($period),
                        $this->arrearsByWardChart($agg),
                    ],
                ],
                // [
                //     'type' => 'table',
                //     'title' => __('Households with 3+ Months of Dues'),
                //     'columns' => $this->arrearsTableColumns(),
                //     'rows' => $this->formatTableRows($agg['table_rows']),
                // ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $agg
     * @return list<array<string, string>>
     */
    protected function tileItems(array $agg, DashboardReportingPeriod $period): array
    {
        $billed = (int) ($agg['billed_household_count'] ?? 0);
        $defaultCount = (int) ($agg['default_count'] ?? 0);
        $defaultRate = $billed > 0
            ? ((float) $defaultCount / (float) $billed) * 100
            : 0.0;

        return [
            [
                'label' => __('Total Billed Amount (Taka)'),
                'value' => $this->formatter->integer($agg['total_billed_amount']),
                'icon' => 'fa-file-invoice',
            ],
            // [
            //     'label' => __('Due for This Month (Taka)'),
            //     'value' => $this->formatter->integer($agg['due_for_this_month']),
            //     'icon' => 'fa-calendar-alt',
            // ],
            [
                'label' => __('Total Bill Collected (through :month) (Taka)', [
                    'month' => $period->toMonth->format('M Y'),
                ]),
                'value' => $this->formatter->integer($agg['total_revenue_collected']),
                'icon' => 'fa-coins',
            ],
            [
                'label' => __('Total Due (Taka)'),
                'value' => $this->formatter->integer($agg['total_due']),
                'icon' => 'fa-file-invoice-dollar',
            ],
            [
                'label' => __('Bill Collection Efficiency (%)'),
                'value' => $this->formatter->percent(
                $this->percentRatio($agg['total_paid'], $agg['total_payable']),
                ),
                'icon' => 'fa-percent',
            ],
            // [
            //     'label' => __('Previous Due Recovery Rate (%)'),
            //     'value' => $this->formatter->percent(
            //         $this->percentRatio($agg['total_previous_due_paid'], $agg['total_previous_due']),
            //     ),
            //     'icon' => 'fa-hand-holding-usd',
            // ],
            [
                'label' => __('Number of Households Having Some Due'),
                'value' => $defaultCount,
                'icon' => 'fa-exclamation-triangle',
            ],
        ];
    }

    protected function revenueTrendChart(DashboardReportingPeriod $period): array
    {
        $endMonth = $period->toMonth->copy()->startOfMonth();
        $revenueByMonth = $this->metrics->revenueByMonth($endMonth, 12);

        $labels = [];
        $values = [];
        foreach ($revenueByMonth as $monthKey => $revenue) {
            $labels[] = Carbon::parse($monthKey)->format('M Y');
            $values[] = round($revenue, 2);
        }

        return [
            'id' => 'swmChartBillingRevenueTrend',
            'type' => 'line',
            'title' => __('Bill Collection Trend'),
            'labels' => $labels,
            'datasets' => [
                ['label' => __('Revenue (Taka)'), 'data' => $values],
            ],
            'options' => [
                'unitX' => __('Month'),
                'unitY' => __('Taka'),
                'decimalValues' => true,
            ],
        ];
    }

    protected function billCollectionByWardChart(DashboardReportingPeriod $period): array
    {
        $byWard = $this->metrics->billCollectionByWard($period->toMonth);
        $aligned = $this->alignCountsToWardAxis($byWard, appendUnknown: true);

        return [
            'id' => 'swmChartBillingCollectionByWard',
            'type' => 'bar',
            'title' => __('Bill Collection by Ward (through :month)', [
                'month' => $period->toMonth->format('M Y'),
            ]),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['label' => __('Bill Collected (Taka)'), 'data' => $aligned['values']],
            ],
            'options' => $this->staticCategoryChartOptions(__('Ward'), __('Taka'), [
                'decimalValues' => true,
            ]),
        ];
    }

    protected function paymentMethodChart(DashboardReportingPeriod $period): array
    {
        $collected = $this->metrics->paymentMethodCollectedThroughMonth($period->toMonth);
        $methods = config('bill_collection.payment_methods', []);
        $byMethod = [];
        foreach ($collected as $key => $amount) {
            $byMethod[(string) $key] = (float) $amount;
        }
        $aligned = $this->alignCountsToCategoryAxis(
            $byMethod,
            array_keys($methods),
            fn (string $key) => isset($methods[$key]) ? __($methods[$key]) : $key,
        );

        return [
            'id' => 'swmChartBillingPaymentMethod',
            'type' => 'doughnut',
            'title' => __('Payment-Method Distribution'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['label' => __('Bill Collected (Taka)'), 'data' => $aligned['values']],
            ],
            'options' => [
                'unitX' => __('Payment Method'),
                'unit' => __('Taka'),
                'valueDescriptor' => __('Bill Collected'),
                'decimalValues' => true,
            ],
        ];
    }

    protected function averageFeeByWardChart(DashboardReportingPeriod $period): array
    {
        $byWard = $this->metrics->averageFeeByWard();
        $aligned = $this->alignCountsToWardAxis($byWard);

        return [
            'id' => 'swmChartBillingAvgFeeByWard',
            'type' => 'bar',
            'title' => __('Waste Collection Fee by Ward'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['label' => __('Average Fee (Taka)'), 'data' => $aligned['values']],
            ],
            'options' => $this->staticCategoryChartOptions(__('Ward'), __('Taka'), [
                'decimalValues' => true,
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $agg
     */
    protected function arrearsByWardChart(array $agg): array
    {
        $byWard = [];
        foreach ($agg['ward_arrears'] ?? [] as $ward => $amount) {
            $byWard[$ward] = round((float) $amount, 2);
        }
        $aligned = $this->alignCountsToWardAxis($byWard);

        return [
            'id' => 'swmChartBillingArrearsByWard',
            'type' => 'bar',
            'title' => __('Due Amount by Ward'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['label' => __('Due Amount (Taka)'), 'data' => $aligned['values']],
            ],
            'options' => $this->staticCategoryChartOptions(__('Ward'), __('Taka'), [
                'decimalValues' => true,
            ]),
        ];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    protected function arrearsTableColumns(): array
    {
        return [
            ['key' => 'holding_number', 'label' => __('Holding Number')],
            ['key' => 'household_owner_name', 'label' => __('Household Owner Name')],
            ['key' => 'ward', 'label' => __('Ward')],
            ['key' => 'fixed_service_fee', 'label' => __('Fixed Service Fee')],
            ['key' => 'due_months', 'label' => __('Due Months')],
            ['key' => 'closing_due', 'label' => __('Closing Due')],
            ['key' => 'contact_number', 'label' => __('Contact Number')],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, string>>
     */
    protected function formatTableRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'holding_number' => (string) ($row['holding_number'] ?? ''),
                'household_owner_name' => (string) ($row['household_owner_name'] ?? ''),
                'ward' => (string) ($row['ward'] ?? ''),
                'fixed_service_fee' => $this->formatter->integer($row['fixed_service_fee'] ?? 0),
                'due_months' => (string) ($row['due_months'] ?? 0),
                'closing_due' => $this->formatter->integer($row['closing_due'] ?? 0),
                'contact_number' => (string) ($row['contact_number'] ?? ''),
            ];
        }

        return $out;
    }

    protected function percentRatio(string $numerator, string $denominator): float
    {
        if (bccomp($denominator, '0', 2) <= 0) {
            return 0.0;
        }

        return ((float) $numerator / (float) $denominator) * 100;
    }
}
