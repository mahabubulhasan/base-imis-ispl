<?php

namespace App\Services\Swm\Dashboard\Modules;

use App\Models\Swm\Organization;
use App\Models\Swm\Worker;
use App\Services\Swm\Dashboard\Concerns\BuildsCumulativeDateQueries;
use App\Services\Swm\Dashboard\Contracts\SwmDashboardModuleInterface;
use App\Services\Swm\Dashboard\DashboardReportingPeriod;
use App\Services\Swm\Dashboard\SwmDashboardFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceProvidersDashboardModule implements SwmDashboardModuleInterface
{
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
                    'title' => __('Overview'),
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
        $query = Organization::query()
            ->operational()
            ->whereNull('deleted_at');

        $this->whereThroughPeriodEnd($query, 'created_at', $period);
        $this->applyOrganizationScope($query);

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
            ->selectRaw('organization_category, COUNT(*) as total')
            ->groupBy('organization_category')
            ->orderByDesc('total')
            ->get();

        $categoryLabels = Organization::categoryOptions();
        $labels = [];
        $values = [];

        foreach ($rows as $row) {
            $key = $row->organization_category;
            $labels[] = $key ? ($categoryLabels[$key] ?? $key) : __('N/A');
            $values[] = (int) $row->total;
        }

        return [
            'id' => 'swmChartOrgCategory',
            'type' => 'doughnut',
            'title' => __('Organizations Category'),
            'labels' => $labels,
            'datasets' => [
                ['data' => $values],
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
            ->orderByDesc('total')
            ->get();

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = $row->label;
            $values[] = (int) $row->total;
        }

        return [
            'id' => 'swmChartWorkersByType',
            'type' => 'bar',
            'title' => __('Workers by Worker Type'),
            'labels' => $labels,
            'datasets' => [
                ['data' => $values],
            ],
            'options' => ['unitX' => __('Worker Type')],
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
                'options' => [
                    'stacked' => true,
                    'unitX' => __('Ward'),
                ],
                'height' => 400,
            ];
        }

        $orgTotals = $rows->groupBy('organization_id')->map(
            fn (Collection $group) => (int) $group->sum('total'),
        )->sortDesc();

        $topOrgIds = $orgTotals->keys()->take(self::TOP_ORGANIZATIONS_FOR_STACKED_CHART)->all();
        $topOrgIdSet = array_flip($topOrgIds);

        $wardLabels = $rows->pluck('ward')
            ->unique()
            ->sortBy(fn (string $ward) => (int) $ward)
            ->values()
            ->all();

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

        $datasets = [];
        foreach ($datasetKeys as $key) {
            $label = $key === 'others'
                ? __('Others')
                : (string) ($orgNames[(int) $key] ?? $key);

            $data = [];
            foreach ($wardLabels as $ward) {
                $data[] = (int) ($countsByWardOrg[$ward][$key] ?? 0);
            }

            $datasets[] = [
                'label' => $label,
                'data' => $data,
            ];
        }

        return [
            'id' => 'swmChartWorkersWardOrg',
            'type' => 'stackedBar',
            'title' => __('Workers by Ward and Organization'),
            'labels' => $wardLabels,
            'datasets' => $datasets,
            'options' => [
                'stacked' => true,
                'unitX' => __('Ward'),
            ],
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

        $chartLabels = [];
        $values = [];
        foreach ($labels as $key => $label) {
            $count = (int) $counts->get($key, 0);
            if ($count > 0) {
                $chartLabels[] = $label;
                $values[] = $count;
            }
        }

        $unknown = $this->sumUnlistedBucketCounts($counts, array_keys($labels));
        if ($unknown > 0) {
            $chartLabels[] = __('N/A');
            $values[] = $unknown;
        }

        return [
            'id' => 'swmChartWorkerGender',
            'type' => 'doughnut',
            'title' => __('Worker Gender Distribution'),
            'labels' => $chartLabels,
            'datasets' => [
                ['data' => $values],
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
            'options' => ['unitX' => __('Age')],
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

        $chartLabels = [];
        $values = [];
        foreach ($labels as $key => $label) {
            $count = (int) $counts->get($key, 0);
            if ($count > 0) {
                $chartLabels[] = $label;
                $values[] = $count;
            }
        }

        $unknown = $this->sumUnlistedBucketCounts($counts, array_keys($labels));
        if ($unknown > 0) {
            $chartLabels[] = __('N/A');
            $values[] = $unknown;
        }

        return [
            'id' => 'swmChartWorkerEmploymentType',
            'type' => 'doughnut',
            'title' => __('Worker by Employment Type'),
            'labels' => $chartLabels,
            'datasets' => [
                ['data' => $values],
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

        $chartLabels = [];
        $values = [];
        foreach (self::EDUCATION_LEVEL_ORDER as $key) {
            $count = (int) ($counts[$key] ?? 0);
            if ($count === 0) {
                continue;
            }
            $chartLabels[] = $labels[$key];
            $values[] = $count;
        }

        $unknown = (int) $counts->filter(fn ($_, $key) => $key === null || $key === '' || ! in_array($key, self::EDUCATION_LEVEL_ORDER, true))->sum();
        if ($unknown > 0) {
            $chartLabels[] = __('N/A');
            $values[] = $unknown;
        }

        return [
            'id' => 'swmChartWorkerEducation',
            'type' => 'bar',
            'title' => __('Worker Education-Level Distribution'),
            'labels' => $chartLabels,
            'datasets' => [
                ['data' => $values],
            ],
            'options' => ['unitX' => __('Education Level')],
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
