<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;

class BuildingsListExport implements FromView, WithTitle, WithEvents
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

    // Prepare an array to store all results
    $buildingResults = [];

    // Ensure the geometries are processed correctly (single or array)
    $geometries = is_array($this->geom) ? $this->geom : [$this->geom];

    // Prepare the SQL query for each geometry
    foreach ($geometries as $polygon) {
        // Clean up the polygon geometry string
        $polygon = trim($polygon);

        // Use prepared statement to prevent SQL injection
        $buildingQuery = "
            SELECT
                b.bin,
                b.tax_code,
                b.house_number,
                b.house_locality,
                b.ward,
                b.road_code,
                st.type as structure_type,
                b.floor_count,
                b.construction_year,
                b.household_served,
                b.population_served,
                b.surveyed_date,
                f.name as functional_use_id,
                u.name as use_category_id,
                b.office_business_name,
                s.source as water_source,
                b.building_associated_to,
                b.well_presence_status,
                b.distance_from_well,
                b.toilet_status,
                b.toilet_count,
                b.household_with_private_toilet,
                b.population_with_private_toilet,
                ss.sanitation_system as sanitation_system,
                b.sewer_code,
                b.drain_code,
                b.desludging_vehicle_accessible,
                b.swm_customer_id,
                b.water_customer_id,
                b.estimated_area,
                b.male_population,
                b.female_population,
                b.other_population,
                b.diff_abled_male_pop,
                b.diff_abled_female_pop,
                b.diff_abled_others_pop,
                b.verification_status,
                owners.owner_name,
                owners.owner_gender,
                owners.owner_contact,
                owners.nid,
                lic.community_name as community_name,
                b.low_income_hh,
                b.watersupply_pipe_code,
                bt.toilet_id,
                t.name as toilet_name
            FROM building_info.buildings b
            LEFT JOIN building_info.structure_types st ON b.structure_type_id = st.id
            LEFT JOIN building_info.functional_uses f ON b.functional_use_id = f.id
            LEFT JOIN building_info.use_categorys u ON b.use_category_id = u.id
            LEFT JOIN building_info.sanitation_systems ss ON b.sanitation_system_id = ss.id
            LEFT JOIN building_info.water_sources s ON b.water_source_id = s.id
            LEFT JOIN layer_info.low_income_communities lic ON lic.id = b.lic_id
            LEFT JOIN fsm.build_toilets bt ON bt.bin = b.bin AND bt.deleted_at IS NULL
            LEFT JOIN fsm.toilets t ON bt.toilet_id = t.id
            LEFT JOIN building_info.owners owners ON owners.bin = b.bin
            WHERE
                ST_Intersects(
                    ST_Buffer(ST_GeomFromText(:polygon, 4326)::GEOGRAPHY, :bufferDistancePolygon)::GEOMETRY,
                    b.geom
                )
            AND b.deleted_at IS NULL
            ORDER BY b.bin ASC
        ";

        // Run the query using a prepared statement
        $results = DB::select($buildingQuery, [
            'polygon' => $polygon,
            'bufferDistancePolygon' => $bufferDisancePolygon
        ]);

        // Merge the results
        $buildingResults = array_merge($buildingResults, $results);
    }

    // Return the view with the collected building results
    return view('exports.buildings-list', compact('buildingResults'));
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
        return 'Buildings List';
    }
}
