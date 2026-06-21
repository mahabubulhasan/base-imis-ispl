<?php

namespace App\Support\Swm;

use Carbon\Carbon;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SwmImportRowHelper
{
    public static function slugifyHeader(string $header): string
    {
        return Str::slug(trim($header), '_');
    }

    public static function normalizeRow(array $raw): array
    {
        $norm = [];
        foreach ($raw as $k => $v) {
            $key = self::slugifyHeader((string) $k);
            $norm[$key] = is_string($v) ? trim($v) : $v;
        }

        return $norm;
    }

    /**
     * @param  array<string, mixed>  $norm
     * @param  array<int, array{key: string, label?: string, import?: bool}>  $columnDefinitions
     * @return array<string, mixed>
     */
    public static function mapRowToKeys(array $norm, array $columnDefinitions): array
    {
        $mapped = [];

        foreach ($columnDefinitions as $column) {
            if (($column['import'] ?? true) === false) {
                continue;
            }

            $key = $column['key'];
            $label = (string) ($column['label'] ?? $key);
            $aliases = array_unique(array_filter([
                $key,
                strtolower($key),
                self::slugifyHeader($key),
                self::slugifyHeader($label),
            ]));

            $value = null;
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $norm)) {
                    $value = $norm[$alias];
                    break;
                }
            }

            if ($value === null) {
                foreach ($norm as $normKey => $normValue) {
                    if (self::resolveConfigKey((string) $normKey, [$key => $label]) === $key) {
                        $value = $normValue;
                        break;
                    }
                }
            }

            if ($value !== null && $value !== '') {
                $mapped[$key] = $value;
            } elseif (array_key_exists($key, $norm)) {
                $mapped[$key] = $norm[$key];
            }
        }

        return $mapped;
    }

    public static function rowIsEmpty(array $norm): bool
    {
        foreach ($norm as $v) {
            if ($v !== null && $v !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Decide whether a mapped row carries no required data at all and should be
     * skipped silently. This filters out stray, partially-formatted rows left
     * below the real data (e.g. a leftover dropdown selection or a single typed
     * cell) so they never produce "<field> is required" noise. Rows that fill
     * at least one required field are treated as genuine and still validated.
     *
     * @param  array<string, mixed>  $mapped  row keyed by canonical column key
     * @param  array<int, array{key: string, required?: bool, import?: bool}>  $columnDefinitions
     */
    public static function rowHasNoRequiredData(array $mapped, array $columnDefinitions): bool
    {
        $requiredKeys = [];
        foreach ($columnDefinitions as $column) {
            if (($column['import'] ?? true) === false) {
                continue;
            }
            if ($column['required'] ?? false) {
                $requiredKeys[] = $column['key'];
            }
        }

        if ($requiredKeys === []) {
            return false;
        }

        foreach ($requiredKeys as $key) {
            if (trim((string) ($mapped[$key] ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    public static function parseDate($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value) && (float) $value > 1) {
            try {
                return Carbon::instance(Date::excelToDateTimeObject((float) $value));
            } catch (\Throwable $e) {
            }
        }
        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function parseMonth($value): ?Carbon
    {
        $parsed = self::parseDate($value);

        return $parsed ? $parsed->copy()->startOfMonth() : null;
    }

    /**
     * Standard display format for a date-only column in exports/templates,
     * e.g. "02 Jun 2026". Returns '' for empty values so spreadsheet cells
     * stay blank rather than showing a placeholder.
     */
    public static function exportDate($value): string
    {
        $date = $value instanceof Carbon ? $value : self::parseDate($value);

        return $date ? $date->format('d M Y') : '';
    }

    /**
     * Standard display format for a datetime column in exports/templates,
     * e.g. "02 Jun 2026 14:30". The time portion is always kept.
     */
    public static function exportDateTime($value): string
    {
        $date = $value instanceof Carbon ? $value : self::parseDate($value);

        return $date ? $date->format('d M Y H:i') : '';
    }

    /**
     * Standard display format for a month column in exports/templates,
     * e.g. "Jun 2026".
     */
    public static function exportMonth($value): string
    {
        $date = $value instanceof Carbon ? $value : self::parseDate($value);

        return $date ? $date->format('M Y') : '';
    }

    public static function parseBoolean($value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_bool($value)) {
            return $value;
        }
        $v = strtolower(trim((string) $value));
        if (in_array($v, ['1', 'true', 'yes', 'y', 'active'], true)) {
            return true;
        }
        if (in_array($v, ['0', 'false', 'no', 'n', 'inactive'], true)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /** @param  array<int|string, string>  $map  id => label */
    public static function resolveByLabel(string $input, array $map): ?int
    {
        if ($input === '') {
            return null;
        }
        if (is_numeric($input)) {
            $id = (int) $input;
            if (isset($map[$id])) {
                return $id;
            }
        }
        foreach ($map as $id => $label) {
            if (strcasecmp(trim((string) $label), $input) === 0) {
                return (int) $id;
            }
        }

        return null;
    }

    /** @param  array<string, string>  $map  key => label */
    public static function resolveConfigKey(string $input, array $map): ?string
    {
        if ($input === '') {
            return null;
        }
        if (array_key_exists($input, $map)) {
            return $input;
        }
        $key = strtolower($input);
        if (array_key_exists($key, $map)) {
            return $key;
        }
        foreach ($map as $k => $label) {
            if (strcasecmp($input, (string) $label) === 0) {
                return (string) $k;
            }
            if (strcasecmp($input, (string) __($label)) === 0) {
                return (string) $k;
            }
        }

        return null;
    }

    /** @param  array<int, string>  $allowed */
    public static function resolveEnumKey(string $input, array $allowed): ?string
    {
        if ($input === '') {
            return null;
        }
        $key = strtolower($input);
        if (in_array($key, $allowed, true)) {
            return $key;
        }
        foreach ($allowed as $k) {
            if (strcasecmp($input, (string) __($k)) === 0) {
                return $k;
            }
        }

        return null;
    }

    public static function parseCommaSeparatedInts(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $parts = array_filter(array_map('trim', explode(',', $value)), fn ($v) => $v !== '');

        return array_values(array_map('intval', $parts));
    }

    /**
     * @param  array<int, array{key: string, label?: string}>  $columnDefinitions
     */
    public static function rowRequired(int $row, string $key, array $columnDefinitions): string
    {
        return __('Row :n: :label is required.', [
            'n' => $row,
            'label' => SwmExcelColumns::labelFor($columnDefinitions, $key),
        ]);
    }

    /**
     * @param  array<int, array{key: string, label?: string}>  $columnDefinitions
     */
    public static function rowInvalid(int $row, string $key, array $columnDefinitions, ?string $detail = null): string
    {
        $label = SwmExcelColumns::labelFor($columnDefinitions, $key);
        if ($detail !== null && $detail !== '') {
            return __('Row :n: :label is invalid (:detail).', ['n' => $row, 'label' => $label, 'detail' => $detail]);
        }

        return __('Row :n: :label is invalid.', ['n' => $row, 'label' => $label]);
    }

    public static function rowMessage(int $row, string $message): string
    {
        return __('Row :n: :msg', ['n' => $row, 'msg' => $message]);
    }

    /**
     * @param  array<int, array{key: string, label?: string}>  $columnDefinitions
     */
    public static function rowRequiredWhen(int $row, string $key, array $columnDefinitions, string $otherKey, string $value, array $otherColumnDefinitions = []): string
    {
        $otherLabel = $otherColumnDefinitions !== []
            ? SwmExcelColumns::labelFor($otherColumnDefinitions, $otherKey)
            : SwmExcelColumns::labelFor($columnDefinitions, $otherKey);

        return __('Row :n: :label is required when :other is :value.', [
            'n' => $row,
            'label' => SwmExcelColumns::labelFor($columnDefinitions, $key),
            'other' => $otherLabel,
            'value' => $value,
        ]);
    }

    public static function importCatchMessage(int $row, \Throwable $e): string
    {
        if ($e instanceof \InvalidArgumentException) {
            return self::rowMessage($row, $e->getMessage());
        }

        return __('Row :n: could not save.', ['n' => $row]);
    }
}
