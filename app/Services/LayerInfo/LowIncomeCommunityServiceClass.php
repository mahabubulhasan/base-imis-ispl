<?php

namespace App\Services\LayerInfo;

use Illuminate\Http\Request;
use Auth;
use DataTables;
use DB;
use App\Models\LayerInfo\LowIncomeCommunity;
use App\Models\LayerInfo\Ward;
use App\Services\Swm\Concerns\HasExcelColumnValidationLabels;
use App\Support\Swm\SwmExcelColumns;
use App\Support\Swm\SwmExcelExportWriter;
use App\Support\Swm\SwmExcelFilename;
use App\Support\Swm\SwmExcelTemplateWriter;
use App\Support\Swm\SwmImportRowHelper;

class LowIncomeCommunityServiceClass
{
    use HasExcelColumnValidationLabels;

    private function isTrueValue($value): bool
    {
        return in_array($value, [true, 1, '1', 'true'], true);
    }

    /**
     * Fetch and format data for DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchData($request)
    {
        $lic = LowIncomeCommunity::whereNull('deleted_at');



        return Datatables::of($lic)
            ->filter(function ($query) use ($request) {
                if ($request->community_name) {
                    $query->whereRaw('LOWER(community_name) LIKE ?', ['%' . strtolower($request->community_name) . '%']);
                }
            })

            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['low-income-communities.destroy', $model->id]]);

                if (Auth::user()->can('Edit Low Income Community')) {
                    $content .= '<a title="' . __("Edit") . '" href="' . action("LayerInfo\LowIncomeCommunityController@edit", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }
                if (Auth::user()->can('View Low Income Community')) {
                    $content .= '<a title="' . __("Detail") . '" href="' . action("LayerInfo\LowIncomeCommunityController@show", [$model->id]) . '"class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }
                if (Auth::user()->can('View Low Income Community History')) {
                    $content .= '<a title="' . __("History") . '" href="' . action("LayerInfo\LowIncomeCommunityController@history", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }
                if (Auth::user()->can('Delete Low Income Community')) {
                    $content .= '<a title="' . __("Delete") . '" class="delete btn btn-danger btn-sm mb-1">&nbsp;<i class="fa fa-trash"></i>&nbsp;</a> ';
                }
                if (Auth::user()->can('View Low Income Community On Map')) {
                    $content .= '<a title="' . __("Map") . '" href="' . action("MapsController@index", ['layer' => 'low_income_communities_layer', 'field' => 'id', 'val' => $model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-map-marker"></i></a> ';
                }

                $content .= \Form::close();
                return $content;
            })
            ->make(true);
    }
    /**
     * Store data for a Low income community record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeData($request)
{
    // If geom is not provided or validation fails, don't update the data
    if (empty($request->geom)) {
        return redirect('layer-info/low-income-communities/create')
            ->with('error', __('Failed to Add Low Income Community'))
            ->withInput();  // Retain the user's input
    }

    $lic = new LowIncomeCommunity();
    $lic->sub_location = $request->sub_location;
    $lic->ward = $request->ward;
    $lic->road_no = $request->road_no;
    $lic->road_name = $request->road_name;
    $lic->area_decima = $request->area_decima;
    $lic->representative_name = $request->representative_name;
    $lic->representative_contact_no = $request->representative_contact_no;
    $lic->population_total = $request->population_total;
    $lic->number_of_households = $request->number_of_households;
    $lic->population_male = $request->population_male;
    $lic->population_female = $request->population_female;
    $lic->population_others = $request->population_others;
    $lic->water_connection_status = $request->water_connection_status;
    $lic->no_of_wate_points = $this->isTrueValue($request->water_connection_status) ? $request->no_of_wate_points : null;
    $lic->sanitation_status = $request->sanitation_status;
    $lic->no_of_septic_tank = $request->no_of_septic_tank;
    $lic->no_of_holding_tank = $request->no_of_holding_tank;
    $lic->no_of_pit = $request->no_of_pit;
    $lic->no_of_sewer_connection = $request->no_of_sewer_connection;
    $lic->no_of_buildings = $request->no_of_buildings;
    $lic->community_name = $request->community_name;
    $lic->no_of_community_toilets = $this->isTrueValue($request->sanitation_status) ? $request->no_of_community_toilets : null;
    $lic->remarks = $request->remarks;

    // Retrieve the municipality boundary geometry
    $citypolygeom = DB::table('layer_info.citypolys')->where('id', 1)->value('geom');

    // Check if the geom is within the municipality boundary
    $contains = DB::select(
        "SELECT ST_Contains(?, ST_GeomFromText(?, 4326)) as contains",
        [$citypolygeom, $request->geom]
    )[0]->contains;

    if ($contains === true) {
        // Find the ward for the geometry if it's inside the boundary
        $ward = DB::select("SELECT w.ward
            FROM layer_info.wards w
            WHERE ST_Intersects(w.geom, ST_GeomFromText('" . $request->geom . "', 4326))
            ORDER BY ST_Area(ST_Intersection(w.geom, ST_GeomFromText('" . $request->geom . "', 4326))) DESC
            LIMIT 1");

        // If a ward is found, save the geometry
        if (!empty($ward)) {
            $lic->geom = DB::raw("ST_Multi(ST_GeomFromText('" . $request->geom . "', 4326))");
            $lic->save();
            return redirect('layer-info/low-income-communities')->with('success', __('Low Income Community added successfully.'));
        } else {
            return redirect('layer-info/low-income-communities/create')
                ->with('error', __('Failed to find the ward for the selected area'))
                ->withInput(); // Retain the user's input
        }
    } else {
        // If the geometry is not within the boundary, show an error
        return redirect('layer-info/low-income-communities/create')
            ->with('error', __('The selected area should be within the Municipality Boundary'))
            ->withInput(); // Retain the user's input
    }
}



    /**
     * Update data for a Low income community record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id The ID of the Low income community record to update
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateData($request, $id)
    {
        $lic = LowIncomeCommunity::find($id);

        if ($lic) {
            $lic->sub_location = $request->sub_location;
            $lic->ward = $request->ward;
            $lic->road_no = $request->road_no;
            $lic->road_name = $request->road_name;
            $lic->area_decima = $request->area_decima;
            $lic->representative_name = $request->representative_name;
            $lic->representative_contact_no = $request->representative_contact_no;
            $lic->population_total = $request->population_total;
            $lic->number_of_households = $request->number_of_households;
            $lic->population_male = $request->population_male;
            $lic->population_female = $request->population_female;
            $lic->population_others = $request->population_others;
            $lic->water_connection_status = $request->water_connection_status;
            $lic->no_of_wate_points = $this->isTrueValue($request->water_connection_status) ? $request->no_of_wate_points : null;
            $lic->sanitation_status = $request->sanitation_status;
            $lic->no_of_septic_tank = $request->no_of_septic_tank;
            $lic->no_of_holding_tank = $request->no_of_holding_tank;
            $lic->no_of_pit = $request->no_of_pit;
            $lic->no_of_sewer_connection = $request->no_of_sewer_connection;
            $lic->no_of_buildings = $request->no_of_buildings;
            $lic->community_name = $request->community_name;
            $lic->no_of_community_toilets = $this->isTrueValue($request->sanitation_status) ? $request->no_of_community_toilets : null;
            $lic->remarks = $request->remarks;

            // Check if 'geom' is provided in the request
            if (!empty($request->geom)) {
                $citypolygeom = DB::table('layer_info.citypolys')->where('id', 1)->value('geom');
                $contains = DB::select(
                    "SELECT ST_Contains(?, ST_GeomFromText(?, 4326)) as contains",
                    [$citypolygeom, $request->geom]
                )[0]->contains;

                if (!empty($contains) && $contains === true) {
                    $ward = DB::select("SELECT w.ward
                        FROM layer_info.wards w, ST_Intersects(w.geom, ST_GeomFromText('" . $request->geom . "', 4326))
                        ORDER BY
                            ST_Area(ST_Intersection(w.geom, ST_GeomFromText('" . $request->geom . "', 4326))) DESC
                        LIMIT 1");
                    $lic->geom = DB::raw("ST_Multi(ST_GeomFromText('" . $request->geom . "', 4326))");
                    $lic->save();
                } else {
                    // Retain input on error
                    return redirect('layer-info/low-income-communities/' . $id . '/edit')
                        ->with('error', __('The selected area should be within the Municipality Boundary'))
                        ->withInput(); // Retain the user's input
                }
            } else {
                // No geom provided, just update the other fields
                $lic->save();
            }

            return redirect('layer-info/low-income-communities')->with('success', __('Low Income Community updated successfully.'));
        } else {
            return redirect('layer-info/low-income-communities')->with('error', __('Failed to update Low Income Community.'));
        }
    }


    /**
     * Display details of a Low income community record.
     *
     * @param  int  $id The ID of the Low income community record to display
     * @return \Illuminate\Contracts\View\View
     */
    public function showData($id)
    {
        $lic = LowIncomeCommunity::find($id);
        if ($lic) {
            $page_title = __("Low Income Community Details");
            $geomArr = DB::select("SELECT ST_X(ST_AsText(ST_Centroid(ST_Centroid(geom)))) AS long, ST_Y(ST_AsText(ST_Centroid(ST_Centroid(geom)))) AS lat, ST_AsText(geom) AS geom FROM layer_info.low_income_communities WHERE id = $id");
            $geom = ($geomArr[0]->geom);
            $lat = $geomArr[0]->lat;
            $long = $geomArr[0]->long;
            return view('layer-info/low-income-communities.show', compact('page_title', 'lic', 'geom', 'lat', 'long'));
        } else {
            abort(404);
        }
    }

    /**
     * Export Low income community data to an Excel file.
     *
     * @param  array  $data The data containing search criteria
     * @return void
     */
    public function exportData($data)
    {
        $community_name = $data['community_name'] ?? null;
        $columns = $this->excelColumnDefinitions();
        $headers = SwmExcelColumns::exportHeaders($columns);
        $query = LowIncomeCommunity::query()->whereNull('deleted_at');
        if (! empty($community_name)) {
            $query->whereRaw('LOWER(community_name) LIKE ?', ['%'.strtolower($community_name).'%']);
        }

        (new SwmExcelExportWriter())->download(SwmExcelFilename::export('low_income_communities'), $headers, function ($sheet, $colLetter) use ($query, $columns) {
            $rowNum = 2;
            $query->orderBy('id')->chunk(5000, function ($lics) use ($sheet, $colLetter, $columns, &$rowNum) {
                foreach ($lics as $lic) {
                    $values = SwmExcelColumns::buildExportRow($columns, $lic, fn (string $key, $model) => $this->formatLicExportValue($key, $model));
                    foreach ($values as $index => $value) {
                        $sheet->setCellValue($colLetter($index + 1).$rowNum, $value);
                    }
                    $rowNum++;
                }
            });
        });
    }

    public function downloadTemplate(): void
    {
        (new SwmExcelTemplateWriter())->download(
            SwmExcelFilename::importTemplate('low_income_communities'),
            SwmExcelColumns::templateColumns($this->excelColumnDefinitions())
        );
    }

    /** @return array<int, string> */
    public function requiredImportLabels(): array
    {
        return SwmExcelColumns::requiredImportLabels($this->excelColumnDefinitions());
    }

    /** @return array<int, array{key: string, label?: string, required?: bool, dropdown?: array<int, string>}> */
    public function importTemplateColumns(): array
    {
        return SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions());
    }

    /** @return array<int, array{key: string, label?: string, required?: bool, dropdown?: array<int, string>}> */
    public function importColumnDefinitions(): array
    {
        return $this->importTemplateColumns();
    }

    /** @return array<int, array{key: string, label: string, required?: bool, import?: bool, export?: bool, dropdown?: array<int, string>}> */
    public function excelColumnDefinitions(): array
    {
        $yesNo = [__('Yes'), __('No')];
        $wards = array_map('strval', array_keys(Ward::getInAscOrder()));

        return [
            ['key' => 'community_name', 'label' => __('LIC Name'), 'required' => true],
            ['key' => 'representative_name', 'label' => __("Representative's Name")],
            ['key' => 'representative_contact_no', 'label' => __("Representative's Contact No.")],
            ['key' => 'sub_location', 'label' => __('Location')],
            ['key' => 'ward', 'label' => __('Ward No.'), 'dropdown' => $wards],
            ['key' => 'road_no', 'label' => __('Road No.')],
            ['key' => 'road_name', 'label' => __('Road Name')],
            ['key' => 'no_of_buildings', 'label' => __('No. of Buildings'), 'required' => true],
            ['key' => 'number_of_households', 'label' => __('No. of Households'), 'required' => true],
            ['key' => 'population_total', 'label' => __('Total Population'), 'required' => true],
            ['key' => 'population_male', 'label' => __('Male Population')],
            ['key' => 'population_female', 'label' => __('Female Population')],
            ['key' => 'population_others', 'label' => __('Other Population')],
            ['key' => 'water_connection_status', 'label' => __('Water Connection Status'), 'required' => true, 'dropdown' => $yesNo],
            ['key' => 'no_of_wate_points', 'label' => __('No. of Wate Points')],
            ['key' => 'sanitation_status', 'label' => __('Sanitation Status'), 'required' => true, 'dropdown' => $yesNo],
            ['key' => 'no_of_community_toilets', 'label' => __('No. of Community Toilets')],
            ['key' => 'no_of_septic_tank', 'label' => __('No. of Septic Tanks')],
            ['key' => 'no_of_holding_tank', 'label' => __('No. of Holding Tanks')],
            ['key' => 'no_of_pit', 'label' => __('No. of Pits')],
            ['key' => 'no_of_sewer_connection', 'label' => __('No. of Sewer Connections')],
            ['key' => 'area_decima', 'label' => __('Area (Decimal)')],
            ['key' => 'remarks', 'label' => __('Remarks')],
        ];
    }

    protected function formatLicExportValue(string $key, LowIncomeCommunity $lic): mixed
    {
        return match ($key) {
            'community_name' => $lic->community_name,
            'representative_name' => $lic->representative_name,
            'representative_contact_no' => $lic->representative_contact_no,
            'sub_location' => $lic->sub_location,
            'ward' => $lic->ward,
            'road_no' => $lic->road_no,
            'road_name' => $lic->road_name,
            'no_of_buildings' => $lic->no_of_buildings,
            'number_of_households' => $lic->number_of_households,
            'population_total' => $lic->population_total,
            'population_male' => $lic->population_male,
            'population_female' => $lic->population_female,
            'population_others' => $lic->population_others,
            'water_connection_status' => $lic->water_connection_status === null ? '' : ($lic->water_connection_status ? __('Yes') : __('No')),
            'no_of_wate_points' => $lic->no_of_wate_points,
            'sanitation_status' => $lic->sanitation_status === null ? '' : ($lic->sanitation_status ? __('Yes') : __('No')),
            'no_of_community_toilets' => $lic->no_of_community_toilets,
            'no_of_septic_tank' => $lic->no_of_septic_tank,
            'no_of_holding_tank' => $lic->no_of_holding_tank,
            'no_of_pit' => $lic->no_of_pit,
            'no_of_sewer_connection' => $lic->no_of_sewer_connection,
            'area_decima' => $lic->area_decima,
            'remarks' => $lic->remarks,
            default => '',
        };
    }

    /**
     * @param  array<string, mixed>  $row
     *
     * @throws \InvalidArgumentException
     */
    public function storeFromImportRow(array $row, int $userId): void
    {
        $communityName = trim((string) ($row['community_name'] ?? ''));
        if ($communityName === '') {
            throw new \InvalidArgumentException(__(':label is required.', [
                'label' => $this->importLabel('community_name'),
            ]));
        }

        $noOfBuildings = $this->parseRequiredNonNegativeInt($row['no_of_buildings'] ?? null, 'no_of_buildings');
        $populationTotal = $this->parseRequiredNonNegativeInt($row['population_total'] ?? null, 'population_total');
        $numberOfHouseholds = $this->parseRequiredNonNegativeInt($row['number_of_households'] ?? null, 'number_of_households');

        $waterConnectionStatus = SwmImportRowHelper::parseBoolean($row['water_connection_status'] ?? null);
        if ($waterConnectionStatus === null) {
            throw new \InvalidArgumentException(__(':label is required.', [
                'label' => $this->importLabel('water_connection_status'),
            ]));
        }

        $sanitationStatus = SwmImportRowHelper::parseBoolean($row['sanitation_status'] ?? null);
        if ($sanitationStatus === null) {
            throw new \InvalidArgumentException(__(':label is required.', [
                'label' => $this->importLabel('sanitation_status'),
            ]));
        }

        $noOfWaterPoints = null;
        if ($waterConnectionStatus) {
            $noOfWaterPoints = $this->parseRequiredNonNegativeInt($row['no_of_wate_points'] ?? null, 'no_of_wate_points');
        }

        $noOfCommunityToilets = null;
        if ($sanitationStatus) {
            $noOfCommunityToilets = $this->parseRequiredNonNegativeInt($row['no_of_community_toilets'] ?? null, 'no_of_community_toilets');
        }

        $lic = new LowIncomeCommunity();
        $lic->community_name = $communityName;
        $lic->sub_location = $this->nullableString($row['sub_location'] ?? null);
        $lic->ward = $this->parseOptionalPositiveInt($row['ward'] ?? null);
        $lic->road_no = $this->nullableString($row['road_no'] ?? null);
        $lic->road_name = $this->nullableString($row['road_name'] ?? null);
        $lic->area_decima = $this->parseOptionalDecimal($row['area_decima'] ?? null);
        $lic->representative_name = $this->nullableString($row['representative_name'] ?? null);
        $lic->representative_contact_no = $this->nullableString($row['representative_contact_no'] ?? null);
        $lic->no_of_buildings = $noOfBuildings;
        $lic->population_total = $populationTotal;
        $lic->number_of_households = $numberOfHouseholds;
        $lic->population_male = $this->parseOptionalNonNegativeInt($row['population_male'] ?? null);
        $lic->population_female = $this->parseOptionalNonNegativeInt($row['population_female'] ?? null);
        $lic->population_others = $this->parseOptionalNonNegativeInt($row['population_others'] ?? null);
        $lic->water_connection_status = $waterConnectionStatus;
        $lic->no_of_wate_points = $noOfWaterPoints;
        $lic->sanitation_status = $sanitationStatus;
        $lic->no_of_septic_tank = $this->parseOptionalNonNegativeInt($row['no_of_septic_tank'] ?? null);
        $lic->no_of_holding_tank = $this->parseOptionalNonNegativeInt($row['no_of_holding_tank'] ?? null);
        $lic->no_of_pit = $this->parseOptionalNonNegativeInt($row['no_of_pit'] ?? null);
        $lic->no_of_sewer_connection = $this->parseOptionalNonNegativeInt($row['no_of_sewer_connection'] ?? null);
        $lic->no_of_community_toilets = $noOfCommunityToilets;
        $lic->remarks = $this->nullableString($row['remarks'] ?? null);
        $lic->user_id = $userId;
        $lic->save();
    }

    private function nullableString($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }

    private function parseRequiredNonNegativeInt($value, string $field): int
    {
        $label = $this->importLabel($field);
        if ($value === null || $value === '') {
            throw new \InvalidArgumentException(__(':label is required.', ['label' => $label]));
        }
        if (! is_numeric($value) || (int) $value < 0) {
            throw new \InvalidArgumentException(__(':label must be a non-negative integer.', ['label' => $label]));
        }

        return (int) $value;
    }

    private function parseOptionalNonNegativeInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value) || (int) $value < 0) {
            throw new \InvalidArgumentException(__('Value must be a non-negative integer.'));
        }

        return (int) $value;
    }

    private function parseOptionalPositiveInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value) || (int) $value < 1) {
            throw new \InvalidArgumentException(__(':label must be a positive integer.', [
                'label' => $this->importLabel('ward'),
            ]));
        }

        return (int) $value;
    }

    private function parseOptionalDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value) || (float) $value < 0) {
            throw new \InvalidArgumentException(__(':label must be a non-negative number.', [
                'label' => $this->importLabel('area_decima'),
            ]));
        }

        return (float) $value;
    }

    /** @return array<string, string> */
    protected function formOnlyValidationLabels(): array
    {
        return [
            'geom' => __('Draw LIC Area on Map'),
        ];
    }

    private function importLabel(string $key): string
    {
        return SwmExcelColumns::labelFor($this->excelColumnDefinitions(), $key);
    }
}
