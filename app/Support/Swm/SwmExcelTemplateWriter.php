<?php

namespace App\Support\Swm;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SwmExcelTemplateWriter
{
    use ResolvesExcelColumnLetters;

    /**
     * Number of data rows that receive dropdown validations in the template.
     * Kept modest so the sheet's used-range doesn't balloon to thousands of
     * phantom rows — import readers would otherwise treat those styled-but-empty
     * rows as data. Values typed beyond this limit still import and are validated
     * server-side; they simply lack the in-cell dropdown convenience.
     */
    protected const VALIDATION_ROW_LIMIT = 200;

    /**
     * Next free row on the Instructions sheet. Set by writeInstructionsSheet()
     * and consumed by appendMultiselectInstructions() so the two sections never
     * overlap regardless of how many intro/date lines were written.
     */
    protected int $instructionsNextRow = 10;

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


        $nextGroupRefColIndex = count($columns) + 2;

        foreach ($columns as $index => $column) {
            $colLetter = $this->columnLetter($index + 1);
            $header = $column['label'] ?? $column['key'];
            $importSheet->setCellValue($colLetter.'1', $header);

            $importSheet->getStyle($colLetter.'1')->getFont()->setBold(true);
            $importSheet->getStyle($colLetter.'1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E4E4E4');

            $pairs = $column['reference_pairs'] ?? null;
            if (is_array($pairs) && count($pairs) > 0) {
                $nextGroupRefColIndex = $this->writeGroupedReference(
                    $importSheet,
                    $referenceSheet,
                    $column,
                    $colLetter,
                    $pairs,
                    $nextGroupRefColIndex
                );

                continue;
            }

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

            for ($row = 2; $row <= self::VALIDATION_ROW_LIMIT + 1; $row++) {
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
        $referenceSheet->setSheetState(Worksheet::SHEETSTATE_VISIBLE);
        $spreadsheet->setActiveSheetIndexByName('Import');

        return $spreadsheet;
    }

    /**
     * Render a two-column "group -> value" block on the Reference sheet (e.g.
     * Organization -> Driver Name) and point the import column's in-cell dropdown
     * at the value column so the reader can tell which values belong to which
     * group. Returns the next free grouped-reference column index.
     *
     * @param  array{label?: string, key: string, required?: bool, multiselect?: bool, reference_pairs_headers?: array<int, string>}  $column
     * @param  array<int, array{group?: string, value?: string}>  $pairs
     */
    protected function writeGroupedReference(
        Worksheet $importSheet,
        Worksheet $referenceSheet,
        array $column,
        string $importColLetter,
        array $pairs,
        int $nextGroupRefColIndex
    ): int {
        $header = $column['label'] ?? $column['key'];
        $headers = $column['reference_pairs_headers'] ?? [__('Group'), $header];

        $groupCol = $this->columnLetter($nextGroupRefColIndex);
        $valueCol = $this->columnLetter($nextGroupRefColIndex + 1);

        $referenceSheet->setCellValue($groupCol.'1', $headers[0] ?? __('Group'));
        $referenceSheet->setCellValue($valueCol.'1', $headers[1] ?? $header);
        $referenceSheet->getStyle($groupCol.'1')->getFont()->setBold(true);
        $referenceSheet->getStyle($valueCol.'1')->getFont()->setBold(true);

        $row = 2;
        foreach ($pairs as $pair) {
            $value = (string) ($pair['value'] ?? '');
            if ($value === '') {
                continue;
            }
            $referenceSheet->setCellValue($groupCol.$row, (string) ($pair['group'] ?? ''));
            $referenceSheet->setCellValue($valueCol.$row, $value);
            $row++;
        }
        $lastRow = $row - 1;

        // No values written (e.g. groups exist but have no entries): nothing to
        // validate against, so leave the import column free-text.
        if ($lastRow < 2) {
            return $nextGroupRefColIndex;
        }

        if (! ($column['multiselect'] ?? false)) {
            $rangeFormula = 'Reference!$'.$valueCol.'$2:$'.$valueCol.'$'.$lastRow;
            for ($r = 2; $r <= self::VALIDATION_ROW_LIMIT + 1; $r++) {
                $validation = $importSheet->getCell($importColLetter.$r)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(! ($column['required'] ?? false));
                $validation->setShowDropDown(true);
                $validation->setFormula1($rangeFormula);
            }
        }

        // Advance past this block's two columns plus a one-column gap.
        return $nextGroupRefColIndex + 3;
    }

    /**
     * @param  array<int, array{key: string, label?: string, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}>  $columns
     */
    protected function writeInstructionsSheet(Worksheet $sheet, array $columns): void
    {
        $sheet->setCellValue('A1', __('Import Instructions'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $row = 3;
        $sheet->setCellValue('A'.$row, __('Fill in data starting from row 2 on the Import sheet.'));
        $row++;
        $sheet->setCellValue('A'.$row, __('Columns marked with a dropdown allow one value selected from the list; the full set of allowed values is listed on the Reference sheet.'));
        $row++;
        $sheet->setCellValue('A'.$row, __('Required columns must have a value in each row you import.'));
        $row++;

        foreach ($columns as $column) {
            if (empty($column['reference_pairs'])) {
                continue;
            }
            $label = $column['label'] ?? $column['key'];
            $groupHeader = $column['reference_pairs_headers'][0] ?? __('Group');
            $sheet->setCellValue('A'.$row, __(':label must belong to the selected :group; see the ":group -> :label" list on the Reference sheet.', [
                'label' => $label,
                'group' => $groupHeader,
            ]));
            $row++;
        }

        $hasDerived = false;
        foreach ($columns as $column) {
            if ($column['derived'] ?? false) {
                $hasDerived = true;
                break;
            }
        }
        if ($hasDerived) {
            $sheet->setCellValue('A'.$row, __('Read-only or derived columns are filled automatically on save; leave them blank when importing.'));
            $row++;
        }

        $dateColumns = array_filter(
            $columns,
            fn (array $column) => isset($column['date_hint']) && $column['date_hint'] !== ''
        );
        if (count($dateColumns) > 0) {
            $row++;
            $sheet->setCellValue('A'.$row, __('Date columns'));
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue('A'.$row, __('Enter dates using the format shown below for each column.'));
            $row++;
            foreach ($dateColumns as $column) {
                $label = $column['label'] ?? $column['key'];
                $sheet->setCellValue('A'.$row, sprintf('%s: %s', $label, $column['date_hint']));
                $row++;
            }
        }

        $hasMultiselect = false;
        foreach ($columns as $column) {
            if (! ($column['multiselect'] ?? false)) {
                continue;
            }
            $hasMultiselect = true;
            break;
        }

        if ($hasMultiselect) {
            $row++;
            $sheet->setCellValue('A'.$row, __('Multiselect columns'));
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue('A'.$row, __('For multiselect columns, enter comma-separated values using the options listed on the Reference sheet.'));
            $row += 2;
        }

        $this->instructionsNextRow = $row;
    }

    /**
     * @param  array<int, array{column: string, section: string, ref_col: string}>  $notes
     */
    protected function appendMultiselectInstructions(Worksheet $sheet, array $notes): void
    {
        $row = $this->instructionsNextRow;
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
        $this->instructionsNextRow = $row;
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
}
