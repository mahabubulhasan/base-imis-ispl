<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ContainmentSummaryInfoMultiSheetExport implements WithMultipleSheets
{
    private $bufferPolygonGeom;
    private $containmentReportYear;

    public function __construct($bufferPolygonGeom, $containmentReportYear)
    {
        $this->geom = $bufferPolygonGeom;
        $this->year = $containmentReportYear;
    }

    public function sheets(): array
    {
        $sheets = [];
            $sheets[] = new BuildingsEmptiedListExport($this->geom, $this->year);
            $sheets[] = new ContainmentsEmptiedListExport($this->geom, $this->year);
            
        return $sheets;
    }
}