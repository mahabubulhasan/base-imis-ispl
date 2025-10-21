<?php

namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromCollection;

class BuildContainExport implements FromView, WithTitle, WithEvents
{
    private $bufferPolygonGeom;
    private $bufferPolygonDistance;

     /**
     * BuildingsExport constructor.
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

            // Initialize the results array to hold building results
            $buildingResults = [];

            // Ensure geometries are always in an array, whether it's a single value or an array
            $geometries = is_array($this->geom) ? $this->geom : [$this->geom];

            // Iterate over each geometry (polygon)
            foreach ($geometries as $polygon) {
                // Clean up the polygon geometry string
                $polygon = trim($polygon);

                // Construct SQL query to retrieve buildings and containment relationship within the buffered area
                $buildingQuery = "
                    SELECT bc.bin, bc.containment_id
                    FROM building_info.build_contains bc
                    JOIN building_info.buildings b ON bc.bin = b.bin
                        AND b.deleted_at IS NULL
                        AND bc.bin IS NOT NULL
                        AND bc.containment_id IS NOT NULL
                    WHERE ST_Intersects(
                            ST_Buffer(ST_GeomFromText(:polygon, 4326)::GEOGRAPHY, :bufferDistancePolygon)::GEOMETRY,
                            b.geom
                        )
                    AND bc.deleted_at IS NULL
                    ORDER BY bc.bin ASC
                ";

                // Execute the query using parameter binding for the geometry and buffer distance
                $results = DB::select(DB::raw($buildingQuery), [
                    'polygon' => $polygon, // Bind the current geometry
                    'bufferDistancePolygon' => $bufferDisancePolygon, // Bind the buffer distance
                ]);

                // Merge the results from each query into the final results array
                $buildingResults = array_merge($buildingResults, $results);
            }

            // Return the view with the building results
            return view('exports.build-contain', compact('buildingResults'));
        }

      /**
     * Registers events for the export.
     *
     * @return array
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
        return 'Build-Contain List';
    }

}
