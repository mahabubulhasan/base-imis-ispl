<?php

namespace App\Services\Swm;

use App\Models\Swm\Complaint;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

class ComplaintService
{
    public function getAllComplaints(array $data)
    {
        $query = Complaint::query()->whereNull('deleted_at');

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['complaint_id'] ?? null)) {
                    $q->where('complaint_id', 'ILIKE', '%'.trim((string) $data['complaint_id']).'%');
                }
                if (! empty($data['holding_number'] ?? null)) {
                    $q->where('holding_number', 'ILIKE', '%'.trim((string) $data['holding_number']).'%');
                }
                if (! empty($data['household_id'] ?? null)) {
                    $q->where('customer_id', 'ILIKE', '%'.trim((string) $data['household_id']).'%');
                }
                if (! empty($data['name'] ?? null)) {
                    $q->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
                }
                if (! empty($data['contact_number'] ?? null)) {
                    $q->where('contact_number', 'ILIKE', '%'.trim((string) $data['contact_number']).'%');
                }
                if (! empty($data['complaint_type'] ?? null)) {
                    $q->where('complaint_type', $data['complaint_type']);
                }
                if (! empty($data['submitted_through'] ?? null)) {
                    $q->where('submitted_through', $data['submitted_through']);
                }
                if (! empty($data['complaint_status'] ?? null)) {
                    $q->where('complaint_status', $data['complaint_status']);
                }
                if (! empty($data['date_from'] ?? null)) {
                    $q->whereDate('date_time', '>=', Carbon::parse($data['date_from'])->toDateString());
                }
                if (! empty($data['date_to'] ?? null)) {
                    $q->whereDate('date_time', '<=', Carbon::parse($data['date_to'])->toDateString());
                }
            })
            ->editColumn('date_time', function ($model) {
                return $model->date_time?->format('Y-m-d H:i') ?? '';
            })
            ->editColumn('complaint_type', function ($model) {
                $map = config('swm_complaints.complaint_types', []);

                return $map[$model->complaint_type] ?? $model->complaint_type;
            })
            ->editColumn('submitted_through', function ($model) {
                $map = config('swm_complaints.submitted_through', []);

                return $map[$model->submitted_through] ?? $model->submitted_through;
            })
            ->editColumn('complaint_status', function ($model) {
                $map = config('swm_complaints.complaint_statuses', []);

                return $map[$model->complaint_status] ?? $model->complaint_status;
            })
            ->addColumn('household_id', function ($model) {
                return $model->customer_id;
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.complaints.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Complaint')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\ComplaintController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Complaint')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\ComplaintController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Complaint History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\ComplaintController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Complaint')) {
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
        if (is_null($id)) {
            $complaint = new Complaint();
        } else {
            $complaint = Complaint::find($id);
            if (! $complaint) {
                return null;
            }
        }

        $complaint->complaint_id = $data['complaint_id'] ?? null;
        $complaint->date_time = isset($data['date_time']) ? Carbon::parse($data['date_time']) : now();
        $complaint->holding_number = $data['holding_number'] ?? null;
        $complaint->customer_id = $data['household_id'] ?? null;
        $complaint->name = $data['name'] ?? null;
        $complaint->contact_number = $data['contact_number'] ?? null;
        $complaint->complaint_type = $data['complaint_type'] ?? null;
        $complaint->complaint_details = $data['complaint_details'] ?? null;
        $complaint->submitted_through = $data['submitted_through'] ?? null;
        $complaint->complaint_status = $data['complaint_status'] ?? null;
        $complaint->notes = $data['notes'] ?? null;
        $complaint->save();

        return $complaint->id;
    }

    public function download(array $data): void
    {
        $query = Complaint::query()->whereNull('deleted_at');

        if (! empty($data['complaint_id'] ?? null)) {
            $query->where('complaint_id', 'ILIKE', '%'.trim((string) $data['complaint_id']).'%');
        }
        if (! empty($data['holding_number'] ?? null)) {
            $query->where('holding_number', 'ILIKE', '%'.trim((string) $data['holding_number']).'%');
        }
        if (! empty($data['household_id'] ?? null)) {
            $query->where('customer_id', 'ILIKE', '%'.trim((string) $data['household_id']).'%');
        }
        if (! empty($data['name'] ?? null)) {
            $query->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
        }
        if (! empty($data['contact_number'] ?? null)) {
            $query->where('contact_number', 'ILIKE', '%'.trim((string) $data['contact_number']).'%');
        }
        if (! empty($data['complaint_type'] ?? null)) {
            $query->where('complaint_type', $data['complaint_type']);
        }
        if (! empty($data['submitted_through'] ?? null)) {
            $query->where('submitted_through', $data['submitted_through']);
        }
        if (! empty($data['complaint_status'] ?? null)) {
            $query->where('complaint_status', $data['complaint_status']);
        }
        if (! empty($data['date_from'] ?? null)) {
            $query->whereDate('date_time', '>=', Carbon::parse($data['date_from'])->toDateString());
        }
        if (! empty($data['date_to'] ?? null)) {
            $query->whereDate('date_time', '<=', Carbon::parse($data['date_to'])->toDateString());
        }

        $columns = [
            __('Complaint ID'),
            __('Date and Time'),
            __('Holding Number'),
            __('Household ID'),
            __('Name'),
            __('Contact Number'),
            __('Complaint Type'),
            __('Complaint Submitted through'),
            __('Complaint Status'),
            __('Complaint Details'),
            __('Notes'),
        ];

        $typeMap = config('swm_complaints.complaint_types', []);
        $throughMap = config('swm_complaints.submitted_through', []);
        $statusMap = config('swm_complaints.complaint_statuses', []);

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('SW Complaints.csv')
            ->addRowWithStyle($columns, $style);

        $query->orderBy('id')->chunk(5000, function ($rows) use ($writer, $typeMap, $throughMap, $statusMap) {
            foreach ($rows as $row) {
                $writer->addRow([
                    $row->complaint_id,
                    $row->date_time?->format('Y-m-d H:i:s'),
                    $row->holding_number,
                    $row->customer_id,
                    $row->name,
                    $row->contact_number,
                    $typeMap[$row->complaint_type] ?? $row->complaint_type,
                    $throughMap[$row->submitted_through] ?? $row->submitted_through,
                    $statusMap[$row->complaint_status] ?? $row->complaint_status,
                    $row->complaint_details,
                    $row->notes,
                ]);
            }
        });

        $writer->close();
    }
}
