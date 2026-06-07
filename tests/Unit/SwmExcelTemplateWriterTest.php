<?php

namespace Tests\Unit;

use App\Support\Swm\SwmExcelTemplateWriter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPUnit\Framework\TestCase;

class SwmExcelTemplateWriterTest extends TestCase
{
    public function test_download_writes_xlsx_with_expected_headers(): void
    {
        $path = sys_get_temp_dir().'/swm-template-test-'.uniqid('', true).'.xlsx';

        ob_start();
        try {
            (new SwmExcelTemplateWriter())->download('test-template.xlsx', [
                ['key' => 'name', 'label' => 'Name', 'required' => true],
                ['key' => 'status', 'label' => 'Status', 'dropdown' => ['Active', 'Inactive']],
            ]);
        } finally {
            $output = ob_get_clean();
        }

        file_put_contents($path, $output);

        $sheet = IOFactory::load($path)->getActiveSheet();
        $this->assertSame('name', $sheet->getCell('A1')->getValue());
        $this->assertSame('status', $sheet->getCell('B1')->getValue());

        @unlink($path);
    }
}
