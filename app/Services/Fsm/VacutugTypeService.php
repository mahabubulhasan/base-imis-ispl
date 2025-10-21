<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Services\Fsm;

use App\Models\Fsm\VacutugType;
use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use DB;
use Carbon\Carbon;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Yajra\DataTables\DataTables;
use App\Enums\VacutugStatus;
use App\Enums\VacutugComplyMaintainStandard;

class VacutugTypeService {

    protected $session;
    protected $instance;

    /**
     * Constructs a new VacutugType object.
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
     * Get all the All Employee Info.
     *
     *
     * @return AllData[]|Collection
     */
    public function getAllVacutugTypes($data)
    {
        if(Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk'))
        {
            $vacutugtypes =  VacutugType::select('*')->where('service_provider_id',Auth::user()->service_provider_id)->whereNull('deleted_at');
        }
        else
        {
            $vacutugtypes =  VacutugType::select('*')->whereNull('deleted_at');
        }
     
        return Datatables::of($vacutugtypes)
                ->filter(function ($query) use ($data) {
                if ($data['service_provider_id']) {
                    $query->where('fsm.desludging_vehicles.service_provider_id',$data['service_provider_id']);
                }
                if ($data['license_plate_number']) {
                    $query->where('fsm.desludging_vehicles.license_plate_number',$data['license_plate_number']);
                }
                if ($data['capacity']) {
                    $query->where('fsm.desludging_vehicles.capacity', $data['capacity']);
                }
                if ($data['width']) {
                    $query->where('fsm.desludging_vehicles.width', $data['width']);
                }
                 if ($data['status']) {
                    $query->where('status', $data['status']);
                }
                })
                ->addColumn('action', function ($model) {
                    $content = \Form::open(['method' => 'DELETE', 'route' => ['desludging-vehicles.destroy', $model->id]]);

                    if (Auth::user()->can('Edit Desludging Vehicle')) {
                        $content .= '<a title="' . __("Edit") . '" href="' . action("Fsm\VacutugTypeController@edit", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                    }
                    if (Auth::user()->can('View Desludging Vehicle')) {
                        $content .= '<a title="' . __("Detail") . '" href="' . action("Fsm\VacutugTypeController@show", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                    }

                    if (Auth::user()->can('View Desludging Vehicle History')) {
                        $content .= '<a title="' . __("History") . '"  href="' . action("Fsm\VacutugTypeController@history", [$model->id]) . '" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                    }

                    if (Auth::user()->can('Delete Desludging Vehicle')) {
                        $content .= '<a href title="' . __("Delete") . '" class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                    }

                    $content .= \Form::close();
                    return $content;
                })
                ->editColumn('service_provider_id',function ($model){
                     $service_provider = \App\Models\Fsm\ServiceProvider::withTrashed()
                        ->where('id', $model->service_provider_id)
                        ->first();
                        return $service_provider->company_name??'-';
                })
                ->editColumn('status',function ($model){
                 return VacutugStatus::getDescription($model->status);
                })
                ->make(true);
    }
    /**
     * Store or update a newly created resource in storage.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function storeOrUpdate($id,$data)
    {
        if(is_null($id)){
            $vacutugType = new VacutugType();
            $vacutugType->license_plate_number = $data['license_plate_number'] ? $data['license_plate_number'] : null;
            $vacutugType->capacity = $data['capacity'] ? $data['capacity'] : null;
            $vacutugType->width = $data['width'] ? $data['width'] : null;
            $vacutugType->description = $data['description'] ? $data['description'] : null;
            $vacutugType->service_provider_id = $data['service_provider_id'] ? $data['service_provider_id'] : null;
            $vacutugType->comply_with_maintainance_standards = $data['comply_with_maintainance_standards'] ? $data['comply_with_maintainance_standards'] : 0;
            $vacutugType->status = $data['status'] ? $data['status'] : 0;
            $vacutugType->save();
        }
        else{
            $vacutugType = VacutugType::find($id);
            $vacutugType->license_plate_number = $data['license_plate_number'] ? $data['license_plate_number'] : null;
            $vacutugType->capacity = $data['capacity'] ? $data['capacity'] : null;
            $vacutugType->width = $data['width'] ? $data['width'] : null;
            $vacutugType->description = $data['description'] ? $data['description'] : null;
            $vacutugType->service_provider_id = $data['service_provider_id'] ? $data['service_provider_id'] : null;
            $vacutugType->comply_with_maintainance_standards = $data['comply_with_maintainance_standards'] ? $data['comply_with_maintainance_standards'] : 0;
            $vacutugType->status = $data['status'] ? $data['status'] : 0;
            $vacutugType->save();
        }
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
        $service_provider_id = $data['service_provider_id'] ? $data['service_provider_id'] : null;
        $license_plate_number = $data['license_plate_number'] ? $data['license_plate_number'] : null;
        $capacity = $data['capacity'] ? $data['capacity'] : null;
        $width = $data['width'] ? $data['width'] : null;
        $columns = [
            __('Service Provider'),
            __('Vehicle License Plate Number'),
            __('Capacity (m³)'),
            __('Width (m)'),
            __('Comply with Maintenance Standards'),
            __('Status')
        ];
        
        $status = $data['status'] ? $data['status'] : 0;
        $query =  VacutugType::select('*')->with('serviceProvider')->whereNull('deleted_at');
        
        if(Auth::user()->hasRole('Service Provider - Admin') )
        {
        $query->where('service_provider_id',"=",Auth::user()->service_provider_id);
        }

        if(!empty($service_provider_id)){
            $query->where('fsm.desludging_vehicles.service_provider_id', $service_provider_id);
        }

        if(!empty($license_plate_number)){
            $query->whereRaw('LOWER(fsm.desludging_vehicles.license_plate_number) LIKE ? ', [trim(strtolower($license_plate_number))]);
        }

        if(!empty($capacity)){
            $query->where('fsm.desludging_vehicles.capacity', $capacity);
        }

        if(!empty($width)){
            $query->where('fsm.desludging_vehicles.width', $width);
        }
        if(!empty($status)){
            $query -> where('status', $status);
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Desludging Vehicles.csv')
            ->addRowWithStyle($columns, $style); //Top row of excel

        $query->chunk(5000, function ($vacutugTypeList) use ($writer) {
            foreach($vacutugTypeList as $data) {
                $values = [];
                $values[] = $data->serviceProvider->company_name;
                $values[] = $data->license_plate_number;
                $values[] = $data->capacity;
                $values[] = $data->width;
                $values[] = VacutugComplyMaintainStandard::getDescription($data->comply_with_maintainance_standards);
                $values[] = VacutugStatus::getDescription($data->status);
                $writer->addRow($values);
            }
        });
        $writer->close();

    }

}
