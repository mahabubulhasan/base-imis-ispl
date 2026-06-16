<?php

namespace Tests\Unit;

use App\Support\Swm\SwmExcelColumns;
use PHPUnit\Framework\TestCase;

class SwmExcelColumnsTest extends TestCase
{
    public function test_export_headers_returns_labels_in_order_for_exportable_columns(): void
    {
        $headers = SwmExcelColumns::exportHeaders([
            ['key' => 'sts_id', 'label' => 'STS ID', 'import' => false],
            ['key' => 'name', 'label' => 'STS Name'],
            ['key' => 'internal', 'label' => 'Internal', 'export' => false],
        ]);

        $this->assertSame(['STS ID', 'STS Name'], $headers);
    }

    public function test_import_template_columns_filters_and_preserves_metadata(): void
    {
        $columns = SwmExcelColumns::importTemplateColumns([
            ['key' => 'sts_id', 'label' => 'STS ID', 'import' => false],
            ['key' => 'name', 'label' => 'STS Name', 'required' => true],
            [
                'key' => 'source_wards',
                'label' => 'Source Wards',
                'multiselect' => true,
                'dropdown' => ['1', '2'],
                'reference_key' => 'source_wards',
            ],
        ]);

        $this->assertCount(2, $columns);
        $this->assertSame('STS Name', $columns[0]['label']);
        $this->assertTrue($columns[0]['required']);
        $this->assertSame('source_wards', $columns[1]['reference_key']);
        $this->assertTrue($columns[1]['multiselect']);
    }
}
