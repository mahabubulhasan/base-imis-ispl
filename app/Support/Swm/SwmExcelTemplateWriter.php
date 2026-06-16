<?php

namespace App\Support\Swm;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SwmExcelTemplateWriter
{
    /**
     * @param  array<int, array{key: string, label?: string, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}>  $columns
     */
    public function download(string $filename, array $columns): void
    {
        $spreadsheet = $this->buildSpreadsheet($columns);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.str_replace('"', '', $filename).'"');
        header('Cache-Control: max-age=0');

        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    /**
     * @param  array<int, array{key: string, label?: string, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}>  $columns
     */
    public function buildSpreadsheet(array $columns): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $importSheet = $spreadsheet->getActiveSheet();
        $importSheet->setTitle('Import');

        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instructions');
        $this->writeInstructionsSheet($instructionsSheet, $columns);

        $referenceSheet = $spreadsheet->createSheet();
        $referenceSheet->setTitle('Reference');

        $multiselectNotes = [];

        foreach ($columns as $index => $column) {
            $colLetter = $this->columnLetter($index + 1);
            $header = $column['label'] ?? $column['key'];
            $importSheet->setCellValue($colLetter.'1', $header);

            $importSheet->getStyle($colLetter.'1')->getFont()->setBold(true);
            $importSheet->getStyle($colLetter.'1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E4E4E4');

            $dropdown = $column['dropdown'] ?? null;
            if (! is_array($dropdown) || count($dropdown) === 0) {
                continue;
            }

            $values = array_values(array_filter(array_map('strval', $dropdown), fn ($v) => $v !== ''));
            if (count($values) === 0) {
                continue;
            }

            $sectionKey = $column['reference_key'] ?? $header;
            $refCol = $this->columnLetter($index + 1);
            $referenceSheet->setCellValue($refCol.'1', $sectionKey);
            $referenceSheet->getStyle($refCol.'1')->getFont()->setBold(true);

            foreach ($values as $i => $value) {
                $referenceSheet->setCellValue($refCol.($i + 2), $value);
            }

            $lastRow = count($values) + 1;
            $rangeFormula = 'Reference!$'.$refCol.'$2:$'.$refCol.'$'.$lastRow;

            if ($column['multiselect'] ?? false) {
                $multiselectNotes[] = [
                    'column' => $header,
                    'section' => $sectionKey,
                    'ref_col' => $refCol,
                ];

                continue;
            }

            for ($row = 2; $row <= 1001; $row++) {
                $validation = $importSheet->getCell($colLetter.$row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(! ($column['required'] ?? false));
                $validation->setShowDropDown(true);
                $validation->setFormula1($rangeFormula);
            }
        }

        if (count($multiselectNotes) > 0) {
            $this->appendMultiselectInstructions($instructionsSheet, $multiselectNotes);
        }

        $importSheet->freezePane('A2');
        $referenceSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
        $spreadsheet->setActiveSheetIndexByName('Import');

        return $spreadsheet;
    }

    /**
     * @param  array<int, array{key: string, label?: string, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}>  $columns
     */
    protected function writeInstructionsSheet(Worksheet $sheet, array $columns): void
    {
        $sheet->setCellValue('A1', __('Import Instructions'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A3', __('Fill in data starting from row 2 on the Import sheet.'));
        $sheet->setCellValue('A4', __('Columns marked with a dropdown allow one value selected from the list.'));
        $sheet->setCellValue('A5', __('Required columns must have a value in each row you import.'));

        $row = 7;
        $hasMultiselect = false;
        foreach ($columns as $column) {
            if (! ($column['multiselect'] ?? false)) {
                continue;
            }
            $hasMultiselect = true;
            break;
        }

        if ($hasMultiselect) {
            $sheet->setCellValue('A'.$row, __('Multiselect columns'));
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue('A'.$row, __('For multiselect columns, enter comma-separated values using options from the Reference sheet (hidden).'));
            $row += 2;
        }
    }

    /**
     * @param  array<int, array{column: string, section: string, ref_col: string}>  $notes
     */
    protected function appendMultiselectInstructions(Worksheet $sheet, array $notes): void
    {
        $row = 10;
        foreach ($notes as $note) {
            $sheet->setCellValue(
                'A'.$row,
                sprintf(
                    '%s: %s (Reference sheet column %s, rows 2+)',
                    $note['column'],
                    __('enter comma-separated values'),
                    $note['ref_col']
                )
            );
            $row++;
        }
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
