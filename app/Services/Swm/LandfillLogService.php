<?php

namespace App\Services\Swm;

use App\Models\Swm\Landfill;
use App\Models\Swm\LandfillLog;
use App\Models\Swm\Vehicle;
use App\Models\Swm\WasteType;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class LandfillLogService
{
    public function landfillLogQuery(): Builder
    {
        $query = LandfillLog::query()
            ->whereNull('deleted_at')
            ->with(['organization', 'vehicle', 'landfill', 'wasteType']);

        $orgId = Auth::user()?->swm_organization_id;
        if ($orgId) {
            $query->where('organization_id', (int) $orgId);
        }

        return $query;
    }

    public function getAllLandfillLogs(array $data)
    {
        $query = $this->landfillLogQuery();

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                $this->applyFilters($q, $data);
            })
            ->addColumn('organization_name', function (LandfillLog $model) {
                return $model->organization?->name ?? '';
            })
            ->addColumn('vehicle_number', function (LandfillLog $model) {
                return $model->vehicle?->vehicle_number ?? '';
            })
            ->addColumn('landfill_label', function (LandfillLog $model) {
                return $model->landfill_name ?: ($model->landfill?->name ?? '');
            })
            ->addColumn('waste_type_label', function (LandfillLog $model) {
                return $model->waste_type_name ?: ($model->wasteType?->name ?? '');
            })
            ->addColumn('source_sts_label', function (LandfillLog $model) {
                if (! is_array($model->source_sts_ids) || empty($model->source_sts_ids)) {
                    return '';
                }
                $names = \App\Models\Swm\Sts::query()
                    ->whereIn('id', $model->source_sts_ids)
                    ->whereNull('deleted_at')
                    ->orderBy('name')
                    ->pluck('name')
                    ->all();

                return implode(', ', $names);
            })
            ->addColumn('source_wards_label', function (LandfillLog $model) {
                return is_array($model->source_wards) ? implode(', ', $model->source_wards) : '';
            })
            ->addColumn('effective_quantity_ton', function (LandfillLog $model) {
                $effective = $model->weighbridge_weight_ton ?? $model->quantity_ton;

                return $effective !== null ? (string) $effective : '';
            })
            ->editColumn('entry_at', function (LandfillLog $model) {
                return $model->entry_at?->format('Y-m-d H:i') ?? '';
            })
            ->editColumn('operation_date', function (LandfillLog $model) {
                return $model->operation_date?->format('Y-m-d') ?? '';
            })
            ->editColumn('operation_status', function (LandfillLog $model) {
                return LandfillLog::statusOptions()[$model->operation_status] ?? $model->operation_status;
            })
            ->orderColumn('organization_name', function ($query, $order) {
                $query->orderBy('organization_id', $order);
            })
            ->addColumn('action', function (LandfillLog $model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.landfill-logs.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Landfill Log')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\LandfillLogController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Landfill Log')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\LandfillLogController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Landfill Log History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\LandfillLogController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Landfill Log')) {
                    $content .= '<a href="#" title="'.__('Delete').'" class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }

                $content .= \Form::close();

                return $content;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function storeOrUpdate(?int $id, array $data): ?int
    {
        $vehicle = Vehicle::query()
            ->whereNull('deleted_at')
            ->whereKey($data['vehicle_id'])
            ->where('organization_id', $data['organization_id'])
            ->with(['vehicleType', 'driver', 'dumpingLandfill'])
            ->first();

        if (! $vehicle) {
            return null;
        }

        if (is_null($id)) {
            $log = new LandfillLog();
        } else {
            $log = LandfillLog::query()->find($id);
            if (! $log) {
                return null;
            }
        }

        DB::transaction(function () use ($log, $data, $vehicle): void {
            $log->organization_id = (int) $data['organization_id'];
            $log->vehicle_id = (int) $data['vehicle_id'];
            $log->entry_at = Carbon::parse($data['entry_at']);
            $log->operation_date = Carbon::parse($data['operation_date'])->toDateString();
            $log->operation_status = $data['operation_status'];
            $log->quantity_ton = $data['quantity_ton'] ?? null;
            $log->weighbridge_weight_ton = $data['weighbridge_weight_ton'] ?? null;
            $log->source_sts_ids = $data['source_sts_ids'] ?? null;
            $log->source_wards = $data['source_wards'] ?? null;
            $log->remarks = $data['remarks'] ?? null;

            $log->vehicle_type_id = ! empty($data['vehicle_type_id']) ? (int) $data['vehicle_type_id'] : $vehicle->vehicle_type_id;
            $log->vehicle_type_name = ! empty($data['vehicle_type_name'])
                ? $data['vehicle_type_name']
                : ($vehicle->vehicleType?->name);

            $log->driver_worker_id = $vehicle->driver_worker_id;
            $log->driver_name = ! empty($data['driver_name'])
                ? $data['driver_name']
                : ($vehicle->driver?->name);

            if (! empty($data['landfill_id'])) {
                $log->landfill_id = (int) $data['landfill_id'];
                $landfill = Landfill::query()->whereNull('deleted_at')->find((int) $data['landfill_id']);
                $log->landfill_name = ! empty($data['landfill_name']) ? $data['landfill_name'] : ($landfill?->name);
                if (empty($data['waste_type_id']) && $landfill) {
                    $first = $landfill->wasteTypes()->first();
                    if ($first) {
                        $log->waste_type_id = $first->id;
                    }
                }
            } else {
                $log->landfill_id = $vehicle->dumping_landfill_id;
                $log->landfill_name = ! empty($data['landfill_name']) ? $data['landfill_name'] : ($vehicle->dumpingLandfill?->name);
            }

            if (! empty($data['waste_type_id'])) {
                $log->waste_type_id = (int) $data['waste_type_id'];
            }
            if (! empty($data['waste_type_name'])) {
                $log->waste_type_name = $data['waste_type_name'];
            } elseif ($log->waste_type_id) {
                $log->waste_type_name = WasteType::query()->whereKey($log->waste_type_id)->value('name');
            } else {
                $log->waste_type_name = null;
            }

            $log->save();
        });

        return $log->id;
    }

    public function download(array $data): void
    {
        $query = $this->landfillLogQuery();
        $this->applyFilters($query, $data);

        $columns = [
            __('Landfill Log ID'),
            __('Entry Date and Time'),
            __('Operation Date'),
            __('Organization'),
            __('Vehicle Number'),
            __('Vehicle Type'),
            __('Driver Name'),
            __('Landfill Name'),
            __('Waste Type'),
            __('Quantity (Ton)'),
            __('Weighbridge Weight (Ton)'),
            __('Effective Quantity (Ton)'),
            __('Source STSs'),
            __('Source Wards'),
            __('Operation Status'),
            __('Remarks'),
        ];

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('SW Landfill Logs.csv')
            ->addRowWithStyle($columns, $style);

        $statusLabels = LandfillLog::statusOptions();

        $query->orderBy('id')->chunk(5000, function ($rows) use ($writer, $statusLabels) {
            foreach ($rows as $row) {
                $wards = is_array($row->source_wards) ? implode(', ', $row->source_wards) : '';
                $stsNames = \App\Models\Swm\Sts::query()
                    ->whereIn('id', is_array($row->source_sts_ids) ? $row->source_sts_ids : [])
                    ->whereNull('deleted_at')
                    ->orderBy('name')
                    ->pluck('name')
                    ->all();
                $sourceSts = implode(', ', $stsNames);
                $effective = $row->weighbridge_weight_ton ?? $row->quantity_ton;

                $writer->addRow([
                    $row->id,
                    $row->entry_at?->format('Y-m-d H:i:s'),
                    $row->operation_date?->format('Y-m-d'),
                    $row->organization?->name,
                    $row->vehicle?->vehicle_number,
                    $row->vehicle_type_name,
                    $row->driver_name,
                    $row->landfill_name ?: $row->landfill?->name,
                    $row->waste_type_name ?: $row->wasteType?->name,
                    $row->quantity_ton,
                    $row->weighbridge_weight_ton,
                    $effective,
                    $sourceSts,
                    $wards,
                    $statusLabels[$row->operation_status] ?? $row->operation_status,
                    $row->remarks,
                ]);
            }
        });

        $writer->close();
    }

    protected function applyFilters($query, array $data): void
    {
        if (! empty($data['organization_id'] ?? null)) {
            $query->where('organization_id', (int) $data['organization_id']);
        }
        if (! empty($data['vehicle_search'] ?? null)) {
            $term = '%'.trim((string) $data['vehicle_search']).'%';
            $query->whereHas('vehicle', function ($vq) use ($term) {
                $vq->where('vehicle_number', 'ILIKE', $term)
                    ->orWhere('vehicle_id_no', 'ILIKE', $term);
            });
        }
        if (! empty($data['landfill_id'] ?? null)) {
            $query->where('landfill_id', (int) $data['landfill_id']);
        }
        if (! empty($data['operation_status'] ?? null)) {
            $query->where('operation_status', $data['operation_status']);
        }
        if (! empty($data['date_from'] ?? null)) {
            $query->whereDate('operation_date', '>=', Carbon::parse($data['date_from'])->toDateString());
        }
        if (! empty($data['date_to'] ?? null)) {
            $query->whereDate('operation_date', '<=', Carbon::parse($data['date_to'])->toDateString());
        }
    }
}
