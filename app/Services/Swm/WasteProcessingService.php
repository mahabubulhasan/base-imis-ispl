<?php

namespace App\Services\Swm;

use App\Models\Swm\WasteProcessingLog;
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
    public function query(): Builder
    {
        $query = WasteProcessingLog::query()
            ->whereNull('deleted_at')
            ->with('organization');

        $orgId = Auth::user()?->swm_organization_id;
        if ($orgId) {
            $query->where('organization_id', (int) $orgId);
        }

        return $query;
    }

    public function getAll(array $data)
    {
        $query = $this->query();

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                $this->applyFilters($q, $data);
            })
            ->addColumn('organization_name', fn (WasteProcessingLog $model) => $model->organization?->name ?? '')
            ->editColumn('entry_at', fn (WasteProcessingLog $model) => $model->entry_at?->format('Y-m-d H:i') ?? '')
            ->editColumn('report_date', fn (WasteProcessingLog $model) => $model->report_date?->format('Y-m-d') ?? '')
            ->addColumn('reporting_month_label', function (WasteProcessingLog $model) {
                return $model->reporting_month?->format('M Y') ?? '';
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
            $log->organization_id = (int) $data['organization_id'];
            $log->entry_at = Carbon::parse($data['entry_at']);
            $log->report_date = Carbon::parse($data['report_date'])->toDateString();
            $log->reporting_month = Carbon::parse($data['reporting_month'])->startOfMonth()->toDateString();
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

        $columns = [
            __('Waste Processing Log ID'),
            __('Entry Date and Time'),
            __('Report Date'),
            __('Reporting Month'),
            __('Organization'),
            __('Quantity of Waste Received (Ton)'),
            __('Organic Waste Composted (Ton)'),
            __('Inorganic Non-biodegradable Waste Recycled (Ton)'),
            __('Waste Incinerated (Ton)'),
            __('Waste Burned in Open Air (Ton)'),
            __('Residual Waste Landfilled (Ton)'),
            __('Remarks'),
        ];

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('SW Waste Processing.csv')
            ->addRowWithStyle($columns, $style);

        $query->orderBy('id')->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $writer->addRow([
                    $row->id,
                    $row->entry_at?->format('Y-m-d H:i:s'),
                    $row->report_date?->format('Y-m-d'),
                    $row->reporting_month?->format('M Y'),
                    $row->organization?->name,
                    $row->waste_received_ton,
                    $row->organic_waste_composted_ton,
                    $row->inorganic_waste_recycled_ton,
                    $row->waste_incinerated_ton,
                    $row->waste_burned_open_air_ton,
                    $row->residual_waste_landfilled_ton,
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
}
