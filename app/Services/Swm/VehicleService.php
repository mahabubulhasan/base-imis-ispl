<?php

namespace App\Services\Swm;

use App\Models\LayerInfo\Ward;
use App\Models\Swm\Organization;
use App\Models\Swm\Vehicle;
use App\Models\Swm\VehicleType;
use App\Models\Swm\Worker;
use App\Models\Swm\WorkType;
use App\Services\Swm\Concerns\HasExcelColumnValidationLabels;
use App\Support\Swm\SwmExcelColumns;
use App\Support\ExcelDownload;
use App\Support\Swm\SwmExcelFilename;
use App\Support\Swm\SwmExcelTemplateWriter;
use App\Support\Swm\SwmImportTemplateOptions;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class VehicleService
{
    use HasExcelColumnValidationLabels;

    /**
     * @return array<string, string>
     */
    public static function operationalTypeLabels(): array
    {
        return [
            'day' => __('Day'),
            'night' => __('Night'),
            'mobile' => __('Mobile'),
            'other' => __('Others (specify)'),
        ];
    }

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
        $wtId = self::driverWorkTypeId();
        if (! $wtId) {
            return [];
        }

        $query = Worker::query()
            ->where('work_type_id', $wtId)
            ->whereNull('deleted_at');

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        return $query->orderBy('name')->pluck('name', 'id')->all();
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
            ->addColumn('operational_type_label', function ($model) {
                $labels = self::operationalTypeLabels();

                return $model->operational_type
                    ? ($labels[$model->operational_type] ?? $model->operational_type)
                    : '';
            })
            ->editColumn('status', function ($model) {
                return match ($model->status) {
                    'active' => __('Active'),
                    'inactive' => __('Inactive'),
                    default => (string) ($model->status ?? ''),
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

    /**
     * Next Vehicle ID for the organization (matches auto-generated pattern in swm.vehicle_id).
     */
    public function peekNextVehicleIdNo(int $organizationId): string
    {
        $width = max(1, (int) config('swm.vehicle_id.sequence_width', 5));
        $nextSeq = $this->maxVehicleIdSequenceForOrganization($organizationId) + 1;

        return $this->vehicleIdPrefix($organizationId).str_pad((string) $nextSeq, $width, '0', STR_PAD_LEFT);
    }

    protected function vehicleIdPrefix(int $organizationId): string
    {
        $format = (string) config('swm.vehicle_id.prefix_format', 'VHC-%d-');

        return sprintf($format, $organizationId);
    }

    protected function maxVehicleIdSequenceForOrganization(int $organizationId): int
    {
        $prefix = $this->vehicleIdPrefix($organizationId);
        $regex = '^'.preg_quote($prefix, '/').'[0-9]+$';

        $maxSeq = Vehicle::query()
            ->where('organization_id', $organizationId)
            ->whereNull('deleted_at')
            ->whereRaw('vehicle_id_no ~ ?', [$regex])
            ->selectRaw("COALESCE(MAX(CAST(SUBSTRING(vehicle_id_no FROM '[0-9]+$') AS INTEGER)), 0) AS max_seq")
            ->value('max_seq');

        return (int) $maxSeq;
    }

    protected function vehicleIdNoIsMissing(?string $vehicleIdNo): bool
    {
        return $vehicleIdNo === null || $vehicleIdNo === '';
    }

    protected function normalizedVehicleIdNoFromData(array $data): ?string
    {
        $raw = $data['vehicle_id_no'] ?? null;
        if ($raw === null) {
            return null;
        }
        $trimmed = trim((string) $raw);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function fillVehicleFromData(Vehicle $vehicle, array $data): void
    {
        $vehicle->organization_id = $data['organization_id'] ?? null;
        $vehicle->vehicle_type_id = $data['vehicle_type_id'] ?? null;
        $vehicle->vehicle_id_no = $this->normalizedVehicleIdNoFromData($data);
        $vehicle->vehicle_number = $data['vehicle_number'] ?? null;
        $vehicle->capacity = $data['capacity'] ?? null;
        $vehicle->driver_worker_id = $data['driver_worker_id'] ?? null;
        if (array_key_exists('service_area', $data)) {
            $vehicle->service_area = $data['service_area'];
        }
        $vehicle->service_wards = $data['service_wards'] ?? null;
        $vehicle->fuel_type = $data['fuel_type'] ?? null;
        $vehicle->operational_type = $data['operational_type'] ?? null;
        $vehicle->operational_type_other = $data['operational_type_other'] ?? null;
        $vehicle->engine_no = $data['engine_no'] ?? null;
        $vehicle->chassis_no = $data['chassis_no'] ?? null;
        $vehicle->status = $data['status'] ?? 'active';
        $vehicle->last_maintenance_year = $data['last_maintenance_year'] ?? null;
        $vehicle->remarks = $data['remarks'] ?? null;
        $vehicle->dumping_place_kind = $data['dumping_place_kind'] ?? null;
        $vehicle->dumping_sts_id = $data['dumping_sts_id'] ?? null;
        $vehicle->dumping_landfill_id = $data['dumping_landfill_id'] ?? null;
        $vehicle->dumping_place_other = $data['dumping_place_other'] ?? null;
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

        return DB::transaction(function () use ($vehicle, $data) {
            $this->fillVehicleFromData($vehicle, $data);
            $orgId = (int) ($vehicle->organization_id ?? 0);
            if ($orgId > 0) {
                Organization::query()->whereKey($orgId)->lockForUpdate()->first();
            }
            if ($orgId > 0 && $this->vehicleIdNoIsMissing($vehicle->vehicle_id_no)) {
                $vehicle->vehicle_id_no = $this->peekNextVehicleIdNo($orgId);
            }
            $vehicle->save();

            return $vehicle->id;
        });
    }

    public function download(array $data): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $vehicleNumber = $data['vehicle_number'] ?? null;
        $vehicleIdNo = $data['vehicle_id_no'] ?? null;
        $chassisNo = $data['chassis_no'] ?? null;
        $organizationId = $data['organization_id'] ?? null;
        $vehicleTypeId = $data['vehicle_type_id'] ?? null;
        $driverWorkerId = $data['driver_worker_id'] ?? null;

        $columns = $this->excelColumnDefinitions();
        $headers = SwmExcelColumns::exportHeaders($columns);

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

        $wardLabels = Ward::getInAscOrder();
        $operationalTypeLabels = self::operationalTypeLabels();

        return ExcelDownload::xlsx(
            SwmExcelFilename::export('vehicles'),
            function ($writer) use ($headers, $style, $query, $columns, $wardLabels, $operationalTypeLabels) {
                $writer->addRowWithStyle($headers, $style);

                $query->orderBy('swm.vehicles.id')->chunk(5000, function ($rows) use ($writer, $columns, $wardLabels, $operationalTypeLabels) {
                    foreach ($rows as $row) {
                        $values = SwmExcelColumns::buildExportRow(
                            $columns,
                            $row,
                            fn (string $key, $model) => $this->formatVehicleExportValue($key, $model, $wardLabels, $operationalTypeLabels)
                        );
                        $writer->addRow($values);
                    }
                });
            }
        );
    }

    public function downloadTemplate(): void
    {
        (new SwmExcelTemplateWriter())->download(
            SwmExcelFilename::importTemplate('vehicles'),
            SwmExcelColumns::templateColumns($this->excelColumnDefinitions())
        );
    }

    /** @return array<int, array{key: string, label: string, required?: bool, dropdown?: array<int, string>, import?: bool, export?: bool, multiselect?: bool, reference_key?: string}> */
    public function excelColumnDefinitions(): array
    {
        $scopedOrgId = Auth::user()?->swm_organization_id;
        $organizationColumn = null;

        if (! $scopedOrgId) {
            $orgNames = Organization::query()
                ->whereNull('deleted_at')
                ->operational()
                ->orderBy('name')
                ->pluck('name')
                ->all();
            $organizationColumn = ['key' => 'organization', 'label' => __('Organization'), 'required' => true, 'dropdown' => $orgNames];
        }

        $vehicleTypes = VehicleType::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $driverOrgId = $scopedOrgId ? (int) $scopedOrgId : null;
        $driverNames = array_values($this->driverWorkersForOrganization($driverOrgId));

        $operationalLabels = array_values(self::operationalTypeLabels());
        $dumpingKinds = [__('STS'), __('Landfill'), __('Others (specify)')];
        $stsLabels = SwmImportTemplateOptions::stsLabels();
        $landfillLabels = SwmImportTemplateOptions::landfillLabels();

        $columns = [
            ['key' => 'vehicle_id_no', 'label' => __('Vehicle ID'), 'import' => false, 'template' => false, 'derived' => true],
            ['key' => 'vehicle_type', 'label' => __('Vehicle Type'), 'required' => true, 'dropdown' => $vehicleTypes],
            ['key' => 'vehicle_number', 'label' => __('Vehicle No.'), 'required' => true],
            ['key' => 'capacity', 'label' => __('Capacity').' ('.__('Ton').')'],
        ];

        if ($organizationColumn !== null) {
            $columns[] = $organizationColumn;
        } elseif ($scopedOrgId) {
            $columns[] = [
                'key' => 'organization_name',
                'label' => __('Organization'),
                'import' => false,
                'template' => true,
                'derived' => true,
            ];
        }

        return array_merge($columns, [
            ['key' => 'driver', 'label' => __('Driver Name'), 'required' => true, 'dropdown' => $driverNames],
            [
                'key' => 'service_wards',
                'label' => __('Service Wards'),
                'multiselect' => true,
                'dropdown' => SwmImportTemplateOptions::wardNumberStrings(),
                'reference_key' => 'service_wards',
            ],
            ['key' => 'dumping_place_kind', 'label' => __('Dumping Place Type'), 'required' => true, 'dropdown' => $dumpingKinds],
            ['key' => 'dumping_sts_id', 'label' => __('Dumping Place Name (STS)'), 'dropdown' => $stsLabels],
            ['key' => 'dumping_landfill_id', 'label' => __('Dumping Place Name (Landfill)'), 'dropdown' => $landfillLabels],
            ['key' => 'dumping_place_other', 'label' => __('Specify Dumping Place')],
            ['key' => 'fuel_type', 'label' => __('Fuel Type')],
            ['key' => 'operational_type', 'label' => __('Operational Type'), 'dropdown' => $operationalLabels],
            ['key' => 'operational_type_other', 'label' => __('Specify Operational Type')],
            ['key' => 'engine_no', 'label' => __('Engine No.')],
            ['key' => 'chassis_no', 'label' => __('Chassis No.')],
            ['key' => 'status', 'label' => __('Status'), 'dropdown' => ['active', 'inactive']],
            ['key' => 'last_maintenance_year', 'label' => __('Last Maintenance Year')],
            ['key' => 'remarks', 'label' => __('Remarks')],
        ]);
    }

    /** @return array<int, array{key: string, label: string, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}> */
    public function importTemplateColumns(): array
    {
        return SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions());
    }

    /** @return array<int, array{key: string, label: string, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}> */
    public function importColumnDefinitions(): array
    {
        return $this->importTemplateColumns();
    }

    protected function formatVehicleExportValue(string $key, Vehicle $row, array $wardLabels, array $operationalTypeLabels): mixed
    {
        return match ($key) {
            'organization' => $row->organization_name,
            'organization_name' => $row->organization_name,
            'vehicle_type' => $row->vehicle_type_name,
            'vehicle_id_no' => $row->vehicle_id_no,
            'vehicle_number' => $row->vehicle_number,
            'capacity' => $row->capacity,
            'driver' => $row->driver_name,
            'service_wards' => collect($row->service_wards ?? [])
                ->map(fn ($wardId) => $wardLabels[$wardId] ?? $wardId)
                ->implode(', '),
            'dumping_place_kind' => match ($row->dumping_place_kind) {
                'sts' => __('STS'),
                'landfill' => __('Landfill'),
                'other' => __('Others (specify)'),
                default => '',
            },
            'dumping_sts_id' => $row->dumping_sts_name,
            'dumping_landfill_id' => $row->dumping_landfill_name,
            'dumping_place_other' => $row->dumping_place_other,
            'fuel_type' => $row->fuel_type,
            'operational_type' => $row->operational_type
                ? ($operationalTypeLabels[$row->operational_type] ?? $row->operational_type)
                : '',
            'operational_type_other' => $row->operational_type_other,
            'engine_no' => $row->engine_no,
            'chassis_no' => $row->chassis_no,
            'status' => $row->status,
            'last_maintenance_year' => $row->last_maintenance_year,
            'remarks' => $row->remarks,
            default => '',
        };
    }

    /** @return array<int, string> */
    public function requiredImportLabels(): array
    {
        return SwmExcelColumns::requiredImportLabels($this->importTemplateColumns());
    }

    /** @return array<string, string> */
    protected function formOnlyValidationLabels(): array
    {
        return [
            'vehicle_type_id' => __('Vehicle Type'),
            'organization_id' => __('Organization'),
            'driver_worker_id' => __('Driver Name'),
        ];
    }
}
