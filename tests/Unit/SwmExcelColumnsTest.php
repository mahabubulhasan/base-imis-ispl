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

    public function test_required_import_labels_uses_labels_for_required_import_columns(): void
    {
        $labels = SwmExcelColumns::requiredImportLabels([
            ['key' => 'organization', 'label' => 'Organization', 'required' => true],
            ['key' => 'name', 'label' => 'Worker Name', 'required' => true],
            ['key' => 'worker_id_no', 'label' => 'Worker ID', 'import' => false, 'required' => true],
            ['key' => 'remarks', 'label' => 'Remarks'],
        ]);

        $this->assertSame(['Organization', 'Worker Name'], $labels);
    }

    public function test_template_columns_includes_importable_and_template_only_columns(): void
    {
        $columns = SwmExcelColumns::templateColumns([
            ['key' => 'id', 'label' => 'Log ID', 'import' => false, 'template' => true, 'derived' => true],
            ['key' => 'name', 'label' => 'Name', 'required' => true],
            ['key' => 'internal', 'label' => 'Internal', 'import' => false, 'export' => false],
        ]);

        $this->assertCount(2, $columns);
        $this->assertSame('Log ID', $columns[0]['label']);
        $this->assertTrue($columns[0]['derived']);
        $this->assertSame('Name', $columns[1]['label']);
    }
}
