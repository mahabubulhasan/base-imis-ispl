<?php

namespace App\Services\Swm;

use App\Models\Swm\Sts;
use App\Models\Swm\StsLog;
use App\Models\Swm\Vehicle;
use App\Models\Swm\WasteType;
use App\Support\Swm\SwmExcelColumns;
use App\Support\Swm\SwmExcelFilename;
use App\Support\Swm\SwmExcelTemplateWriter;
use App\Support\Swm\SwmImportTemplateOptions;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class StsLogService
{
    public function stsLogQuery(): Builder
    {
        return StsLog::query()
            ->whereNull('deleted_at')
            ->with(['vehicle', 'sts', 'wasteType']);
    }

    public function getAllStsLogs(array $data)
    {
        $query = $this->stsLogQuery();

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                $this->applyFilters($q, $data);
            })
            ->addColumn('vehicle_number', function (StsLog $model) {
                return $model->vehicle?->vehicle_number ?? '';
            })
            ->addColumn('sts_label', function (StsLog $model) {
                return $model->sts_name ?: ($model->sts?->name ?? '');
            })
            ->addColumn('waste_type_label', function (StsLog $model) {
                return $this->wasteTypesDisplayLabel($model);
            })
            ->addColumn('source_wards_label', function (StsLog $model) {
                return is_array($model->source_wards) ? implode(', ', $model->source_wards) : '';
            })
            ->editColumn('entry_at', function (StsLog $model) {
                return $model->entry_at?->format('Y-m-d H:i') ?? '';
            })
            ->editColumn('operation_date', function (StsLog $model) {
                return $model->operation_date?->format('Y-m-d') ?? '';
            })
            ->editColumn('quantity_ton', function (StsLog $model) {
                return $model->quantity_ton !== null ? (string) $model->quantity_ton : '';
            })
            ->orderColumn('vehicle_number', function ($query, $order) {
                $query->orderBy(
                    Vehicle::query()
                        ->select('vehicle_number')
                        ->whereColumn('swm.vehicles.id', 'swm.sts_logs.vehicle_id')
                        ->limit(1),
                    $order
                );
            })
            ->addColumn('action', function (StsLog $model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.sts-logs.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW STS Log')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\StsLogController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW STS Log')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\StsLogController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW STS Log History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\StsLogController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW STS Log')) {
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
            ->with(['vehicleType', 'driver', 'dumpingSts'])
            ->first();

        if (! $vehicle) {
            return null;
        }

        if (is_null($id)) {
            $log = new StsLog();
        } else {
            $log = StsLog::query()->find($id);
            if (! $log) {
                return null;
            }
        }

        DB::transaction(function () use ($log, $data, $vehicle): void {
            $log->vehicle_id = (int) $data['vehicle_id'];
            $log->entry_at = Carbon::parse($data['entry_at']);
            $log->operation_date = Carbon::parse($data['operation_date'])->toDateString();
            $log->quantity_ton = $data['quantity_ton'] ?? null;
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

            $log->sts_id = (int) $data['sts_id'];
            $log->sts_name = ! empty($data['sts_name'])
                ? $data['sts_name']
                : (Sts::query()->whereNull('deleted_at')->find((int) $data['sts_id'])?->name);

            $wtIds = array_values(array_unique(array_filter(
                array_map(static fn ($v) => (int) $v, $data['waste_type_ids'] ?? []),
                static fn ($v) => $v > 0
            )));
            $this->applyWasteTypeIdsToStsLog($log, $wtIds);

            $log->save();
        });

        return $log->id;
    }

    public function download(array $data): void
    {
        $query = $this->stsLogQuery();
        $this->applyFilters($query, $data);

        $columns = SwmExcelColumns::exportHeaders($this->excelColumnDefinitions());

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToBrowser(SwmExcelFilename::export('sts_logs'))
            ->addRowWithStyle($columns, $style);

        $query->orderBy('id')->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $wards = is_array($row->source_wards) ? implode(', ', $row->source_wards) : '';

                $writer->addRow([
                    $row->id,
                    $row->entry_at?->format('Y-m-d H:i:s'),
                    $row->operation_date?->format('Y-m-d'),
                    $row->vehicle?->vehicle_number,
                    $row->vehicle_type_name,
                    $row->driver_name,
                    $row->sts_name ?: $row->sts?->name,
                    $this->wasteTypesDisplayLabel($row),
                    $row->quantity_ton,
                    $wards,
                    $row->remarks,
                ]);
            }
        });

        $writer->close();
    }

    public function downloadTemplate(): void
    {
        app(SwmExcelTemplateWriter::class)->download(
            SwmExcelFilename::importTemplate('sts_logs'),
            SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions())
        );
    }

    /** @return array<int, array{key: string, label: string, export?: bool, import?: bool, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}> */
    protected function excelColumnDefinitions(): array
    {
        return [
            ['key' => 'id', 'label' => __('STS Log ID'), 'import' => false],
            ['key' => 'entry_at', 'label' => __('Entry Date and Time'), 'required' => true],
            ['key' => 'operation_date', 'label' => __('Operation Date'), 'required' => true],
            [
                'key' => 'vehicle_number',
                'label' => __('Vehicle Number'),
                'required' => true,
                'dropdown' => array_values(Vehicle::query()
                    ->whereNull('deleted_at')
                    ->whereNotNull('vehicle_number')
                    ->orderBy('vehicle_number')
                    ->pluck('vehicle_number')
                    ->all()),
            ],
            ['key' => 'vehicle_type_name', 'label' => __('Vehicle Type'), 'import' => false],
            ['key' => 'driver_name', 'label' => __('Driver Name'), 'import' => false],
            [
                'key' => 'sts_name',
                'label' => __('STS Name'),
                'required' => true,
                'dropdown' => SwmImportTemplateOptions::stsLabels(),
            ],
            [
                'key' => 'waste_types',
                'label' => __('Waste Type'),
                'multiselect' => true,
                'dropdown' => SwmImportTemplateOptions::wasteTypeNames(),
                'reference_key' => 'waste_types',
            ],
            ['key' => 'quantity_ton', 'label' => __('Quantity (Ton)')],
            [
                'key' => 'source_wards',
                'label' => __('Source Wards'),
                'multiselect' => true,
                'dropdown' => SwmImportTemplateOptions::wardNumberStrings(),
                'reference_key' => 'source_wards',
            ],
            ['key' => 'remarks', 'label' => __('Remarks')],
        ];
    }

    /** @return array<int, array{key: string, label: string, required?: bool, dropdown?: array<int, string>}> */
    protected function importTemplateColumns(): array
    {
        return SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions());
    }

    protected function wasteTypesDisplayLabel(StsLog $model): string
    {
        if (! empty($model->waste_type_name)) {
            return $model->waste_type_name;
        }
        $ids = $model->waste_type_ids ?? [];
        if (! empty($ids)) {
            return WasteType::query()
                ->whereIn('id', $ids)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->pluck('name')
                ->implode(', ');
        }

        return $model->wasteType?->name ?? '';
    }

    /**
     * @param  array<int>  $candidateIds
     */
    protected function applyWasteTypeIdsToStsLog(StsLog $log, array $candidateIds): void
    {
        $types = WasteType::query()
            ->whereIn('id', $candidateIds)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($types->isEmpty()) {
            $log->waste_type_ids = null;
            $log->waste_type_id = null;
            $log->waste_type_name = null;

            return;
        }

        $log->waste_type_ids = $types->pluck('id')->values()->all();
        $log->waste_type_id = $types->first()->id;
        $log->waste_type_name = $types->pluck('name')->implode(', ');
    }

    protected function applyFilters($query, array $data): void
    {
        if (! empty($data['vehicle_search'] ?? null)) {
            $term = '%'.trim((string) $data['vehicle_search']).'%';
            $query->whereHas('vehicle', function ($vq) use ($term) {
                $vq->where('vehicle_number', 'ILIKE', $term)
                    ->orWhere('vehicle_id_no', 'ILIKE', $term);
            });
        }
        if (! empty($data['sts_id'] ?? null)) {
            $query->where('sts_id', (int) $data['sts_id']);
        }
        if (! empty($data['date_from'] ?? null)) {
            $query->whereDate('operation_date', '>=', Carbon::parse($data['date_from'])->toDateString());
        }
        if (! empty($data['date_to'] ?? null)) {
            $query->whereDate('operation_date', '<=', Carbon::parse($data['date_to'])->toDateString());
        }
    }
}
