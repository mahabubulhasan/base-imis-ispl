<?php

namespace App\Services\Swm;

use App\Models\Swm\Vehicle;
use App\Models\Swm\Worker;
use App\Models\Swm\WorkType;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\DataTables;

class VehicleService
{
    public static function driverWorkTypeId(): ?int
    {
        $name = (string) config('swm.driver_work_type_name', 'Driver');

        return WorkType::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->whereNull('deleted_at')
            ->value('id');
    }

    /**
     * @return array<int, string> id => worker name
     */
    public function driverWorkersForOrganization(?int $organizationId): array
    {
        if (! $organizationId) {
            return [];
        }
        $wtId = self::driverWorkTypeId();
        if (! $wtId) {
            return [];
        }

        return Worker::query()
            ->where('organization_id', $organizationId)
            ->where('work_type_id', $wtId)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function baseQuery(): Builder
    {
        $query = Vehicle::query()
            ->select('swm.vehicles.*')
            ->leftJoin('swm.organizations as swm_org', 'swm.vehicles.organization_id', '=', 'swm_org.id')
            ->leftJoin('swm.vehicle_types as swm_vt', 'swm.vehicles.vehicle_type_id', '=', 'swm_vt.id')
            ->leftJoin('swm.workers as swm_driver', 'swm.vehicles.driver_worker_id', '=', 'swm_driver.id')
            ->leftJoin('swm.sts as swm_ds', 'swm.vehicles.dumping_sts_id', '=', 'swm_ds.id')
            ->leftJoin('swm.landfills as swm_dl', 'swm.vehicles.dumping_landfill_id', '=', 'swm_dl.id')
            ->addSelect([
                'swm_org.name as organization_name',
                'swm_vt.name as vehicle_type_name',
                'swm_driver.name as driver_name',
                'swm_ds.name as dumping_sts_name',
                'swm_dl.name as dumping_landfill_name',
            ])
            ->whereNull('swm.vehicles.deleted_at');

        $orgId = Auth::user()?->swm_organization_id;
        if ($orgId) {
            $query->where('swm.vehicles.organization_id', $orgId);
        }

        return $query;
    }

    public function getAllVehicles(array $data)
    {
        $query = $this->baseQuery();

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['vehicle_number'] ?? null)) {
                    $q->where('swm.vehicles.vehicle_number', 'ILIKE', '%'.trim((string) $data['vehicle_number']).'%');
                }
                if (! empty($data['vehicle_id_no'] ?? null)) {
                    $q->where('swm.vehicles.vehicle_id_no', 'ILIKE', '%'.trim((string) $data['vehicle_id_no']).'%');
                }
                if (! empty($data['chassis_no'] ?? null)) {
                    $q->where('swm.vehicles.chassis_no', 'ILIKE', '%'.trim((string) $data['chassis_no']).'%');
                }
                if (! empty($data['organization_id'] ?? null)) {
                    $q->where('swm.vehicles.organization_id', $data['organization_id']);
                }
                if (! empty($data['vehicle_type_id'] ?? null)) {
                    $q->where('swm.vehicles.vehicle_type_id', $data['vehicle_type_id']);
                }
                if (! empty($data['driver_worker_id'] ?? null)) {
                    $q->where('swm.vehicles.driver_worker_id', $data['driver_worker_id']);
                }
            })
            ->orderColumn('organization_name', 'swm_org.name $1')
            ->orderColumn('vehicle_type_name', 'swm_vt.name $1')
            ->orderColumn('driver_name', 'swm_driver.name $1')
            ->addColumn('dumping_place', function ($model) {
                return match ($model->dumping_place_kind) {
                    'sts' => (string) ($model->dumping_sts_name ?? ''),
                    'landfill' => (string) ($model->dumping_landfill_name ?? ''),
                    'other' => (string) ($model->dumping_place_other ?? ''),
                    default => '',
                };
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.vehicles.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Vehicle')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\VehicleController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Vehicle')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\VehicleController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Vehicle History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\VehicleController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Vehicle')) {
                    $content .= '<a href="#" title="'.__('Delete').'" class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }

                $content .= \Form::close();

                return $content;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public static function normalizeDumpingFields(array $data): array
    {
        $kind = $data['dumping_place_kind'] ?? null;
        if ($kind === 'sts') {
            $data['dumping_landfill_id'] = null;
            $data['dumping_place_other'] = null;
        } elseif ($kind === 'landfill') {
            $data['dumping_sts_id'] = null;
            $data['dumping_place_other'] = null;
        } elseif ($kind === 'other') {
            $data['dumping_sts_id'] = null;
            $data['dumping_landfill_id'] = null;
        }

        return $data;
    }

    public function storeOrUpdate(?int $id, array $data): ?int
    {
        $data = self::normalizeDumpingFields($data);

        if (is_null($id)) {
            $vehicle = new Vehicle();
        } else {
            $vehicle = Vehicle::find($id);
            if (! $vehicle) {
                return null;
            }
        }

        $vehicle->organization_id = $data['organization_id'] ?? null;
        $vehicle->vehicle_type_id = $data['vehicle_type_id'] ?? null;
        $vehicle->vehicle_id_no = $data['vehicle_id_no'] ?? null;
        $vehicle->vehicle_number = $data['vehicle_number'] ?? null;
        $vehicle->capacity = $data['capacity'] ?? null;
        $vehicle->driver_worker_id = $data['driver_worker_id'] ?? null;
        $vehicle->service_area = $data['service_area'] ?? null;
        $vehicle->fuel_type = $data['fuel_type'] ?? null;
        $vehicle->operational_type = $data['operational_type'] ?? null;
        $vehicle->vehicle_registration_no = $data['vehicle_registration_no'] ?? null;
        $vehicle->engine_no = $data['engine_no'] ?? null;
        $vehicle->chassis_no = $data['chassis_no'] ?? null;
        $vehicle->status = $data['status'] ?? 'active';
        $vehicle->last_maintenance_year = $data['last_maintenance_year'] ?? null;
        $vehicle->remarks = $data['remarks'] ?? null;
        $vehicle->dumping_place_kind = $data['dumping_place_kind'] ?? null;
        $vehicle->dumping_sts_id = $data['dumping_sts_id'] ?? null;
        $vehicle->dumping_landfill_id = $data['dumping_landfill_id'] ?? null;
        $vehicle->dumping_place_other = $data['dumping_place_other'] ?? null;
        $vehicle->save();

        return $vehicle->id;
    }

    public function download(array $data): void
    {
        $vehicleNumber = $data['vehicle_number'] ?? null;
        $vehicleIdNo = $data['vehicle_id_no'] ?? null;
        $chassisNo = $data['chassis_no'] ?? null;
        $organizationId = $data['organization_id'] ?? null;
        $vehicleTypeId = $data['vehicle_type_id'] ?? null;
        $driverWorkerId = $data['driver_worker_id'] ?? null;

        $columns = [
            __('Vehicle ID'),
            __('Vehicle Number'),
            __('Chassis No.'),
            __('Organization'),
            __('Vehicle Type'),
            __('Capacity'),
            __('Driver'),
            __('Service Area'),
            __('Fuel Type'),
            __('Operational Type'),
            __('Vehicle Registration No.'),
            __('Engine No.'),
            __('Status'),
            __('Last Maintenance (Year)'),
            __('Remarks'),
            __('Dumping Place'),
        ];

        $query = $this->baseQuery();

        if (! empty($vehicleNumber)) {
            $query->where('swm.vehicles.vehicle_number', 'ILIKE', '%'.trim((string) $vehicleNumber).'%');
        }
        if (! empty($vehicleIdNo)) {
            $query->where('swm.vehicles.vehicle_id_no', 'ILIKE', '%'.trim((string) $vehicleIdNo).'%');
        }
        if (! empty($chassisNo)) {
            $query->where('swm.vehicles.chassis_no', 'ILIKE', '%'.trim((string) $chassisNo).'%');
        }
        if (! empty($organizationId)) {
            $query->where('swm.vehicles.organization_id', $organizationId);
        }
        if (! empty($vehicleTypeId)) {
            $query->where('swm.vehicles.vehicle_type_id', $vehicleTypeId);
        }
        if (! empty($driverWorkerId)) {
            $query->where('swm.vehicles.driver_worker_id', $driverWorkerId);
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('SW Vehicles.csv')
            ->addRowWithStyle($columns, $style);

        $query->orderBy('swm.vehicles.id')->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $dumping = match ($row->dumping_place_kind) {
                    'sts' => (string) ($row->dumping_sts_name ?? ''),
                    'landfill' => (string) ($row->dumping_landfill_name ?? ''),
                    'other' => (string) ($row->dumping_place_other ?? ''),
                    default => '',
                };
                $writer->addRow([
                    $row->vehicle_id_no,
                    $row->vehicle_number,
                    $row->chassis_no,
                    $row->organization_name,
                    $row->vehicle_type_name,
                    $row->capacity,
                    $row->driver_name,
                    $row->service_area,
                    $row->fuel_type,
                    $row->operational_type,
                    $row->vehicle_registration_no,
                    $row->engine_no,
                    $row->status,
                    $row->last_maintenance_year,
                    $row->remarks,
                    $dumping,
                ]);
            }
        });

        $writer->close();
    }
}
