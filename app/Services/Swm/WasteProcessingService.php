<?php

namespace App\Services\Swm;

use App\Models\Swm\WasteProcessingLog;
use App\Services\Swm\Concerns\HasExcelColumnValidationLabels;
use App\Support\Swm\SwmExcelColumns;
use App\Support\Swm\SwmExcelFilename;
use App\Support\Swm\SwmExcelTemplateWriter;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class WasteProcessingService
{
    use HasExcelColumnValidationLabels;

    public function query(): Builder
    {
        return WasteProcessingLog::query()
            ->whereNull('deleted_at');
    }

    public function getAll(array $data)
    {
        $query = $this->query();

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                $this->applyFilters($q, $data);
            })
            ->editColumn('entry_at', fn (WasteProcessingLog $model) => $model->entry_at?->format('Y-m-d H:i') ?? '')
            ->editColumn('report_date', fn (WasteProcessingLog $model) => $model->report_date?->format('Y-m-d') ?? '')
            ->addColumn('reporting_month_label', function (WasteProcessingLog $model) {
                return $model->reporting_month?->format('M Y') ?? '';
            })
            ->addColumn('waste_processing_site_name', function (WasteProcessingLog $model) {
                return $model->waste_processing_site_name ?? '';
            })
            ->addColumn('action', function (WasteProcessingLog $model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.waste-processing.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Waste Processing')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\WasteProcessingController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Waste Processing')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\WasteProcessingController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Waste Processing History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\WasteProcessingController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Waste Processing')) {
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
        $log = is_null($id) ? new WasteProcessingLog() : WasteProcessingLog::query()->find($id);
        if (! $log) {
            return null;
        }

        DB::transaction(function () use ($log, $data): void {
            $log->entry_at = Carbon::parse($data['entry_at']);
            $log->report_date = Carbon::parse($data['report_date'])->toDateString();
            $log->reporting_month = Carbon::parse($data['reporting_month'])->startOfMonth()->toDateString();
            $log->waste_processing_site_name = $data['waste_processing_site_name'] ?? null;
            $log->waste_received_ton = $data['waste_received_ton'] ?? null;
            $log->organic_waste_composted_ton = $data['organic_waste_composted_ton'] ?? null;
            $log->inorganic_waste_recycled_ton = $data['inorganic_waste_recycled_ton'] ?? null;
            $log->waste_incinerated_ton = $data['waste_incinerated_ton'] ?? null;
            $log->waste_burned_open_air_ton = $data['waste_burned_open_air_ton'] ?? null;
            $log->residual_waste_landfilled_ton = $data['residual_waste_landfilled_ton'] ?? null;
            $log->remarks = $data['remarks'] ?? null;
            $log->save();
        });

        return $log->id;
    }

    public function download(array $data): void
    {
        $query = $this->query();
        $this->applyFilters($query, $data);

        $columnDefs = $this->excelColumnDefinitions();
        $columns = SwmExcelColumns::exportHeaders($columnDefs);

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToBrowser(SwmExcelFilename::export('waste_processing'))
            ->addRowWithStyle($columns, $style);

        $query->orderBy('id')->chunk(5000, function ($rows) use ($writer, $columnDefs) {
            foreach ($rows as $row) {
                $writer->addRow(SwmExcelColumns::buildExportRow(
                    $columnDefs,
                    $row,
                    fn (string $key, $model) => $this->formatWasteProcessingExportValue($key, $model)
                ));
            }
        });

        $writer->close();
    }

    public function downloadTemplate(): void
    {
        app(SwmExcelTemplateWriter::class)->download(
            SwmExcelFilename::importTemplate('waste_processing'),
            SwmExcelColumns::templateColumns($this->excelColumnDefinitions())
        );
    }

    /** @return array<int, array{key: string, label: string, export?: bool, import?: bool, required?: bool}> */
    protected function excelColumnDefinitions(): array
    {
        return [
            ['key' => 'id', 'label' => __('Waste Processing Log ID'), 'import' => false, 'template' => true, 'derived' => true],
            ['key' => 'entry_at', 'label' => __('Entry Date and Time'), 'required' => true],
            ['key' => 'report_date', 'label' => __('Report Date'), 'required' => true],
            ['key' => 'reporting_month', 'label' => __('Reporting Month'), 'required' => true],
            ['key' => 'waste_processing_site_name', 'label' => __('Waste Processing Site Name')],
            ['key' => 'waste_received_ton', 'label' => __('Quantity of Waste Received (Ton)')],
            ['key' => 'organic_waste_composted_ton', 'label' => __('Organic Waste Composted (Ton)')],
            ['key' => 'inorganic_waste_recycled_ton', 'label' => __('Inorganic Non-biodegradable Waste Recycled (Ton)')],
            ['key' => 'waste_incinerated_ton', 'label' => __('Waste Incinerated (Ton)')],
            ['key' => 'waste_burned_open_air_ton', 'label' => __('Waste Burned in Open Air (Ton)')],
            ['key' => 'residual_waste_landfilled_ton', 'label' => __('Residual Waste Landfilled (Ton)')],
            ['key' => 'remarks', 'label' => __('Remarks')],
        ];
    }

    /** @return array<int, array{key: string, label: string, required?: bool}> */
    protected function importTemplateColumns(): array
    {
        return SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions());
    }

    protected function formatWasteProcessingExportValue(string $key, $row): mixed
    {
        return match ($key) {
            'id' => $row->id,
            'entry_at' => $row->entry_at?->format('Y-m-d H:i:s'),
            'report_date' => $row->report_date?->format('Y-m-d'),
            'reporting_month' => $row->reporting_month?->format('M Y'),
            'waste_processing_site_name' => $row->waste_processing_site_name,
            'waste_received_ton' => $row->waste_received_ton,
            'organic_waste_composted_ton' => $row->organic_waste_composted_ton,
            'inorganic_waste_recycled_ton' => $row->inorganic_waste_recycled_ton,
            'waste_incinerated_ton' => $row->waste_incinerated_ton,
            'waste_burned_open_air_ton' => $row->waste_burned_open_air_ton,
            'residual_waste_landfilled_ton' => $row->residual_waste_landfilled_ton,
            'remarks' => $row->remarks,
            default => '',
        };
    }

    protected function applyFilters($query, array $data): void
    {
        if (! empty($data['date_from'] ?? null)) {
            $query->whereDate('report_date', '>=', Carbon::parse($data['date_from'])->toDateString());
        }
        if (! empty($data['date_to'] ?? null)) {
            $query->whereDate('report_date', '<=', Carbon::parse($data['date_to'])->toDateString());
        }
        if (! empty($data['reporting_month'] ?? null)) {
            $query->whereDate('reporting_month', Carbon::parse($data['reporting_month'])->startOfMonth()->toDateString());
        }
    }

    /** @return array<int, string> */
    public function requiredImportLabels(): array
    {
        return SwmExcelColumns::requiredImportLabels($this->excelColumnDefinitions());
    }

    /** @return array<int, array{key: string, label: string, required?: bool}> */
    public function importColumnDefinitions(): array
    {
        return SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions());
    }
}
