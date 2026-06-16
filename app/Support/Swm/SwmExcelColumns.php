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
}
