<?php

namespace Tests\Unit;

use App\Support\Swm\SwmExcelTemplateWriter;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\TestCase;

class SwmExcelTemplateWriterTest extends TestCase
{
    public function test_build_spreadsheet_writes_expected_headers(): void
    {
        $spreadsheet = (new SwmExcelTemplateWriter())->buildSpreadsheet([
            ['key' => 'name', 'label' => 'Name', 'required' => true],
            ['key' => 'status', 'label' => 'Status', 'dropdown' => ['Active', 'Inactive']],
        ]);

        $importSheet = $spreadsheet->getSheetByName('Import');
        $this->assertNotNull($importSheet);
        $this->assertSame('name', $importSheet->getCell('A1')->getValue());
        $this->assertSame('status', $importSheet->getCell('B1')->getValue());
    }

    public function test_single_select_dropdown_uses_reference_sheet_range(): void
    {
        $spreadsheet = (new SwmExcelTemplateWriter())->buildSpreadsheet([
            ['key' => 'status', 'dropdown' => ['Active, With Comma', 'Inactive']],
        ]);

        $referenceSheet = $spreadsheet->getSheetByName('Reference');
        $importSheet = $spreadsheet->getSheetByName('Import');

        $this->assertSame('status', $referenceSheet->getCell('A1')->getValue());
        $this->assertSame('Active, With Comma', $referenceSheet->getCell('A2')->getValue());
        $this->assertSame('Inactive', $referenceSheet->getCell('A3')->getValue());

        $validation = $importSheet->getCell('A2')->getDataValidation();
        $this->assertSame(DataValidation::TYPE_LIST, $validation->getType());
        $this->assertSame('Reference!$A$2:$A$3', $validation->getFormula1());

        $this->assertSame(Worksheet::SHEETSTATE_VISIBLE, $referenceSheet->getSheetState());
    }

    public function test_multiselect_column_has_no_list_validation_and_writes_instructions(): void
    {
        $spreadsheet = (new SwmExcelTemplateWriter())->buildSpreadsheet([
            [
                'key' => 'service_wards',
                'multiselect' => true,
                'dropdown' => ['1', '2', '3'],
            ],
        ]);

        $importSheet = $spreadsheet->getSheetByName('Import');
        $referenceSheet = $spreadsheet->getSheetByName('Reference');
        $instructionsSheet = $spreadsheet->getSheetByName('Instructions');

        $this->assertSame('service_wards', $referenceSheet->getCell('A1')->getValue());
        $this->assertSame('1', $referenceSheet->getCell('A2')->getValue());

        $validation = $importSheet->getCell('A2')->getDataValidation();
        $this->assertSame(DataValidation::TYPE_NONE, $validation->getType());

        $this->assertStringContainsString('service_wards', (string) $instructionsSheet->getCell('A10')->getValue());
    }

    public function test_build_spreadsheet_can_be_saved_to_xlsx(): void
    {
        $path = sys_get_temp_dir().'/swm-template-test-'.uniqid('', true).'.xlsx';
        $spreadsheet = (new SwmExcelTemplateWriter())->buildSpreadsheet([
            ['key' => 'name', 'required' => true],
        ]);

        (new Xlsx($spreadsheet))->save($path);

        $loaded = IOFactory::load($path);
        $this->assertSame('name', $loaded->getSheetByName('Import')->getCell('A1')->getValue());

        @unlink($path);
    }
}
