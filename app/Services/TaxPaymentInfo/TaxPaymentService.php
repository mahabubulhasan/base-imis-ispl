<?php
// Last Modified: 2026-02-28
// Developed By: Streams Tech Ltd.
// Description: Handles tax payment data operations and DataTable generation.

namespace App\Services\TaxPaymentInfo;

use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;

class TaxPaymentService
{
    /**
     * Fetch tax payment data with action column for DataTable
     *
     * @param Request $request
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function fetchData(Request $request)
    {
        $sql = "SELECT ts.tax_code, ts.bin, ts.ward, ts.owner_name, ts.owner_contact, ts.due_year
                FROM taxpayment_info.tax_payment_status as ts
                JOIN taxpayment_info.tax_payments as tp on tp.tax_code = ts.tax_code
                ORDER BY ts.tax_code";

        $taxpaymentData = DB::table(DB::raw("($sql) AS tax"))
            ->leftjoin('taxpayment_info.due_years AS due', 'due.value', '=', 'tax.due_year')
            ->select('tax.*', 'due.name', 'tax.bin');

        return DataTables::of($taxpaymentData)
            ->filter(function ($query) use ($request) {
                if ($request->dueyear_select) {
                    $query->where('due.name', $request->dueyear_select);
                }
                if ($request->ward_select) {
                    $query->where('tax.ward', $request->ward_select);
                }
                if ($request->tax_code) {
                    $query->where('tax_code', 'ILIKE', '%' . $request->tax_code . '%');
                }
                if ($request->bin) {
                    $query->where('tax.bin', 'ILIKE', '%' . $request->bin . '%');
                }
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['tax-payment.destroy', $model->tax_code]]);

                $content .= '<a title="' . __("Edit") . '" href="' . action("TaxPaymentInfo\TaxPaymentController@edit", [$model->tax_code]) . '" class="btn btn-info btn-sm mb-1"><i class="fas fa-edit"></i></a> ';

                $content .= '<a title="' . __("Detail") . '" href="' . action("TaxPaymentInfo\TaxPaymentController@show", [$model->tax_code]) . '" class="btn btn-info btn-sm mb-1"><i class="fas fa-list"></i></a> ';

                $content .= '<a href="#" title="' . __("Delete") . '" class="delete btn btn-danger btn-sm mb-1"><i class="fas fa-trash"></i></a> ';

                $content .= \Form::close();
                return $content;
            })
            ->make(true);
    }

    public function getDetails($tax_code)
    {
        return DB::table('taxpayment_info.tax_payment_status as ts')
            ->join('taxpayment_info.tax_payments as tp', 'tp.tax_code', '=', 'ts.tax_code')
            ->where('ts.tax_code', $tax_code)
            ->select(
                'ts.tax_code',
                'ts.bin',
                'ts.ward',
                'tp.owner_name',
                'tp.owner_contact',
                'ts.due_year',
                'tp.created_at',
                'tp.updated_at',
                'tp.last_payment_date'
            )
            ->first();
    }
}
