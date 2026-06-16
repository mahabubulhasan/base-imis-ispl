<?php

namespace App\Support\Swm;

class SwmExcelFilename
{
    public static function importTemplate(string $entitySlug): string
    {
        return $entitySlug.'_import_template.xlsx';
    }

    public static function export(string $entitySlug): string
    {
        return $entitySlug.'_export_'.now()->format('Y-m-d').'.xlsx';
    }
}
