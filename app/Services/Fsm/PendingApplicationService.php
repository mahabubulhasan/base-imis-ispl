<?php
// Last Modified: 12-03-2026
// Developed By: Streams Tech Ltd.
// Description: Provides pending application listing, detail data shaping, and delete helper methods.

namespace App\Services\Fsm;

use App\Models\Fsm\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class PendingApplicationService
{
    protected string $indexRoute;

    public function __construct()
    {
        $this->indexRoute = route('pending-application.index');
    }

    public function getIndexRoute(): string
    {
        return $this->indexRoute;
    }

    public function getPendingApplicationsQuery(Request $request): Builder
    {
        $query = Application::query();

        if ($request->filled('tax_id')) {
            $query->where('tax_code', 'ILIKE', '%' . trim($request->tax_id) . '%');
        }

        if ($request->filled('customer_name')) {
            $query->where('applicant_name', 'ILIKE', '%' . trim($request->customer_name) . '%');
        }

        if ($request->filled('ward')) {
            $query->where('ward', 'ILIKE', '%' . trim($request->ward) . '%');
        }

        if ($request->filled('applicant_contact')) {
            $query->where('customer_contact', 'ILIKE', '%' . trim($request->customer_contact) . '%');
        }

        return $query->orderByDesc('id');
    }

    public function getDatatable(Request $request)
    {
        $query = $this->getPendingApplicationsQuery($request);
        $query->where('approved_status', false);

        return DataTables::of($query)
            ->addColumn('action', function (Application $pendingApplication) {
                $actions = '';

                if (Auth::user()->can('View Application')) {
                    $actions .= '<a href="' . route('pending-application.show', $pendingApplication->id) . '" class="btn btn-info btn-sm" title="' . __('View') . '"><i class="fas fa-eye"></i></a> ';
                }

                if (Auth::user()->can('Delete Application')) {
                    $actions .= '<form method="POST" action="' . route('pending-application.destroy', $pendingApplication->id) . '" style="display:inline-block;">'
                        . csrf_field()
                        . method_field('DELETE')
                        . '<button type="submit" class="btn btn-danger btn-sm delete" title="' . __('Delete') . '"><i class="fas fa-trash"></i></button>'
                        . '</form>';
                }

                return $actions;
            })
            ->editColumn('proposed_emptying_date', function (Application $pendingApplication) {
                return $pendingApplication->proposed_emptying_date ?: '-';
            })
            ->editColumn('approved_status', function (Application $pendingApplication) {
                return $pendingApplication->approved_status ? __('Approved') : __('Pending');
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getShowData(Application $pendingApplication): array
    {
        return [
            __('ID') => $pendingApplication->id,
            __('Tax ID') => $pendingApplication->tax_code ?: '-',
            __('Customer Name') => $pendingApplication->applicant_name ?: '-',
            __('Customer Contact') => $pendingApplication->applicant_contact ?: '-',
            __('Holding Owner Name') => $pendingApplication->customer_name ?: '-',
            __('Ward') => $pendingApplication->ward ?: '-',
            __('Road Code') => $pendingApplication->road_code ?: '-',
            __('Address') => $pendingApplication->address ?: '-',
            __('Proposed Emptying Date') => $pendingApplication->proposed_emptying_date ?: '-',
            __('Notes') => $pendingApplication->note ?: '-',
            __('Approval Status') => $pendingApplication->approved_status ? __('Approved') : __('Pending'),
            __('Created At') => optional($pendingApplication->created_at)->format('Y-m-d H:i:s') ?: '-',
        ];
    }
}
