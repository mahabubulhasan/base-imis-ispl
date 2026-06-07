<?php

namespace App\Support\Swm;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SwmImportRowHelper
{
    public static function normalizeRow(array $raw): array
    {
        $norm = [];
        foreach ($raw as $k => $v) {
            $key = strtolower(trim(preg_replace('/\s+/', '_', (string) $k)));
            $norm[$key] = is_string($v) ? trim($v) : $v;
        }

        return $norm;
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
}
