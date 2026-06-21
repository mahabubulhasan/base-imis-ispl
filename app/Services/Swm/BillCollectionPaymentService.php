<?php

namespace App\Services\Swm;

use App\Models\BuildingInfo\Household;
use App\Models\Swm\BillCollectionPayment;
use App\Services\Formatting\Currency;
use App\Services\Swm\Concerns\HasExcelColumnValidationLabels;
use App\Services\Formatting\CurrencyFormatter;
use App\Support\Swm\SwmExcelColumns;
use App\Support\Swm\SwmExcelExportWriter;
use App\Support\Swm\SwmExcelFilename;
use App\Support\Swm\SwmExcelTemplateWriter;
use App\Support\Swm\SwmImportRowHelper;
use App\Support\Swm\SwmImportTemplateOptions;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class BillCollectionPaymentService
{
    use HasExcelColumnValidationLabels;

    public function __construct(
        protected CurrencyFormatter $currencyFormatter,
    ) {
    }

    /**
     * @return array<string, string> holding_number => label for Select2
     */
    public function searchHoldings(string $q, int $limit = 30): array
    {
        $q = trim($q);
        if (strlen($q) < 1) {
            return [];
        }

        $rows = Household::query()
            ->whereNull('deleted_at')
            ->activeStatus()
            ->whereNotNull('holding_number')
            ->where('holding_number', 'ILIKE', '%'.$q.'%')
            ->select('holding_number')
            ->selectRaw('MIN(id) as min_id')
            ->groupBy('holding_number')
            ->orderBy('holding_number')
            ->limit($limit)
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $hn = $row->holding_number;
            $out[$hn] = $hn;
        }

        return $out;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function customersByHolding(string $holdingNumber, ?string $q = null): array
    {
        $query = Household::query()
            ->whereNull('deleted_at')
            ->activeStatus()
            ->where('holding_number', $holdingNumber)
            ->orderBy('household_id');

        if ($q !== null && $q !== '') {
            $term = '%'.trim($q).'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('household_id', 'ILIKE', $term)
                    ->orWhere('household_owner_name', 'ILIKE', $term)
                    ->orWhere('father_or_husband_name', 'ILIKE', $term);
            });
        }

        return $query->get([
            'id',
            'household_id',
            'household_owner_name',
            'father_or_husband_name',
            'holding_number',
            'contact_number',
            'area_mohalla_name',
            'ward',
            'road_no',
            'road_name',
            'waste_charge',
            'using_this_service_since',
            'survey_date',
        ])
            ->map(fn ($site) => [
                'id' => $site->id,
                'text' => $site->household_owner_name.' -- '.$site->household_id,
                'household_id' => $site->household_id,
                'household_owner_name' => $site->household_owner_name,
                'father_or_husband_name' => $site->father_or_husband_name,
                'holding_number' => $site->holding_number,
                'contact_number' => $site->contact_number,
                'sub_location' => $site->area_mohalla_name,
                'ward' => $site->ward !== null && $site->ward !== '' ? (string) $site->ward : null,
                'road_no' => $site->road_no,
                'road_name' => $site->road_name,
                'waste_charge' => $site->waste_charge,
                'using_this_service_since' => $site->using_this_service_since?->format('Y-m-d'),
                'survey_date' => $site->survey_date?->format('Y-m-d'),
            ])
            ->values()
            ->all();
    }

    /**
     * Billing status filter: customers under one or more holdings, or global search when no holdings (q min 2 chars).
     *
     * @param  array<int, string>|null  $holdingNumbers
     * @return array<int, array<string, mixed>>
     */
    public function customersForBillingFilter(?array $holdingNumbers, ?string $q = null, int $limit = 200): array
    {
        $holdingNumbers = is_array($holdingNumbers) ? $holdingNumbers : [];

        $holdings = array_values(array_unique(array_filter(array_map(static function ($h) {
            return is_string($h) ? trim($h) : '';
        }, $holdingNumbers), static fn (string $h) => $h !== '')));

        $query = Household::query()
            ->whereNull('deleted_at')
            ->activeStatus();

        if ($holdings !== []) {
            $query->whereIn('holding_number', $holdings);
        } else {
            $term = trim((string) ($q ?? ''));
            if (strlen($term) < 2) {
                return [];
            }
            $like = '%'.$term.'%';
            $query->where(function ($sub) use ($like) {
                $sub->where('household_id', 'ILIKE', $like)
                    ->orWhere('household_owner_name', 'ILIKE', $like)
                    ->orWhere('father_or_husband_name', 'ILIKE', $like);
            });
        }

        if ($q !== null && $q !== '' && $holdings !== []) {
            $like = '%'.trim((string) $q).'%';
            $query->where(function ($sub) use ($like) {
                $sub->where('household_id', 'ILIKE', $like)
                    ->orWhere('household_owner_name', 'ILIKE', $like)
                    ->orWhere('father_or_husband_name', 'ILIKE', $like);
            });
        }

        return $query->orderBy('holding_number')
            ->orderBy('household_id')
            ->limit($limit)
            ->get(['id', 'household_id', 'household_owner_name', 'father_or_husband_name', 'holding_number', 'waste_charge', 'using_this_service_since', 'survey_date'])
            ->map(fn ($site) => [
                'id' => $site->id,
                'text' => ($site->household_id ?? '').' — '.($site->household_owner_name ?? '').($site->father_or_husband_name ? ' ('.$site->father_or_husband_name.')' : '').($site->holding_number ? ' ('.$site->holding_number.')' : ''),
                'household_id' => $site->household_id,
                'holding_number' => $site->holding_number,
                'waste_charge' => $site->waste_charge,
                'using_this_service_since' => $site->using_this_service_since?->format('Y-m-d'),
                'survey_date' => $site->survey_date?->format('Y-m-d'),
            ])
            ->values()
            ->all();
    }

    public function billingAnchor(Household $site): ?Carbon
    {
        if ($site->using_this_service_since) {
            return Carbon::parse($site->using_this_service_since)->startOfMonth();
        }
        if ($site->survey_date) {
            return Carbon::parse($site->survey_date)->startOfMonth();
        }

        return null;
    }

    public function billableMonthCount(Household $site, Carbon $paymentMonthStart): int
    {
        $paymentMonthStart = $paymentMonthStart->copy()->startOfMonth();
        $wasteCharge = $site->waste_charge;

        if ($wasteCharge === null) {
            return 0;
        }

        $anchor = $this->billingAnchor($site);
        if ($anchor === null) {
            return 1;
        }

        if ($paymentMonthStart->lt($anchor)) {
            return 0;
        }

        return (int) $anchor->diffInMonths($paymentMonthStart) + 1;
    }

    /**
     * @return array{
     *     waste_charge: ?string,
     *     billing_anchor: ?string,
     *     billable_month_count: int,
     *     cumulative_obligation: ?string,
     *     cumulative_paid_through: string,
     *     due: ?string,
     *     current_month_amount_paid: string,
     *     current_month_fully_paid: bool
     * }
     */
    public function balanceThroughMonth(Household $site, Carbon $paymentForMonth, ?int $excludePaymentId = null): array
    {
        $monthStart = $paymentForMonth->copy()->startOfMonth();
        $anchor = $this->billingAnchor($site);
        $billableMonths = $this->billableMonthCount($site, $monthStart);
        $wasteCharge = $site->waste_charge;

        $cumulativeObligation = null;
        if ($wasteCharge !== null) {
            if ($billableMonths > 0) {
                $cumulativeObligation = bcmul((string) $wasteCharge, (string) $billableMonths, 2);
            } else {
                $cumulativeObligation = '0.00';
            }
        }

        $cumulativePaid = $this->cumulativePaidThroughMonth($site, $monthStart, $excludePaymentId);
        $currentMonthAmountPaid = $this->currentMonthAmountPaid($site, $monthStart, $excludePaymentId);

        $due = null;
        if ($cumulativeObligation !== null) {
            $due = bcsub($cumulativeObligation, $cumulativePaid, 2);
            if (bccomp($due, '0', 2) < 0) {
                $due = '0.00';
            }
        }

        return [
            'waste_charge' => $wasteCharge !== null
                ? $this->currencyFormatter->format(Currency::TK, $wasteCharge, false)
                : null,
            'billing_anchor' => $anchor?->toDateString(),
            'billable_month_count' => $billableMonths,
            'cumulative_obligation' => $cumulativeObligation,
            'cumulative_paid_through' => $cumulativePaid,
            'due' => $due,
            'current_month_amount_paid' => $currentMonthAmountPaid,
            'current_month_fully_paid' => $wasteCharge !== null && bccomp($currentMonthAmountPaid, (string) $wasteCharge, 2) >= 0,
        ];
    }

    /**
     * Billable months gained when moving from the prior calendar month into {@see $monthStart} (0 or 1 with current rules).
     */
    public function marginalBillableUnitCount(Household $site, Carbon $monthStart): int
    {
        $monthStart = $monthStart->copy()->startOfMonth();
        $prev = $monthStart->copy()->subMonthNoOverflow()->startOfMonth();
        $cNow = $this->billableMonthCount($site, $monthStart);
        $cPrev = $this->billableMonthCount($site, $prev);

        return max(0, $cNow - $cPrev);
    }

    /**
     * Sum of payments recorded for exactly this billing month (payment_for_month = first day of month).
     */
    public function paidForPaymentMonth(Household $site, Carbon $monthStart): string
    {
        $d = $monthStart->copy()->startOfMonth()->toDateString();
        $sum = BillCollectionPayment::query()
            ->where('household_id', $site->id)
            ->whereNull('deleted_at')
            ->whereDate('payment_for_month', $d)
            ->sum(DB::raw('amount + COALESCE(due_paid, 0)'));

        return number_format((float) $sum, 2, '.', '');
    }

    /**
     * Amount still owed for this calendar month only (marginal obligation for the month minus payments tagged to that month).
     */
    public function marginalDueForMonth(Household $site, Carbon $monthStart): string
    {
        if ($site->waste_charge === null) {
            return '0.00';
        }

        $monthStart = $monthStart->copy()->startOfMonth();
        $prevMonthStart = $monthStart->copy()->subMonthNoOverflow()->startOfMonth();

        $dueThroughThisMonth = $this->balanceThroughMonth($site, $monthStart)['due'] ?? '0.00';
        $dueThroughPrevMonth = $this->balanceThroughMonth($site, $prevMonthStart)['due'] ?? '0.00';

        $marginalDue = bcsub((string) $dueThroughThisMonth, (string) $dueThroughPrevMonth, 2);
        if (bccomp($marginalDue, '0', 2) < 0) {
            return '0.00';
        }

        return $marginalDue;
    }

    /**
     * Remaining due per month in the given range using FIFO settlement:
     * each payment (amount + due_paid) is applied to the oldest unpaid month
     * up to and including that payment_for_month.
     *
     * @return array<string, string> keyed by Y-m (e.g. 2026-05 => "200.00")
     */
    public function outstandingByMonthInRange(Household $site, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $rangeStart = $rangeStart->copy()->startOfMonth();
        $rangeEnd = $rangeEnd->copy()->startOfMonth();
        if ($rangeStart->gt($rangeEnd) || $site->waste_charge === null) {
            return [];
        }

        $anchor = $this->billingAnchor($site);
        $calcStart = $anchor && $anchor->lt($rangeStart) ? $anchor->copy() : $rangeStart->copy();

        $months = [];
        $monthCursor = $calcStart->copy();
        $guard = 0;
        while ($monthCursor->lte($rangeEnd) && $guard < 240) {
            $guard++;
            $key = $monthCursor->format('Y-m');
            $units = $this->marginalBillableUnitCount($site, $monthCursor);
            $obligation = $units > 0
                ? bcmul((string) $site->waste_charge, (string) $units, 2)
                : '0.00';
            $months[$key] = [
                'month' => $monthCursor->copy(),
                'remaining' => $obligation,
            ];
            $monthCursor->addMonth();
        }

        $payments = BillCollectionPayment::query()
            ->where('household_id', $site->id)
            ->whereNull('deleted_at')
            ->whereDate('payment_for_month', '<=', $rangeEnd->toDateString())
            ->orderBy('payment_for_month')
            ->orderBy('id')
            ->get(['payment_for_month', 'amount', 'due_paid']);

        foreach ($payments as $payment) {
            $paymentMonth = Carbon::parse($payment->payment_for_month)->startOfMonth();
            $amount = (string) ($payment->amount ?? 0);
            $duePaid = (string) ($payment->due_paid ?? 0);

            // Current-month installment: apply only to the selected payment month bucket.
            if (bccomp($amount, '0', 2) > 0) {
                $paymentMonthKey = $paymentMonth->format('Y-m');
                if (isset($months[$paymentMonthKey])) {
                    $remainingForPaymentMonth = $months[$paymentMonthKey]['remaining'];
                    if (bccomp($remainingForPaymentMonth, '0', 2) > 0) {
                        if (bccomp($amount, $remainingForPaymentMonth, 2) >= 0) {
                            $months[$paymentMonthKey]['remaining'] = '0.00';
                        } else {
                            $months[$paymentMonthKey]['remaining'] = bcsub($remainingForPaymentMonth, $amount, 2);
                        }
                    }
                }
            }

            // Arrears component: apply FIFO to oldest unpaid months up to payment month.
            $available = $duePaid;
            if (bccomp($available, '0', 2) <= 0) {
                continue;
            }
            foreach ($months as $entryKey => $entry) {
                /** @var Carbon $entryMonth */
                $entryMonth = $entry['month'];
                if ($entryMonth->gt($paymentMonth)) {
                    break;
                }
                if (bccomp($available, '0', 2) <= 0) {
                    break;
                }
                $remaining = $months[$entryKey]['remaining'];
                if (bccomp($remaining, '0', 2) <= 0) {
                    continue;
                }

                if (bccomp($available, $remaining, 2) >= 0) {
                    $available = bcsub($available, $remaining, 2);
                    $months[$entryKey]['remaining'] = '0.00';
                } else {
                    $months[$entryKey]['remaining'] = bcsub($remaining, $available, 2);
                    $available = '0.00';
                }
            }
        }

        $out = [];
        $sliceCursor = $rangeStart->copy();
        $sliceGuard = 0;
        while ($sliceCursor->lte($rangeEnd) && $sliceGuard < 240) {
            $sliceGuard++;
            $key = $sliceCursor->format('Y-m');
            $out[$key] = isset($months[$key]) ? $months[$key]['remaining'] : '0.00';
            $sliceCursor->addMonth();
        }

        return $out;
    }

    protected function cumulativePaidThroughMonth(Household $site, Carbon $monthStart, ?int $excludePaymentId = null): string
    {
        $paidQuery = BillCollectionPayment::query()
            ->where('household_id', $site->id)
            ->whereNull('deleted_at')
            ->whereDate('payment_for_month', '<=', $monthStart->toDateString());

        if ($excludePaymentId) {
            $paidQuery->where('id', '!=', $excludePaymentId);
        }

        return (string) $paidQuery->sum(DB::raw('amount + COALESCE(due_paid, 0)'));
    }

    protected function currentMonthAmountPaid(Household $site, Carbon $monthStart, ?int $excludePaymentId = null): string
    {
        $query = BillCollectionPayment::query()
            ->where('household_id', $site->id)
            ->whereNull('deleted_at')
            ->whereDate('payment_for_month', $monthStart->toDateString());

        if ($excludePaymentId) {
            $query->where('id', '!=', $excludePaymentId);
        }

        return number_format((float) $query->sum('amount'), 2, '.', '');
    }

    protected function baseQuery(): Builder
    {
        $query = BillCollectionPayment::query()
            ->select('swm.bill_collection_payments.*')
            ->leftJoin('building_info.households as swm_pcs', 'swm.bill_collection_payments.household_id', '=', 'swm_pcs.id')
            ->leftJoin('building_info.buildings as swm_hh_building', 'swm_pcs.bin', '=', 'swm_hh_building.bin')
            ->leftJoin('auth.users as recv_user', 'swm.bill_collection_payments.received_by_user_id', '=', 'recv_user.id')
            ->addSelect([
                'swm_pcs.household_owner_name as site_household_owner_name',
                'swm_pcs.father_or_husband_name as site_father_or_husband_name',
                'swm.bill_collection_payments.customer_id as household_code',
                'swm_pcs.contact_number as household_contact_number',
                'swm_pcs.area_mohalla_name as household_sub_location',
                'swm_hh_building.ward as household_ward',
                DB::raw("COALESCE(recv_user.name, '') as received_by_name"),
            ])
            ->whereNull('swm.bill_collection_payments.deleted_at');

        return $query;
    }

    public function getAllPayments(array $data)
    {
        $query = $this->baseQuery();

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['holding_number'] ?? null)) {
                    $q->where('swm.bill_collection_payments.holding_number', 'ILIKE', '%'.trim((string) $data['holding_number']).'%');
                }
                if (! empty($data['household_id'] ?? null)) {
                    $q->where('swm.bill_collection_payments.customer_id', 'ILIKE', '%'.trim((string) $data['household_id']).'%');
                }
                if (! empty($data['payment_for_month'] ?? null)) {
                    $q->whereDate('swm.bill_collection_payments.payment_for_month', Carbon::parse($data['payment_for_month'])->startOfMonth());
                }
            })
            ->orderColumn('site_household_owner_name', 'swm_pcs.household_owner_name $1')
            ->orderColumn('received_by_name', 'recv_user.name $1')
            ->orderColumn('holding_number', 'swm.bill_collection_payments.holding_number $1')
            ->orderColumn('household_id', 'swm.bill_collection_payments.customer_id $1')
            ->orderColumn('ward', 'swm_hh_building.ward $1')
            ->orderColumn('sub_location', 'swm_pcs.sub_location $1')
            ->orderColumn('contact_number', 'swm_pcs.contact_number $1')
            ->orderColumn('receipt_no', 'swm.bill_collection_payments.receipt_no $1')
            ->orderColumn('amount', 'swm.bill_collection_payments.amount $1')
            ->orderColumn('due_paid', 'swm.bill_collection_payments.due_paid $1')
            ->orderColumn('payment_for_month', 'swm.bill_collection_payments.payment_for_month $1')
            ->orderColumn('payment_time', 'swm.bill_collection_payments.payment_time $1')
            ->editColumn('payment_for_month', function ($model) {
                return $model->payment_for_month?->format('M Y') ?? '';
            })
            ->editColumn('payment_time', function ($model) {
                return $model->payment_time?->format('Y-m-d H:i') ?? '';
            })
            ->editColumn('amount', function ($model) {
                return $this->currencyFormatter->format(Currency::TK, $model->amount);
            })
            ->addColumn('due_paid', function ($model) {
                return $this->currencyFormatter->format(Currency::TK, $model->due_paid ?? 0);
            })
            ->addColumn('total_collected', function ($model) {
                $total = (float) ($model->amount ?? 0) + (float) ($model->due_paid ?? 0);

                return $this->currencyFormatter->format(Currency::TK, $total);
            })
            ->editColumn('payment_method', function ($model) {
                $methods = config('bill_collection.payment_methods', []);

                return $methods[$model->payment_method] ?? $model->payment_method;
            })
            ->addColumn('household_id', function ($model) {
                return $model->household_code;
            })
            ->addColumn('household_owner_name', function ($model) {
                return $model->site_household_owner_name;
            })
            ->addColumn('ward', function ($model) {
                return $model->household_ward ?? '';
            })
            ->addColumn('sub_location', function ($model) {
                return $model->household_sub_location ?? '';
            })
            ->addColumn('contact_number', function ($model) {
                return $model->household_contact_number ?? '';
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.bill-collection-payments.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Bill Collection Payment')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\BillCollectionPaymentController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Bill Collection Payment')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\BillCollectionPaymentController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Bill Collection Payment History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\BillCollectionPaymentController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Bill Collection Payment')) {
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
        $householdPk = (int) ($data['household_id'] ?? 0);
        if ($householdPk <= 0) {
            return null;
        }
        $siteQuery = Household::query()
            ->whereKey($householdPk)
            ->whereNull('deleted_at');
        if ($id === null) {
            $siteQuery->activeStatus();
        }
        $site = $siteQuery->first();

        if (! $site) {
            return null;
        }

        if (is_null($id)) {
            $payment = new BillCollectionPayment();
        } else {
            $payment = BillCollectionPayment::find($id);
            if (! $payment) {
                return null;
            }
        }

        $payment->household_id = (int) $site->id;
        $payment->holding_number = $site->holding_number ?? '';
        $payment->customer_id = $site->household_id;
        $payment->amount = $data['amount'] ?? 0;
        $payment->due_paid = $data['due_paid'] ?? 0;
        $payment->payment_for_month = Carbon::parse($data['payment_for_month'] ?? null)->startOfMonth();
        $payment->payment_time = isset($data['payment_time']) ? Carbon::parse($data['payment_time']) : now();
        $payment->payment_method = $data['payment_method'] ?? '';
        $payment->received_by_user_id = $data['received_by_user_id'] ?? Auth::id();
        if (array_key_exists('receipt_copy_path', $data)) {
            $payment->receipt_copy_path = $data['receipt_copy_path'];
        }
        if (array_key_exists('receipt_no', $data)) {
            $payment->receipt_no = $data['receipt_no'] !== '' && $data['receipt_no'] !== null
                ? (string) $data['receipt_no']
                : null;
        }
        $payment->save();

        return $payment->id;
    }

    public function download(array $data): void
    {
        $holdingNumber = $data['holding_number'] ?? null;
        $householdId = $data['household_id'] ?? null;
        $paymentForMonth = $data['payment_for_month'] ?? null;

        $columns = SwmExcelColumns::exportHeaders($this->exportColumnDefinitions());

        $query = $this->baseQuery();

        if (! empty($holdingNumber)) {
            $query->where('swm.bill_collection_payments.holding_number', 'ILIKE', '%'.trim((string) $holdingNumber).'%');
        }
        if (! empty($householdId)) {
            $query->where('swm.bill_collection_payments.customer_id', 'ILIKE', '%'.trim((string) $householdId).'%');
        }
        if (! empty($paymentForMonth)) {
            $query->whereDate('swm.bill_collection_payments.payment_for_month', Carbon::parse($paymentForMonth)->startOfMonth());
        }

        (new SwmExcelExportWriter())->download(SwmExcelFilename::export('bill_collection'), $columns, function ($sheet, $colLetter) use ($query) {
            $rowNum = 2;
            $query->orderBy('swm.bill_collection_payments.id')->chunk(5000, function ($rows) use ($sheet, $colLetter, &$rowNum) {
                foreach ($rows as $row) {
                    $methods = config('bill_collection.payment_methods', []);
                    $methodLabel = $methods[$row->payment_method] ?? $row->payment_method;
                    $values = [
                        $row->holding_number,
                        $row->customer_id,
                        $row->site_household_owner_name,
                        $row->site_father_or_husband_name ?? '',
                        $row->household_ward ?? '',
                        $row->household_sub_location ?? '',
                        $row->household_contact_number ?? '',
                        $row->receipt_no ?? '',
                        $row->amount,
                        $row->due_paid ?? 0,
                        (float) ($row->amount ?? 0) + (float) ($row->due_paid ?? 0),
                        SwmImportRowHelper::exportMonth($row->payment_for_month),
                        SwmImportRowHelper::exportDateTime($row->payment_time),
                        $methodLabel,
                        $row->received_by_name,
                    ];
                    foreach ($values as $index => $value) {
                        $sheet->setCellValue($colLetter($index + 1).$rowNum, $value);
                    }
                    $rowNum++;
                }
            });
        });
    }

    public function downloadTemplate(): void
    {
        (new SwmExcelTemplateWriter())->download(
            SwmExcelFilename::importTemplate('bill_collection'),
            SwmExcelColumns::templateColumns($this->excelColumnDefinitions())
        );
    }

    /** @return array<int, array{key: string, label: string, export?: bool, import?: bool, template?: bool, derived?: bool, required?: bool, dropdown?: array<int, string>}> */
    protected function excelColumnDefinitions(): array
    {
        return [
            ['key' => 'household_id', 'label' => __('Household ID'), 'required' => true, 'dropdown' => SwmImportTemplateOptions::householdCustomerLabels()],
            ['key' => 'holding_number', 'label' => __('Holding Number')],
            ['key' => 'contact_number', 'label' => __('Contact Number'), 'import' => false, 'template' => true, 'derived' => true],
            ['key' => 'sub_location', 'label' => __('Sub Location'), 'import' => false, 'template' => true, 'derived' => true],
            ['key' => 'ward', 'label' => __('Ward'), 'import' => false, 'template' => true, 'derived' => true],
            ['key' => 'road_no', 'label' => __('Road No.'), 'import' => false, 'template' => true, 'derived' => true],
            ['key' => 'road_name', 'label' => __('Road Name'), 'import' => false, 'template' => true, 'derived' => true],
            ['key' => 'payment_for_month', 'label' => __('Transaction Month'), 'required' => true, 'date_hint' => 'Jun 2026'],
            ['key' => 'amount', 'label' => __('Current Month Payment').' ('.__('Taka').')', 'required' => true],
            ['key' => 'due_paid', 'label' => __('Previous Due Payment').' ('.__('Taka').')'],
            ['key' => 'payment_method', 'label' => __('Payment Method'), 'required' => true, 'dropdown' => array_values(config('bill_collection.payment_methods', []))],
            ['key' => 'payment_time', 'label' => __('Payment Time'), 'date_hint' => '02 Jun 2026 14:30'],
            ['key' => 'received_by_user_id', 'label' => __('Payment Received by'), 'dropdown' => SwmImportTemplateOptions::userLabels()],
            ['key' => 'receipt_no', 'label' => __('Receipt No.')],
        ];
    }

    /** @return array<int, array{key: string, label: string}> */
    protected function exportColumnDefinitions(): array
    {
        return [
            ['key' => 'holding_number', 'label' => __('Holding Number')],
            ['key' => 'customer_id', 'label' => __('Household ID')],
            ['key' => 'household_owner_name', 'label' => __('Household Owner Name')],
            ['key' => 'father_or_husband_name', 'label' => __("Father's/Husband's Name")],
            ['key' => 'ward', 'label' => __('Ward No.')],
            ['key' => 'sub_location', 'label' => __('Sub Location')],
            ['key' => 'contact_number', 'label' => __('Contact Number')],
            ['key' => 'receipt_no', 'label' => __('Receipt No.')],
            ['key' => 'amount', 'label' => __('Current Month Payment').' ('.__('Taka').')'],
            ['key' => 'due_paid', 'label' => __('Previous Due Payment').' ('.__('Taka').')'],
            ['key' => 'total_collected', 'label' => __('Total Payment').' ('.__('Taka').')'],
            ['key' => 'payment_for_month', 'label' => __('Transaction Month')],
            ['key' => 'payment_time', 'label' => __('Payment Time')],
            ['key' => 'payment_method', 'label' => __('Payment Method')],
            ['key' => 'received_by_name', 'label' => __('Payment Received by')],
        ];
    }

    /** @return array<int, array{key: string, label: string, required?: bool, dropdown?: array<int, string>}> */
    protected function importTemplateColumns(): array
    {
        return SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions());
    }

    /** @return array<int, array{key: string, label: string, required?: bool, dropdown?: array<int, string>}> */
    public function importColumnDefinitions(): array
    {
        return $this->importTemplateColumns();
    }

    /** @return array<int, string> */
    public function requiredImportLabels(): array
    {
        return SwmExcelColumns::requiredImportLabels($this->excelColumnDefinitions());
    }

    /** @return array<string, string> */
    protected function formOnlyValidationLabels(): array
    {
        return [
            'household_id' => __('Household'),
            'receipt_copy' => __('Payment Receipt Copy'),
        ];
    }
}
