<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Models\BuildingInfo\Building;

class ContainmentsIsochroneListExport implements FromView, WithTitle, WithEvents
{
    private $bin;
    
    public function __construct($bin)
    {
        $this->bin = $bin;
      
    }
    public function view(): View
    {
     
     
       $containmentResults = array();
        $containments = Building::whereIn('bin',$this->bin)->whereHas('containments')->get();
        foreach($containments as $containment)
        {
            array_push($containmentResults,$containment->containments[0]);
        }
         
        return view('exports.containments-list', compact('containmentResults'));
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
        return 'Containment List';
    }
}