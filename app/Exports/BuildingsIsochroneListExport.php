<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Models\BuildingInfo\Building;

class BuildingsIsochroneListExport implements FromView, WithTitle, WithEvents
{
   
    
    public function __construct($bins)
    {
        $this->bins = $bins;
    }
    public function view(): View
    {
        
        $buildingResults = Building::whereIn('bin',$this->bins)->get();
        return view('exports.buildings-list', compact('buildingResults'));
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:B1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
            }
        ];
    }
     /**
     * @return string
     */
    public function title(): string
    {
        return 'Buildings List';
    }
}