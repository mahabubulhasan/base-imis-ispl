<?php

namespace App\Support\Swm;

class SwmExcelColumns
{
    /** @var array<int, string> */
    private const FILE_COLUMN_KEYS = [
        'photo_attachment',
        'photo_attachment_path',
        'receipt_copy',
        'receipt_copy_path',
    ];

    /**
     * @param  array{key: string, file?: bool}  $column
     */
    public static function isFileColumn(array $column): bool
    {
        if (($column['file'] ?? false) === true) {
            return true;
        }

        return in_array($column['key'] ?? '', self::FILE_COLUMN_KEYS, true);
    }

    /**
     * @param  array<int, array{key: string, label: string, export?: bool, import?: bool, file?: bool, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}>  $columns
     * @return array<int, array{key: string, label: string, export?: bool, import?: bool, file?: bool, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}>
     */
    public static function excelColumns(array $columns): array
    {
        return array_values(array_filter(
            $columns,
            fn (array $column) => ! self::isFileColumn($column)
        ));
    }

    /**
     * @param  array<int, array{key: string, label: string, export?: bool, import?: bool, file?: bool, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}>  $columns
     * @return array<int, string>
     */
    public static function exportHeaders(array $columns): array
    {
        return array_values(array_map(
            fn (array $column) => $column['label'],
            array_filter(self::excelColumns($columns), fn (array $column) => ($column['export'] ?? true) === true)
        ));
    }

    /**
     * @param  array<int, array{key: string, label: string, export?: bool, import?: bool, template?: bool, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string, derived?: bool}>  $columns
     * @return array<int, array{key: string, label?: string, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}>
     */
    public static function templateColumns(array $columns): array
    {
        return array_values(array_map(
            function (array $column): array {
                $templateColumn = [
                    'key' => $column['key'],
                    'label' => $column['label'] ?? $column['key'],
                ];

                if (array_key_exists('required', $column)) {
                    $templateColumn['required'] = $column['required'];
                }
                if (isset($column['dropdown'])) {
                    $templateColumn['dropdown'] = $column['dropdown'];
                }
                if (array_key_exists('multiselect', $column)) {
                    $templateColumn['multiselect'] = $column['multiselect'];
                }
                if (isset($column['reference_key'])) {
                    $templateColumn['reference_key'] = $column['reference_key'];
                }
                if (isset($column['date_hint'])) {
                    $templateColumn['date_hint'] = $column['date_hint'];
                }
                if (($column['derived'] ?? false) === true) {
                    $templateColumn['derived'] = true;
                }

                return $templateColumn;
            },
            array_filter(
                self::excelColumns($columns),
                fn (array $column) => ($column['import'] ?? true) === true || ($column['template'] ?? false) === true
            )
        ));
    }

    /**
     * @param  array<int, array{key: string, label: string, export?: bool, import?: bool, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}>  $columns
     * @return array<int, array{key: string, label?: string, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}>
     */
    public static function importTemplateColumns(array $columns): array
    {
        return array_values(array_map(
            function (array $column): array {
                $importColumn = [
                    'key' => $column['key'],
                    'label' => $column['label'] ?? $column['key'],
                ];

                if (array_key_exists('required', $column)) {
                    $importColumn['required'] = $column['required'];
                }
                if (isset($column['dropdown'])) {
                    $importColumn['dropdown'] = $column['dropdown'];
                }
                if (array_key_exists('multiselect', $column)) {
                    $importColumn['multiselect'] = $column['multiselect'];
                }
                if (isset($column['reference_key'])) {
                    $importColumn['reference_key'] = $column['reference_key'];
                }

                return $importColumn;
            },
            array_filter(self::excelColumns($columns), fn (array $column) => ($column['import'] ?? true) === true)
        ));
    }

    /**
     * @param  array<int, array{key: string, label?: string, required?: bool, import?: bool}>  $columns
     * @return array<int, string>
     */
    public static function requiredImportLabels(array $columns): array
    {
        return array_values(array_map(
            fn (array $column) => (string) ($column['label'] ?? $column['key']),
            array_filter(
                self::excelColumns($columns),
                fn (array $column) => ($column['import'] ?? true) === true && ($column['required'] ?? false) === true
            )
        ));
    }

    /**
     * @param  array<int, array{key: string, label?: string}>  $columns
     * @return array<string, string>
     */
    public static function labelMap(array $columns): array
    {
        $map = [];
        foreach (self::excelColumns($columns) as $column) {
            $map[$column['key']] = (string) ($column['label'] ?? $column['key']);
        }

        return $map;
    }

    /**
     * @param  array<int, array{key: string, label?: string}>  $columns
     */
    public static function labelFor(array $columns, string $key, ?string $fallback = null): string
    {
        $map = self::labelMap($columns);

        return $map[$key] ?? $fallback ?? $key;
    }

    /**
     * @param  array<int, array{key: string, label: string, export?: bool}>  $columns
     * @return array<int, array{key: string, label: string}>
     */
    public static function exportableColumns(array $columns): array
    {
        return array_values(array_filter(
            self::excelColumns($columns),
            fn (array $column) => ($column['export'] ?? true) === true
        ));
    }

    /**
     * @param  array<int, array{key: string, label: string, export?: bool}>  $columns
     * @param  callable(string, mixed): mixed  $valueResolver
     * @return array<int, mixed>
     */
    public static function buildExportRow(array $columns, mixed $model, callable $valueResolver): array
    {
        $row = [];
        foreach (self::exportableColumns($columns) as $column) {
            $row[] = $valueResolver($column['key'], $model);
        }

        return $row;
    }
}
