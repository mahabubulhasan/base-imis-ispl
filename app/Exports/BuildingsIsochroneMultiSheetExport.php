<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BuildingsIsochroneMultiSheetExport implements WithMultipleSheets
{
    private $bin;


    public function __construct($bin)
    {
        $this->bin = $bin;
 
    }

    public function sheets(): array
    {
        $sheets = [];
        
            $sheets[] = new BuildingsIsochroneListExport($this->bin);
            $sheets[] = new ContainmentsIsochroneListExport($this->bin);
        
        return $sheets;
    }
}