<?php

namespace App\Services\Swm\Dashboard\Concerns;

use App\Models\Swm\Landfill;
use App\Models\Swm\Organization;
use App\Models\Swm\OrganizationType;
use App\Models\Swm\Sts;
use App\Models\Swm\VehicleType;
use App\Models\Swm\WasteBinType;
use App\Models\Swm\WasteType;
use App\Models\Swm\WorkType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait AlignsChartsToStaticCategoryAxis
{
    public const CATEGORY_AXIS_NA_KEY = 'N/A';

    /** @var array<string, list<string>> */
    protected array $cachedCategoryMasterKeys = [];

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    protected function staticCategoryChartOptions(string $unitX, string $unitY, array $extra = []): array
    {
        return array_merge([
            'unitX' => $unitX,
            'unitY' => $unitY,
            'staticCategoryAxis' => true,
        ], $extra);
    }

    /**
     * @param  array<string|int, int|float|string>  $countsByKey
     * @param  list<string>  $masterKeys
     * @return array{labels: list<string>, values: list<int|float>}
     */
    protected function alignCountsToCategoryAxis(
        array $countsByKey,
        array $masterKeys,
        ?callable $displayLabel = null,
    ): array {
        $normalized = $this->normalizeCountsByCategoryKey($countsByKey);
        $axisKeys = $this->resolveCategoryAxisKeys($normalized, $masterKeys);
        $displayLabel ??= fn (string $key) => $this->categoryAxisDisplayLabel($key);

        $labels = [];
        $values = [];
        foreach ($axisKeys as $key) {
            $labels[] = $displayLabel($key);
            $values[] = $normalized[$key] ?? 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @param  array<string|int, array<string|int, int|float|string>>  $seriesByCategory
     * @param  list<string>  $seriesKeys
     * @param  list<string>  $masterCategoryKeys
     * @return array{labels: list<string>, datasets: list<array{label: string, data: list<int|float>}>}
     */
    protected function alignStackedSeriesToCategoryAxis(
        array $seriesByCategory,
        array $seriesKeys,
        callable $seriesLabel,
        array $masterCategoryKeys,
        ?callable $categoryDisplayLabel = null,
    ): array {
        $normalizedByCategory = [];
        foreach ($seriesByCategory as $category => $seriesCounts) {
            $categoryKey = $this->normalizeCategoryKey($category);
            if ($categoryKey === '') {
                continue;
            }
            foreach ($seriesCounts as $seriesKey => $value) {
                $normalizedByCategory[$categoryKey][(string) $seriesKey] = is_numeric($value)
                    ? (float) $value
                    : 0.0;
            }
        }

        $flatCounts = [];
        foreach ($normalizedByCategory as $categoryKey => $seriesCounts) {
            $flatCounts[$categoryKey] = array_sum($seriesCounts);
        }

        $axisKeys = $this->resolveCategoryAxisKeys($flatCounts, $masterCategoryKeys);
        $categoryDisplayLabel ??= fn (string $key) => $this->categoryAxisDisplayLabel($key);
        $labels = array_map($categoryDisplayLabel, $axisKeys);

        $datasets = [];
        foreach ($seriesKeys as $seriesKey) {
            $data = [];
            foreach ($axisKeys as $categoryKey) {
                $data[] = $normalizedByCategory[$categoryKey][$seriesKey] ?? 0;
            }
            $datasets[] = [
                'label' => $seriesLabel((string) $seriesKey),
                'data' => $data,
            ];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    /**
     * @param  array<string, int|float>  $countsByKey
     * @param  list<string>  $masterKeys
     * @return list<string>
     */
    protected function resolveCategoryAxisKeys(array $countsByKey, array $masterKeys): array
    {
        if ($masterKeys !== []) {
            return $masterKeys;
        }

        $keys = array_keys($countsByKey);
        usort($keys, static fn (string $a, string $b) => strnatcasecmp($a, $b));

        return $keys;
    }

    /**
     * @param  array<string|int, int|float|string>  $countsByKey
     * @return array<string, float>
     */
    protected function normalizeCountsByCategoryKey(array $countsByKey): array
    {
        $normalized = [];
        foreach ($countsByKey as $key => $value) {
            $normalizedKey = $this->normalizeCategoryKey($key);
            if ($normalizedKey === '') {
                continue;
            }
            $normalized[$normalizedKey] = is_numeric($value) ? (float) $value : 0.0;
        }

        return $normalized;
    }

    protected function normalizeCategoryKey(mixed $key): string
    {
        if ($key === null) {
            return '';
        }

        $normalized = trim((string) $key);
        if ($normalized === '') {
            return '';
        }

        if (strcasecmp($normalized, 'n/a') === 0) {
            return self::CATEGORY_AXIS_NA_KEY;
        }

        return $normalized;
    }

    protected function categoryAxisDisplayLabel(string $key): string
    {
        if ($key === self::CATEGORY_AXIS_NA_KEY) {
            return __('N/A');
        }

        return $key;
    }

    /**
     * @return list<string>
     */
    protected function masterStsCategoryKeys(bool $includeNa = true): array
    {
        return $this->rememberCategoryMasterKeys('sts', function () use ($includeNa): array {
            $keys = Sts::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->pluck('name')
                ->map(fn ($name) => $this->normalizeCategoryKey($name))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($includeNa) {
                $keys[] = self::CATEGORY_AXIS_NA_KEY;
            }

            return $keys;
        });
    }

    /**
     * @return list<string>
     */
    protected function masterLandfillCategoryKeys(bool $includeNa = true): array
    {
        return $this->rememberCategoryMasterKeys('landfill', function () use ($includeNa): array {
            $keys = Landfill::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->pluck('name')
                ->map(fn ($name) => $this->normalizeCategoryKey($name))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($includeNa) {
                $keys[] = self::CATEGORY_AXIS_NA_KEY;
            }

            return $keys;
        });
    }

    /**
     * @return list<string>
     */
    protected function masterWorkTypeCategoryKeys(): array
    {
        return $this->rememberCategoryMasterKeys('work_type', function (): array {
            return WorkType::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->pluck('name')
                ->map(fn ($name) => $this->normalizeCategoryKey($name))
                ->filter()
                ->values()
                ->all();
        });
    }

    /**
     * @return list<string>
     */
    protected function masterOrganizationTypeCategoryKeys(): array
    {
        return $this->rememberCategoryMasterKeys('organization_type', function (): array {
            $keys = OrganizationType::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->pluck('name')
                ->map(fn ($name) => $this->normalizeCategoryKey($name) ?: self::CATEGORY_AXIS_NA_KEY)
                ->unique()
                ->values()
                ->all();

            if (! in_array(self::CATEGORY_AXIS_NA_KEY, $keys, true)) {
                $keys[] = self::CATEGORY_AXIS_NA_KEY;
            }

            return $keys;
        });
    }

    /**
     * @return list<string>
     */
    protected function masterVehicleTypeCategoryKeys(): array
    {
        return $this->rememberCategoryMasterKeys('vehicle_type', function (): array {
            return VehicleType::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->pluck('name')
                ->map(fn ($name) => $this->normalizeCategoryKey($name))
                ->filter()
                ->values()
                ->all();
        });
    }

    /**
     * @return list<string>
     */
    protected function masterWasteBinTypeCategoryKeys(): array
    {
        return $this->rememberCategoryMasterKeys('waste_bin_type', function (): array {
            $keys = WasteBinType::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->pluck('name')
                ->map(fn ($name) => $this->normalizeCategoryKey($name))
                ->filter()
                ->values()
                ->all();

            $keys[] = self::CATEGORY_AXIS_NA_KEY;

            return $keys;
        });
    }

    /**
     * @return list<string>
     */
    protected function masterWasteTypeCategoryKeys(): array
    {
        return $this->rememberCategoryMasterKeys('waste_type', function (): array {
            return WasteType::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->pluck('name')
                ->map(fn ($name) => $this->normalizeCategoryKey($name))
                ->filter()
                ->values()
                ->all();
        });
    }

    /**
     * @return list<string>
     */
    protected function masterOrganizationCategoryKeys(): array
    {
        return $this->rememberCategoryMasterKeys('organization', function (): array {
            $query = Organization::query()
                ->where('swm.organizations.status', true)
                ->whereNull('swm.organizations.deleted_at')
                ->orderBy('swm.organizations.name');

            $orgId = Auth::user()?->swm_organization_id;
            if ($orgId) {
                $query->where('swm.organizations.id', $orgId);
            }

            return $query
                ->pluck('name')
                ->map(fn ($name) => $this->normalizeCategoryKey($name))
                ->filter()
                ->values()
                ->all();
        });
    }

    /**
     * @return list<string>
     */
    protected function masterAttendanceDepartmentCategoryKeys(): array
    {
        return $this->rememberCategoryMasterKeys('department', function (): array {
            $orgScope = $this->attendanceDepartmentOrgScopeSql();

            $rows = DB::select(
                "
                SELECT DISTINCT COALESCE(NULLIF(TRIM(al.department), ''), ?) AS department
                FROM swm.attendance_logs al
                INNER JOIN swm.organizations o ON o.id = al.organization_id AND o.deleted_at IS NULL
                WHERE al.deleted_at IS NULL
                    {$orgScope['sql']}
                ORDER BY department
                ",
                array_merge([self::CATEGORY_AXIS_NA_KEY], $orgScope['bindings']),
            );

            return array_map(
                fn ($row) => $this->normalizeCategoryKey($row->department),
                $rows,
            );
        });
    }

    /**
     * @return array{sql: string, bindings: list<mixed>}
     */
    protected function attendanceDepartmentOrgScopeSql(): array
    {
        $orgId = Auth::user()?->swm_organization_id;
        if (! $orgId) {
            return ['sql' => '', 'bindings' => []];
        }

        return ['sql' => ' AND al.organization_id = ?', 'bindings' => [$orgId]];
    }

    /**
     * @param  list<string>  $configKeys
     * @param  callable(string): string  $labelResolver
     * @return list<string>
     */
    protected function masterConfigCategoryKeys(array $configKeys, callable $labelResolver): array
    {
        return array_map(
            fn (string $key) => $this->normalizeCategoryKey($key) ?: $key,
            array_keys($configKeys),
        );
    }

    /**
     * @param  callable(): list<string>  $resolver
     * @return list<string>
     */
    protected function rememberCategoryMasterKeys(string $cacheKey, callable $resolver): array
    {
        if (! isset($this->cachedCategoryMasterKeys[$cacheKey])) {
            $this->cachedCategoryMasterKeys[$cacheKey] = $resolver();
        }

        return $this->cachedCategoryMasterKeys[$cacheKey];
    }

    /**
     * @return list<string>|null
     */
    protected function categoryAxisMasterKeysForUnit(string $unitX): ?array
    {
        return match ($unitX) {
            __('STS'), __('Source STS') => $this->masterStsCategoryKeys(),
            __('Organization') => $this->masterOrganizationCategoryKeys(),
            __('Department') => $this->masterAttendanceDepartmentCategoryKeys(),
            default => null,
        };
    }
}
