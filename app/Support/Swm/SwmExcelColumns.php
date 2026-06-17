<?php

namespace App\Support\Swm;

class SwmExcelColumns
{
    /**
     * @param  array<int, array{key: string, label: string, export?: bool, import?: bool, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}>  $columns
     * @return array<int, string>
     */
    public static function exportHeaders(array $columns): array
    {
        return array_values(array_map(
            fn (array $column) => $column['label'],
            array_filter($columns, fn (array $column) => ($column['export'] ?? true) === true)
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
            array_filter($columns, fn (array $column) => ($column['import'] ?? true) === true)
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
                $columns,
                fn (array $column) => ($column['import'] ?? true) === true && ($column['required'] ?? false) === true
            )
        ));
    }

    /**
     * @param  array<int, array{key: string, label: string, export?: bool}>  $columns
     * @return array<int, array{key: string, label: string}>
     */
    public static function exportableColumns(array $columns): array
    {
        return array_values(array_filter(
            $columns,
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
