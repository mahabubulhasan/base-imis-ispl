<?php

namespace App\Services\Swm\Dashboard\Modules;

use App\Models\Swm\Organization;
use App\Models\Swm\Worker;
use App\Services\Swm\Dashboard\Concerns\BuildsCountChartAxisLabels;
use App\Services\Swm\Dashboard\Concerns\BuildsCumulativeDateQueries;
use App\Services\Swm\Dashboard\Contracts\SwmDashboardModuleInterface;
use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\SwmDashboardAxisKeys;
use App\Services\Swm\Dashboard\SwmDashboardFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceProvidersDashboardModule implements SwmDashboardModuleInterface
{
    use BuildsCountChartAxisLabels;
    use BuildsCumulativeDateQueries;

    private const TOP_ORGANIZATIONS_FOR_STACKED_CHART = 8;

    private const AGE_BUCKETS = [
        '0-17' => [0, 17],
        '18-24' => [18, 24],
        '25-34' => [25, 34],
        '35-44' => [35, 44],
        '45-54' => [45, 54],
        '55-64' => [55, 64],
        '65+' => [65, null],
    ];

    private const EDUCATION_LEVEL_ORDER = [
        'primary',
        'secondary',
        'below_ssc',
        'ssc',
        'hsc',
        'bachelor',
        'master',
        'others',
    ];

    public function __construct(
        protected SwmDashboardFormatter $formatter,
    ) {
    }

    public function key(): string
    {
        return 'service_providers';
    }

    public function label(): string
    {
        return __('Service Providers');
    }

    public function permission(): ?string
    {
        return null;
    }

    public function build(DashboardReportingPeriod $period): array
    {
        $orgCount = (int) $this->organizationQuery($period)->count();
        $workerCount = (int) $this->workerQuery($period)->count();

        return [
            'submodules' => [
                [
                    'key' => 'overview',
                    'title' => __('Service Providers'),
                    'blocks' => [
                        [
                            'type' => 'tiles',
                            'items' => [
                                [
                                    'label' => __('Total Organizations'),
                                    'value' => $this->formatter->integer($orgCount),
                                    'icon' => 'fa-building',
                                ],
                                [
                                    'label' => __('Total Workers'),
                                    'value' => $this->formatter->integer($workerCount),
                                    'icon' => 'fa-hard-hat',
                                ],
                            ],
                        ],
                        [
                            'type' => 'charts',
                            'subsection' => __('Visualizations'),
                            'items' => [
                                $this->organizationCategoryChart($period),
                                $this->workersByTypeChart($period),
                                $this->workersByWardAndOrganizationChart($period),
                                $this->workerGenderChart($period),
                                $this->workerAgeChart($period),
                                $this->workerEmploymentTypeChart($period),
                                $this->workerEducationLevelChart($period),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function organizationQuery(DashboardReportingPeriod $period): Builder
    {
        $table = (new Organization)->getTable();

        $query = Organization::query()
            ->where($table.'.status', true)
            ->whereNull($table.'.deleted_at');

        $this->whereThroughPeriodEnd($query, $table.'.created_at', $period);
        $this->applyOrganizationScope($query, $table.'.id');

        return $query;
    }

    protected function workerQuery(DashboardReportingPeriod $period): Builder
    {
        $query = Worker::query()
            ->where('swm.workers.status', 'active')
            ->whereNull('swm.workers.deleted_at')
            ->whereHas('organization', function (Builder $orgQuery) use ($period) {
                $orgQuery->operational()->whereNull('deleted_at');
                $this->whereThroughPeriodEnd($orgQuery, 'created_at', $period);
                $this->applyOrganizationScope($orgQuery);
            });

        $this->whereThroughPeriodEnd($query, 'swm.workers.created_at', $period);
        $this->applyOrganizationScope($query, 'swm.workers.organization_id');

        return $query;
    }

    protected function applyOrganizationScope(Builder $query, string $column = 'id'): void
    {
        $orgId = Auth::user()?->swm_organization_id;
        if ($orgId) {
            $query->where($column, $orgId);
        }
    }

    protected function organizationCategoryChart(DashboardReportingPeriod $period): array
    {
        $rows = $this->organizationQuery($period)
            ->join('swm.organization_types', 'swm.organizations.organization_type_id', '=', 'swm.organization_types.id')
            ->whereNull('swm.organization_types.deleted_at')
            ->selectRaw('swm.organization_types.name as organization_type_name, COUNT(*) as total')
            ->groupBy('swm.organization_types.name')
            ->orderByDesc('total')
            ->get();

        $byType = [];
        foreach ($rows as $row) {
            $key = $row->organization_type_name ?: SwmDashboardAxisKeys::CATEGORY_AXIS_NA_KEY;
            $byType[$key] = ($byType[$key] ?? 0) + (int) $row->total;
        }
        $aligned = $this->alignCountsToCategoryAxis($byType, $this->masterOrganizationTypeCategoryKeys());

        return [
            'id' => 'swmChartOrgCategory',
            'type' => 'doughnut',
            'title' => __('Organizations Type'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['data' => array_map(static fn ($v) => (int) $v, $aligned['values'])],
            ],
            'options' => [],
        ];
    }

    protected function workersByTypeChart(DashboardReportingPeriod $period): array
    {
        $rows = $this->workerQuery($period)
            ->join('swm.work_types as wt', 'swm.workers.work_type_id', '=', 'wt.id')
            ->whereNull('wt.deleted_at')
            ->selectRaw('wt.name as label, COUNT(*) as total')
            ->groupBy('wt.id', 'wt.name')
            ->get();

        $byType = $rows->pluck('total', 'label')->all();
        $aligned = $this->alignCountsToCategoryAxis($byType, $this->masterWorkTypeCategoryKeys());

        return [
            'id' => 'swmChartWorkersByType',
            'type' => 'bar',
            'title' => __('Workers by Worker Type'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['data' => array_map(static fn ($v) => (int) $v, $aligned['values'])],
            ],
            'options' => $this->staticCategoryChartOptions(
                __('Worker Type'),
                $this->countChartAxisY(__('Workers')),
                ['integerYTicks' => true],
            ),
        ];
    }

    protected function workersByWardAndOrganizationChart(DashboardReportingPeriod $period): array
    {
        $orgScopeSql = '';
        $bindings = [$period->periodEnd, $period->periodEnd];

        $orgId = Auth::user()?->swm_organization_id;
        if ($orgId) {
            $orgScopeSql = ' AND w.organization_id = ?';
            $bindings[] = $orgId;
        }

        $rows = collect(DB::select(
            "
            SELECT
                ward_rows.ward,
                w.organization_id,
                o.name AS organization_name,
                COUNT(*)::int AS total
            FROM swm.workers w
            INNER JOIN swm.organizations o ON o.id = w.organization_id
            CROSS JOIN LATERAL (
                SELECT elem AS ward
                FROM jsonb_array_elements_text(
                    CASE
                        WHEN w.service_wards IS NOT NULL
                            AND jsonb_typeof(w.service_wards::jsonb) = 'array'
                            AND jsonb_array_length(w.service_wards::jsonb) > 0
                        THEN w.service_wards::jsonb
                        WHEN w.service_area IS NOT NULL AND w.service_area ~ '^[0-9]+$'
                        THEN jsonb_build_array(w.service_area)
                        ELSE '[]'::jsonb
                    END
                ) AS elem
            ) AS ward_rows
            WHERE w.deleted_at IS NULL
                AND w.status = 'active'
                AND w.created_at <= ?
                AND o.deleted_at IS NULL
                AND o.status = true
                AND o.created_at <= ?
                {$orgScopeSql}
            GROUP BY ward_rows.ward, w.organization_id, o.name
            ORDER BY ward_rows.ward::int, o.name
            ",
            $bindings,
        ));

        if ($rows->isEmpty()) {
            return [
                'id' => 'swmChartWorkersWardOrg',
                'type' => 'stackedBar',
                'title' => __('Workers by Ward and Organization'),
                'labels' => [],
                'datasets' => [],
                'options' => $this->staticCategoryChartOptions(
                    __('Ward'),
                    $this->countChartAxisY(__('Workers')),
                    ['stacked' => true, 'integerYTicks' => true],
                ),
                'height' => 400,
            ];
        }

        $orgTotals = $rows->groupBy('organization_id')->map(
            fn (Collection $group) => (int) $group->sum('total'),
        )->sortDesc();

        $topOrgIds = $orgTotals->keys()->take(self::TOP_ORGANIZATIONS_FOR_STACKED_CHART)->all();
        $topOrgIdSet = array_flip($topOrgIds);

        $countsByWardOrg = [];
        foreach ($rows as $row) {
            $orgKey = isset($topOrgIdSet[$row->organization_id])
                ? (string) $row->organization_id
                : 'others';
            $countsByWardOrg[$row->ward][$orgKey] = ($countsByWardOrg[$row->ward][$orgKey] ?? 0) + (int) $row->total;
        }

        $orgNames = $rows->unique('organization_id')->pluck('organization_name', 'organization_id');
        $datasetKeys = array_map('strval', $topOrgIds);
        if ($orgTotals->count() > count($topOrgIds)) {
            $datasetKeys[] = 'others';
        }

        $aligned = $this->alignStackedSeriesToWardAxis(
            $countsByWardOrg,
            $datasetKeys,
            fn (string $key) => $key === 'others'
                ? __('Others')
                : (string) ($orgNames[(int) $key] ?? $key),
        );

        return [
            'id' => 'swmChartWorkersWardOrg',
            'type' => 'stackedBar',
            'title' => __('Workers by Ward and Organization'),
            'labels' => $aligned['labels'],
            'datasets' => array_map(
                static fn (array $dataset) => [
                    'label' => $dataset['label'],
                    'data' => array_map(static fn ($v) => (int) $v, $dataset['data']),
                ],
                $aligned['datasets'],
            ),
            'options' => $this->staticCategoryChartOptions(
                __('Ward'),
                $this->countChartAxisY(__('Workers')),
                ['stacked' => true, 'integerYTicks' => true],
            ),
            'height' => 400,
        ];
    }

    protected function workerGenderChart(DashboardReportingPeriod $period): array
    {
        $labels = [
            'male' => __('Male'),
            'female' => __('Female'),
            'others' => __('Others'),
        ];

        $counts = $this->workerQuery($period)
            ->selectRaw('gender, COUNT(*) as total')
            ->groupBy('gender')
            ->pluck('total', 'gender');

        $byGender = [];
        foreach ($labels as $key => $label) {
            $byGender[$key] = (int) $counts->get($key, 0);
        }
        $byGender[SwmDashboardAxisKeys::CATEGORY_AXIS_NA_KEY] = $this->sumUnlistedBucketCounts($counts, array_keys($labels));
        $masterKeys = array_merge(array_keys($labels), [SwmDashboardAxisKeys::CATEGORY_AXIS_NA_KEY]);
        $aligned = $this->alignCountsToCategoryAxis(
            $byGender,
            $masterKeys,
            fn (string $key) => match ($key) {
                'male' => __('Male'),
                'female' => __('Female'),
                'others' => __('Others'),
                default => __('N/A'),
            },
        );

        return [
            'id' => 'swmChartWorkerGender',
            'type' => 'doughnut',
            'title' => __('Worker Gender Distribution'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['data' => array_map(static fn ($v) => (int) $v, $aligned['values'])],
            ],
            'options' => [],
        ];
    }

    protected function workerAgeChart(DashboardReportingPeriod $period): array
    {
        $caseParts = [];
        foreach (self::AGE_BUCKETS as $label => [$min, $max]) {
            if ($max === null) {
                $caseParts[] = "WHEN age >= {$min} THEN '{$label}'";
            } else {
                $caseParts[] = "WHEN age BETWEEN {$min} AND {$max} THEN '{$label}'";
            }
        }

        $caseSql = 'CASE '.implode(' ', $caseParts)." ELSE 'unknown' END";

        $counts = $this->workerQuery($period)
            ->selectRaw("{$caseSql} as bucket, COUNT(*) as total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $bucketLabels = array_keys(self::AGE_BUCKETS);
        $bucketLabels[] = 'unknown';

        $labels = [];
        $values = [];
        foreach ($bucketLabels as $bucket) {
            $labels[] = $bucket === 'unknown' ? __('Unknown') : $bucket;
            $values[] = (int) ($counts[$bucket] ?? 0);
        }

        return [
            'id' => 'swmChartWorkerAge',
            'type' => 'bar',
            'title' => __('Worker Age Distribution'),
            'labels' => $labels,
            'datasets' => [
                ['data' => $values],
            ],
            'options' => $this->staticCategoryChartOptions(
                __('Age'),
                $this->countChartAxisY(__('Workers')),
                ['integerYTicks' => true],
            ),
        ];
    }

    protected function workerEmploymentTypeChart(DashboardReportingPeriod $period): array
    {
        $labels = [
            'permanent' => __('Permanent'),
            'daily' => __('Daily'),
            'contract' => __('Contract'),
        ];

        $counts = $this->workerQuery($period)
            ->selectRaw('employment_type, COUNT(*) as total')
            ->groupBy('employment_type')
            ->pluck('total', 'employment_type');

        $byEmployment = [];
        foreach ($labels as $key => $label) {
            $byEmployment[$key] = (int) $counts->get($key, 0);
        }
        $byEmployment[SwmDashboardAxisKeys::CATEGORY_AXIS_NA_KEY] = $this->sumUnlistedBucketCounts($counts, array_keys($labels));
        $aligned = $this->alignCountsToCategoryAxis(
            $byEmployment,
            array_merge(array_keys($labels), [SwmDashboardAxisKeys::CATEGORY_AXIS_NA_KEY]),
            fn (string $key) => $labels[$key] ?? __('N/A'),
        );

        return [
            'id' => 'swmChartWorkerEmploymentType',
            'type' => 'doughnut',
            'title' => __('Worker by Employment Type'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['data' => array_map(static fn ($v) => (int) $v, $aligned['values'])],
            ],
            'options' => [],
        ];
    }

    protected function workerEducationLevelChart(DashboardReportingPeriod $period): array
    {
        $labels = $this->educationLevelLabels();

        $counts = $this->workerQuery($period)
            ->selectRaw('education_level, COUNT(*) as total')
            ->groupBy('education_level')
            ->pluck('total', 'education_level');

        $byEducation = [];
        foreach (self::EDUCATION_LEVEL_ORDER as $key) {
            $byEducation[$key] = (int) ($counts[$key] ?? 0);
        }
        $byEducation[SwmDashboardAxisKeys::CATEGORY_AXIS_NA_KEY] = (int) $counts->filter(
            fn ($_, $key) => $key === null || $key === '' || ! in_array($key, self::EDUCATION_LEVEL_ORDER, true),
        )->sum();
        $aligned = $this->alignCountsToCategoryAxis(
            $byEducation,
            array_merge(self::EDUCATION_LEVEL_ORDER, [SwmDashboardAxisKeys::CATEGORY_AXIS_NA_KEY]),
            fn (string $key) => $labels[$key] ?? __('N/A'),
        );

        return [
            'id' => 'swmChartWorkerEducation',
            'type' => 'bar',
            'title' => __('Worker Education-Level Distribution'),
            'labels' => $aligned['labels'],
            'datasets' => [
                ['data' => array_map(static fn ($v) => (int) $v, $aligned['values'])],
            ],
            'options' => $this->staticCategoryChartOptions(
                __('Education Level'),
                $this->countChartAxisY(__('Workers')),
                ['integerYTicks' => true],
            ),
        ];
    }

    /**
     * Sum bucket totals for keys not in $knownKeys (null, empty string, or other).
     * Avoids double-counting when PHP maps null and '' to the same array key.
     */
    protected function sumUnlistedBucketCounts(Collection $counts, array $knownKeys): int
    {
        $unknown = 0;
        foreach ($counts as $value => $total) {
            if (! in_array($value, $knownKeys, true)) {
                $unknown += (int) $total;
            }
        }

        return $unknown;
    }

    /** @return array<string, string> */
    protected function educationLevelLabels(): array
    {
        return [
            'primary' => __('Primary'),
            'secondary' => __('Secondary (Below SSC)'),
            'below_ssc' => __('Below SSC'),
            'ssc' => __('SSC'),
            'hsc' => __('HSC'),
            'bachelor' => __('Bachelor'),
            'master' => __('Master'),
            'others' => __('Others (specify)'),
        ];
    }
}
