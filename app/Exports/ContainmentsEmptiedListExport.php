<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Auth;
class ContainmentsEmptiedListExport implements FromView, WithTitle, WithEvents
{
    private $bufferPolygonGeom;
    private $year;
    
    public function __construct($bufferPolygonGeom, $year)
    {
        $this->geom = $bufferPolygonGeom;
        $this->year = $year;
    }
    public function view(): View
    {
     
         if($this->year == 'All'){
                $yearOn = "";
            } else {
                $yearOn = "AND extract(year from e.created_at) = '$this->year'";
            }
            
        if (Auth::user()->hasRole('Service Provider - Admin')){
            $whereUser = " AND a.service_provider_id =" . Auth::user()->service_provider_id;
        }else{
            $whereUser = " AND a.user_id = " . Auth::id();
        }
            

        /**No of containment emptied**/
        
        $containmentQuery = "
                        SELECT 
                            c.*, 
                            ct.type AS containment_type,  
                            b.bin AS bin
                        FROM (
                            SELECT m AS month_val FROM GENERATE_SERIES(1,12) m
                        ) AS months
                        LEFT JOIN fsm.emptyings e 
                            ON months.month_val = EXTRACT(MONTH FROM e.created_at)
                            $yearOn
                        LEFT JOIN fsm.applications a 
                            ON e.application_id = a.id
                        LEFT JOIN fsm.containments c 
                            ON c.id = a.containment_id
                        LEFT JOIN fsm.containment_types ct 
                            ON ct.id = c.type_id
                        LEFT JOIN building_info.build_contains bc 
                            ON bc.containment_id = c.id 
                            AND bc.deleted_at IS NULL 
                            AND bc.bin IS NOT NULL 
                            AND bc.containment_id IS NOT NULL
                        LEFT JOIN building_info.buildings b 
                            ON b.bin = bc.bin 
                            AND b.deleted_at IS NULL
                        WHERE 
                            c.emptied_status = true
                            AND ST_Intersects(
                                c.geom, 
                                ST_SetSRID(ST_GeomFromText('" . $this->geom . "'), 4326)
                            )
                        ORDER BY c.id ASC
                    ";

                            

        $containmentResults = DB::select($containmentQuery);
         
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