<?php
// Last Modified: 2026-04-30
// Developed By: Streams Tech Ltd.
// Description: Handles drain network data operations with road-based code generation.

namespace App\Services\UtilityInfo;

use App\Models\UtilityInfo\Drain;
use App\Models\Fsm\TreatmentPlant;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use DB;
use Carbon\Carbon;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Yajra\DataTables\DataTables;
use Illuminate\Database\QueryException;

class DrainService {

    protected $session;
    protected $instance;

    /**
     * Constructs a new Drain object.
     *
     *
     */
    public function __construct()
    {
        /*Session code
        ....
         here*/


    }

    /**
     * Get all list of resource.
     *
     *
     * @return array[]|Collection
     */
    public function getAllData($data)
    {
        $drainData = Drain::select('utility_info.drains.*', 'utility_info.roads.ward')
            ->leftJoin('utility_info.roads', 'utility_info.drains.road_code', '=', 'utility_info.roads.code');
        return Datatables::of($drainData)
                ->filter(function ($query) use ($data) {
                if ($data['code']) {
                    $query->where('code', 'ILIKE', '%' .  $data['code'] . '%');
                }

                if ($data['cover_type']) {
                    $query->whereRaw('LOWER(cover_type) LIKE ? ', [trim(strtolower($data['cover_type']))]);
                }

            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['drains.destroy', $model->code]]);

                if (Auth::user()->can('Edit Drain')) {
                    $content .= '<a title="' . __("Edit") . '" href="' . action("UtilityInfo\DrainController@edit", [$model->code]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View Drain')) {
                    $content .= '<a title="' . __("Detail") . '" href="' . action("UtilityInfo\DrainController@show", [$model->code]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View Drain History')) {
                    $content .= '<a title="' . __("History") . '" href="' . action("UtilityInfo\DrainController@history", [$model->code]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete Drain')) {
                    $content .= '<a href="#" title="' . __("Delete") . '"  class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }

                if (Auth::user()->can('View Drain On Map')) {
                    $content .= '<a title="' . __("Map") . '" href="' . action("MapsController@index", ['layer' => 'drains_layer', 'field' => 'code', 'val' => $model->code]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-map-marker"></i></a> ';
                }

                $content .= \Form::close();
                return $content;
            })
            ->editColumn('treatment_plant_id', function ($model) {
                $treatmentPlant = TreatmentPlant::select('name')
                    ->where('id', $model->treatment_plant_id)
                    ->first();
                if ($treatmentPlant) {
                    return $treatmentPlant->name;
                }
                return null; // or any default value you prefer if treatment plant not found
            })
            ->make(true);
    }
    /**
     * Store or update a newly created resource in storage.
     *
     * @param string|null $code
     * @param array $data
     * @return bool
     */
    public function storeOrUpdate($code = null,$data)
    {
        if(empty($code)){
            // Validate road_code exists
            if (empty($data['road_code'])) {
                \Log::error('Road code is required for drain creation');
                throw new \Exception('Road code is required to generate drain code.');
            }

            DB::beginTransaction();
            try {
                $generatedDrainCode = $this->generateDrainCode($data['road_code']);
                if (!empty($data['drain_code']) && (string)$data['drain_code'] !== (string)$generatedDrainCode) {
                    throw new \Exception('Submitted drain code is invalid. Please regenerate and try again.');
                }

                $drainCode = !empty($data['drain_code']) ? $data['drain_code'] : $generatedDrainCode;

                $drain = new Drain();
                $drain->code = $drainCode;
                $drain->user_id = Auth::id();
                $drain->road_code = $data['road_code'];
                $drain->surface_type = $data['surface_type'] ? $data['surface_type'] : null;
                $drain->cover_type = $data['cover_type'] ? $data['cover_type'] : null;
                $drain->treatment_plant_id = $data['treatment_plant_id'] ? $data['treatment_plant_id'] : null;
                $drain->size = $data['size'] ? $data['size'] : null;
                $drain->length = $data['length'] ? $data['length'] : null;
                $drain->geom = $data['geom'] ? DB::raw("ST_Multi(ST_GeomFromText('" . $data['geom'] . "', 4326))") : null;

                $drain->save();

                DB::commit();

                return true;
            } catch (QueryException $e) {
                DB::rollback();

                // Handle PostgreSQL unique constraint or duplicate key violations
                if ($e->getCode() === '23505') {
                    \Log::warning('Duplicate key constraint violation', [
                        'road_code' => $data['road_code'],
                        'error' => $e->getMessage()
                    ]);
                    throw new \Exception('A drain with this code already exists. Please try again.');
                } else {
                    \Log::error('Database error occurred', [
                        'road_code' => $data['road_code'],
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Error creating drain', [
                    'road_code' => $data['road_code'],
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
        else{
            $drain = Drain::find($code);
            $drain->user_id = Auth::id();
            $drain->surface_type = $data['surface_type'] ? $data['surface_type'] : null;
            $drain->treatment_plant_id = $data['treatment_plant_id'] ? $data['treatment_plant_id'] : null;
            $drain->cover_type = $data['cover_type'] ? $data['cover_type'] : null;
            $drain->size = $data['size'] ? $data['size'] : null;
            $drain->length = $data['length'] ? $data['length'] : null;

            $drain->save();

            return true;
        }
    }

    /**
     * Generate next drain code for a given road code.
     *
     * @param string $roadCode
     * @return string
     */
    public function generateDrainCode(string $roadCode): string
    {
        if (empty($roadCode)) {
            \Log::error('Road code is required for drain code generation');
            throw new \Exception('Road code is required to generate drain code.');
        }

        if (strlen($roadCode) >= 8 && substr($roadCode, 6, 2) === '10') {
            $baseCode = substr($roadCode, 0, 6) . '20' . substr($roadCode, 8);
        } else {
            $baseCode = 'D-' . $roadCode;
        }

        $maxDrainCode = Drain::withTrashed()
            ->where('code', 'LIKE', $baseCode . '-%')
            ->max('code');

        $suffix = 0;
        if ($maxDrainCode) {
            $lastSuffix = substr($maxDrainCode, -2);
            $suffix = intval($lastSuffix) + 1;
        }

        if ($suffix > 99) {
            throw new \Exception('Maximum number of drains (100) reached for this road.');
        }

        return $baseCode . '-' . sprintf('%02d', $suffix);
    }

    /**
     * Download a listing of the specified resource from storage.
     *
     * @param array $data
     * @return null
     */
    public function download($data)
    {

        $searchData = $data['searchData'] ? $data['searchData'] : null;
        $code = $data['code'] ? $data['code'] : null;
        $cover_type = $data['cover_type'] ? $data['cover_type'] : null;
        $columns = [
            __('Code'),
            __('Road Code'),
            __('Cover Type'),
            __('Surface Type'),
            __('Width (mm)'),
            __('Length (m)'),
            __('Treatment Plant'),
        ];
        $query = Drain::select('drains.code', 'drains.road_code', 'drains.cover_type', 'drains.surface_type', 'drains.size', 'drains.length', 'fsm.treatment_plants.name as Treatment Plant')
        ->leftJoin('fsm.treatment_plants', 'drains.treatment_plant_id', '=', 'fsm.treatment_plants.id')
        ->whereNull('drains.deleted_at');
        if (!empty($code)) {
            $query->where('code','ILIKE', '%'. $code .'%');

        }

        if (!empty($cover_type)) {
            $query->where('cover_type', $cover_type);
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Drain Network.csv')
            ->addRowWithStyle($columns, $style); //Top row of excel

        $query->chunk(5000, function ($drains) use ($writer) {
            $writer->addRows($drains->toArray());
        });

        $writer->close();

    }

}
