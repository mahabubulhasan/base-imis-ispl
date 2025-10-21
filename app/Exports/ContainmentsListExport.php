<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;

class ContainmentsListExport implements FromView, WithTitle, WithEvents
{
    private $bufferPolygonGeom; 
    private $bufferPolygonDistance; 
    
     /**
     * ContainmentsExport constructor.
     *
     * @param $bufferPolygonGeom
     * @param $bufferPolygonDistance
     */
    public function __construct($bufferPolygonGeom, $bufferPolygonDistance)
    {
        $this->geom = $bufferPolygonGeom;
        $this->distance = $bufferPolygonDistance;
    }

     /**
     * Generates the view for exporting.
     *
     * @return View
     */
    public function view(): View
    {
      
         if($this->distance > 0){
                $bufferDisancePolygon = $this->distance;
            } else {
                $bufferDisancePolygon = 0;
            }
       
            // Prepare an array to store all results
            $containmentResults = [];

            // Ensure the geometries are processed correctly (single or array)
            $geometries = is_array($this->geom) ? $this->geom : [$this->geom];

            // Prepare the SQL query for each geometry
            foreach ($geometries as $polygon) {
                // Clean up the polygon geometry string
                $polygon = trim($polygon);

                // Prepare the query with parameter bindings
                $containmentQuery = "
                    SELECT DISTINCT ON (final_result.id) final_result.*
                    FROM (
                        SELECT 
                            c.*, 
                            ct.type AS containment_type, 
                            b.bin AS bin
                        FROM fsm.containments c
                        LEFT JOIN building_info.build_contains bc 
                            ON bc.containment_id = c.id 
                            AND bc.deleted_at IS NULL 
                            AND bc.bin IS NOT NULL 
                            AND bc.containment_id IS NOT NULL
                        LEFT JOIN building_info.buildings b 
                            ON b.bin = bc.bin 
                            AND b.deleted_at IS NULL
                        LEFT JOIN fsm.containment_types ct 
                            ON ct.id = c.type_id
                        WHERE 
                            ST_Intersects(
                                ST_Buffer(ST_GeomFromText(:polygon, 4326)::GEOGRAPHY, :bufferDistancePolygon)::GEOMETRY,
                                b.geom
                            ) 
                            AND c.deleted_at IS NULL
                    ) final_result
                    ORDER BY final_result.id;
                ";

                // Execute the query using parameter bindings to avoid SQL injection
                $results = DB::select(DB::raw($containmentQuery), [
                    'polygon' => $polygon,
                    'bufferDistancePolygon' => $bufferDisancePolygon
                ]);

                // Merge the results
                $containmentResults = array_merge($containmentResults, $results);
            }

            // Return the view with the results
            return view('exports.containments-list', compact('containmentResults'));
            }

    /**
     * registerEvents method: Register events for styling Excel sheet after generation.
     *
     * @return array An array of events to be registered.
     */
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