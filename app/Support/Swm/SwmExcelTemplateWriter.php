<?php

namespace App\Support\Swm;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SwmExcelTemplateWriter
{
    /** @param  array<int, array{key: string, label: string, required?: bool, dropdown?: array<int, string>}>  $columns */
    public function download(string $filename, array $columns): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import');

        $referenceSheet = $spreadsheet->createSheet();
        $referenceSheet->setTitle('Reference');
        $referenceRow = 1;

        foreach ($columns as $index => $column) {
            $colLetter = $this->columnLetter($index + 1);
            $header = $column['key'];
            $sheet->setCellValue($colLetter.'1', $header);

            $sheet->getStyle($colLetter.'1')->getFont()->setBold(true);
            $sheet->getStyle($colLetter.'1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E4E4E4');

            $dropdown = $column['dropdown'] ?? null;
            if (is_array($dropdown) && count($dropdown) > 0) {
                $values = array_values(array_filter(array_map('strval', $dropdown), fn ($v) => $v !== ''));
                if (count($values) > 0) {
                    if (count($values) <= 30) {
                        $formula = '"'.implode(',', array_map(fn ($v) => str_replace('"', '""', $v), $values)).'"';
                    } else {
                        $refCol = $this->columnLetter($index + 1);
                        foreach ($values as $i => $value) {
                            $referenceSheet->setCellValue($refCol.($i + 1), $value);
                        }
                        $lastRow = count($values);
                        $formula = 'Reference!$'.$refCol.'$1:$'.$refCol.'$'.$lastRow;
                    }

                    for ($row = 2; $row <= 1001; $row++) {
                        $validation = $sheet->getCell($colLetter.$row)->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_STOP);
                        $validation->setAllowBlank(! ($column['required'] ?? false));
                        $validation->setShowDropDown(true);
                        $validation->setFormula1($formula);
                    }
                }
            }
        }

        $sheet->freezePane('A2');
        $referenceSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.str_replace('"', '', $filename).'"');
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    /**
     * @param  array<int, string>  $headers
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public function downloadData(string $filename, array $headers, iterable $rows): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Export');

        foreach ($headers as $index => $header) {
            $colLetter = $this->columnLetter($index + 1);
            $sheet->setCellValue($colLetter.'1', $header);
            $sheet->getStyle($colLetter.'1')->getFont()->setBold(true);
            $sheet->getStyle($colLetter.'1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E4E4E4');
        }

        $rowNum = 2;
        foreach ($rows as $row) {
            foreach (array_values($row) as $index => $value) {
                $sheet->setCellValue($this->columnLetter($index + 1).$rowNum, $value);
            }
            $rowNum++;
        }

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
