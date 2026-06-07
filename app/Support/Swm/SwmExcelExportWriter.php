<?php

namespace App\Support\Swm;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SwmExcelExportWriter
{
    /** @param  array<int, string>  $headers */
    public function download(string $filename, array $headers, callable $writeRows): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $index => $header) {
            $colLetter = $this->columnLetter($index + 1);
            $sheet->setCellValue($colLetter.'1', $header);
            $sheet->getStyle($colLetter.'1')->getFont()->setBold(true);
            $sheet->getStyle($colLetter.'1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E4E4E4');
        }

        $writeRows($sheet, fn (int $index) => $this->columnLetter($index));

        $sheet->freezePane('A2');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.str_replace('"', '', $filename).'"');
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    protected function columnLetter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)).$letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }
}
