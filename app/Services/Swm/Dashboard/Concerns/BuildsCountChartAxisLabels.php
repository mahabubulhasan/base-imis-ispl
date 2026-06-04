<?php

namespace App\Services\Swm\Dashboard\Concerns;

use App\Models\LayerInfo\Ward;
use App\Services\Swm\Dashboard\SwmDashboardAxisKeys;

trait BuildsCountChartAxisLabels
{
    use AlignsChartsToStaticCategoryAxis;

    /** @var list<string>|null */
    protected ?array $cachedMunicipalWardKeys = null;

    protected function countChartAxisY(string $entity): string
    {
        return __('Number of :entity', ['entity' => $entity]);
    }

    /**
     * @return list<string>
     */
    protected function municipalWardKeys(): array
    {
        if ($this->cachedMunicipalWardKeys !== null) {
            return $this->cachedMunicipalWardKeys;
        }

        $wards = Ward::getInAscOrder();

        $this->cachedMunicipalWardKeys = array_map(
            fn ($ward) => $this->normalizeWardKey($ward),
            array_values($wards),
        );

        return $this->cachedMunicipalWardKeys;
    }

    /**
     * @return list<string>
     */
    protected function wardAxisKeys(bool $appendUnknown = false): array
    {
        $keys = $this->municipalWardKeys();

        if ($keys === []) {
            return [];
        }

        if ($appendUnknown) {
            $keys[] = SwmDashboardAxisKeys::WARD_AXIS_UNKNOWN_KEY;
        }

        return $keys;
    }

    /**
     * @return list<string>
     */
    protected function wardAxisLabels(bool $appendUnknown = false): array
    {
        return array_map(
            fn (string $key) => $this->wardAxisDisplayLabel($key),
            $this->wardAxisKeys($appendUnknown),
        );
    }

    protected function wardAxisDisplayLabel(string $wardKey): string
    {
        if ($wardKey === SwmDashboardAxisKeys::WARD_AXIS_UNKNOWN_KEY) {
            return __('Unknown');
        }

        return $wardKey;
    }

    /**
     * @param  array<string|int, int|float|string>  $countsByWard
     * @return array{labels: list<string>, values: list<int|float>}
     */
    protected function alignCountsToWardAxis(array $countsByWard, bool $appendUnknown = false): array
    {
        return $this->alignCountsToCategoryAxis(
            $countsByWard,
            $this->resolveWardAxisKeys($this->normalizeCountsByCategoryKey($countsByWard), $appendUnknown),
            fn (string $key) => $this->wardAxisDisplayLabel($key),
        );
    }

    /**
     * @param  array<string|int, array<string|int, int|float|string>>  $seriesByWard
     * @param  list<string>  $seriesKeys
     * @param  callable(string): string  $seriesLabel
     * @return array{labels: list<string>, datasets: list<array{label: string, data: list<int|float>}>}
     */
    protected function alignStackedSeriesToWardAxis(
        array $seriesByWard,
        array $seriesKeys,
        callable $seriesLabel,
        bool $appendUnknown = false,
    ): array {
        $normalizedByWard = [];
        foreach ($seriesByWard as $ward => $seriesCounts) {
            $wardKey = $this->normalizeWardKey($ward);
            if ($wardKey === '') {
                continue;
            }
            foreach ($seriesCounts as $seriesKey => $value) {
                $normalizedByWard[$wardKey][(string) $seriesKey] = is_numeric($value)
                    ? (float) $value
                    : 0.0;
            }
        }

        $flatCounts = [];
        foreach ($normalizedByWard as $wardKey => $seriesCounts) {
            $flatCounts[$wardKey] = array_sum($seriesCounts);
        }

        return $this->alignStackedSeriesToCategoryAxis(
            $seriesByWard,
            $seriesKeys,
            $seriesLabel,
            $this->resolveWardAxisKeys($flatCounts, $appendUnknown),
            fn (string $key) => $this->wardAxisDisplayLabel($key),
        );
    }

    /**
     * @param  array<string, float>  $countsByWard
     * @return list<string>
     */
    protected function resolveWardAxisKeys(array $countsByWard, bool $appendUnknown): array
    {
        $master = $this->municipalWardKeys();

        if ($master !== []) {
            $keys = $master;
        } else {
            $keys = array_keys($countsByWard);
            usort($keys, static function (string $a, string $b): int {
                if ($a === SwmDashboardAxisKeys::WARD_AXIS_UNKNOWN_KEY) {
                    return 1;
                }
                if ($b === SwmDashboardAxisKeys::WARD_AXIS_UNKNOWN_KEY) {
                    return -1;
                }

                return strnatcasecmp($a, $b);
            });
        }

        if ($appendUnknown && ! in_array(SwmDashboardAxisKeys::WARD_AXIS_UNKNOWN_KEY, $keys, true)) {
            $keys[] = SwmDashboardAxisKeys::WARD_AXIS_UNKNOWN_KEY;
        }

        return $keys;
    }

    protected function normalizeWardKey(mixed $ward): string
    {
        if ($ward === null || $ward === '') {
            return '';
        }

        return trim((string) $ward);
    }
}
