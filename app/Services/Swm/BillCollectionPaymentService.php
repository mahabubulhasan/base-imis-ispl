<?php

namespace App\Services\Swm;

use App\Models\Swm\BillCollectionPayment;
use App\Models\Swm\PrimaryCollectionSite;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class BillCollectionPaymentService
{
    /**
     * @return array<string, string> holding_number => label for Select2
     */
    public function searchHoldings(string $q, int $limit = 30): array
    {
        $q = trim($q);
        if (strlen($q) < 1) {
            return [];
        }

        $rows = PrimaryCollectionSite::query()
            ->whereNull('deleted_at')
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
        $query = PrimaryCollectionSite::query()
            ->whereNull('deleted_at')
            ->where('holding_number', $holdingNumber)
            ->orderBy('customer_id');

        if ($q !== null && $q !== '') {
            $term = '%'.trim($q).'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('customer_id', 'ILIKE', $term)
                    ->orWhere('customer_name', 'ILIKE', $term);
            });
        }

        return $query->get(['id', 'customer_id', 'customer_name', 'holding_number', 'waste_charge', 'using_this_service_since', 'survey_date'])
            ->map(fn ($site) => [
                'id' => $site->id,
                'text' => $site->customer_id.' — '.$site->customer_name,
                'customer_id' => $site->customer_id,
                'holding_number' => $site->holding_number,
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

        $query = PrimaryCollectionSite::query()
            ->whereNull('deleted_at');

        if ($holdings !== []) {
            $query->whereIn('holding_number', $holdings);
        } else {
            $term = trim((string) ($q ?? ''));
            if (strlen($term) < 2) {
                return [];
            }
            $like = '%'.$term.'%';
            $query->where(function ($sub) use ($like) {
                $sub->where('customer_id', 'ILIKE', $like)
                    ->orWhere('customer_name', 'ILIKE', $like);
            });
        }

        if ($q !== null && $q !== '' && $holdings !== []) {
            $like = '%'.trim((string) $q).'%';
            $query->where(function ($sub) use ($like) {
                $sub->where('customer_id', 'ILIKE', $like)
                    ->orWhere('customer_name', 'ILIKE', $like);
            });
        }

        return $query->orderBy('holding_number')
            ->orderBy('customer_id')
            ->limit($limit)
            ->get(['id', 'customer_id', 'customer_name', 'holding_number', 'waste_charge', 'using_this_service_since', 'survey_date'])
            ->map(fn ($site) => [
                'id' => $site->id,
                'text' => ($site->customer_id ?? '').' — '.($site->customer_name ?? '').($site->holding_number ? ' ('.$site->holding_number.')' : ''),
                'customer_id' => $site->customer_id,
                'holding_number' => $site->holding_number,
                'waste_charge' => $site->waste_charge,
                'using_this_service_since' => $site->using_this_service_since?->format('Y-m-d'),
                'survey_date' => $site->survey_date?->format('Y-m-d'),
            ])
            ->values()
            ->all();
    }

    public function billingAnchor(PrimaryCollectionSite $site): ?Carbon
    {
        if ($site->using_this_service_since) {
            return Carbon::parse($site->using_this_service_since)->startOfMonth();
        }
        if ($site->survey_date) {
            return Carbon::parse($site->survey_date)->startOfMonth();
        }

        return null;
    }

    public function billableMonthCount(PrimaryCollectionSite $site, Carbon $paymentMonthStart): int
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
     * @return array{waste_charge: ?string, billing_anchor: ?string, billable_month_count: int, cumulative_obligation: ?string, cumulative_paid_through: string, due: ?string}
     */
    public function balanceThroughMonth(PrimaryCollectionSite $site, Carbon $paymentForMonth, ?int $excludePaymentId = null): array
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

        $paidQuery = BillCollectionPayment::query()
            ->where('primary_collection_site_id', $site->id)
            ->whereNull('deleted_at')
            ->where('payment_for_month', '<=', $monthStart->toDateString());

        if ($excludePaymentId) {
            $paidQuery->where('id', '!=', $excludePaymentId);
        }

        $cumulativePaid = (string) $paidQuery->sum('amount');

        $due = null;
        if ($cumulativeObligation !== null) {
            $due = bcsub($cumulativeObligation, $cumulativePaid, 2);
            if (bccomp($due, '0', 2) < 0) {
                $due = '0.00';
            }
        }

        return [
            'waste_charge' => $wasteCharge !== null ? number_format((float) $wasteCharge, 2, '.', '') : null,
            'billing_anchor' => $anchor?->toDateString(),
            'billable_month_count' => $billableMonths,
            'cumulative_obligation' => $cumulativeObligation,
            'cumulative_paid_through' => $cumulativePaid,
            'due' => $due,
        ];
    }

    /**
     * Billable months gained when moving from the prior calendar month into {@see $monthStart} (0 or 1 with current rules).
     */
    public function marginalBillableUnitCount(PrimaryCollectionSite $site, Carbon $monthStart): int
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
    public function paidForPaymentMonth(PrimaryCollectionSite $site, Carbon $monthStart): string
    {
        $d = $monthStart->copy()->startOfMonth()->toDateString();
        $sum = BillCollectionPayment::query()
            ->where('primary_collection_site_id', $site->id)
            ->whereNull('deleted_at')
            ->whereDate('payment_for_month', $d)
            ->sum('amount');

        return number_format((float) $sum, 2, '.', '');
    }

    /**
     * Amount still owed for this calendar month only (marginal obligation for the month minus payments tagged to that month).
     */
    public function marginalDueForMonth(PrimaryCollectionSite $site, Carbon $monthStart): string
    {
        $w = $site->waste_charge;
        if ($w === null) {
            return '0.00';
        }
        $units = $this->marginalBillableUnitCount($site, $monthStart);
        if ($units <= 0) {
            return '0.00';
        }
        $obligation = bcmul((string) $w, (string) $units, 2);
        $paid = $this->paidForPaymentMonth($site, $monthStart);
        $due = bcsub($obligation, $paid, 2);
        if (bccomp($due, '0', 2) < 0) {
            return '0.00';
        }

        return $due;
    }

    protected function baseQuery(): Builder
    {
        $query = BillCollectionPayment::query()
            ->select('swm.bill_collection_payments.*')
            ->leftJoin('swm.primary_collection_sites as swm_pcs', 'swm.bill_collection_payments.primary_collection_site_id', '=', 'swm_pcs.id')
            ->leftJoin('auth.users as recv_user', 'swm.bill_collection_payments.received_by_user_id', '=', 'recv_user.id')
            ->addSelect([
                'swm_pcs.customer_name as site_customer_name',
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
                if (! empty($data['customer_id'] ?? null)) {
                    $q->where('swm.bill_collection_payments.customer_id', 'ILIKE', '%'.trim((string) $data['customer_id']).'%');
                }
                if (! empty($data['payment_for_month'] ?? null)) {
                    $q->whereDate('swm.bill_collection_payments.payment_for_month', Carbon::parse($data['payment_for_month'])->startOfMonth());
                }
            })
            ->orderColumn('site_customer_name', 'swm_pcs.customer_name $1')
            ->orderColumn('received_by_name', 'recv_user.name $1')
            ->orderColumn('holding_number', 'swm.bill_collection_payments.holding_number $1')
            ->orderColumn('customer_id', 'swm.bill_collection_payments.customer_id $1')
            ->orderColumn('amount', 'swm.bill_collection_payments.amount $1')
            ->orderColumn('payment_for_month', 'swm.bill_collection_payments.payment_for_month $1')
            ->orderColumn('payment_time', 'swm.bill_collection_payments.payment_time $1')
            ->editColumn('payment_for_month', function ($model) {
                return $model->payment_for_month?->format('Y-m-d') ?? '';
            })
            ->editColumn('payment_time', function ($model) {
                return $model->payment_time?->format('Y-m-d H:i') ?? '';
            })
            ->editColumn('amount', function ($model) {
                return number_format((float) $model->amount, 2, '.', '');
            })
            ->editColumn('payment_method', function ($model) {
                $methods = config('bill_collection.payment_methods', []);

                return $methods[$model->payment_method] ?? $model->payment_method;
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.bill-collection-payments.destroy', $model->id]]);

                if (Auth::user()->can('Edit SWM Bill Collection Payment')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\BillCollectionPaymentController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SWM Bill Collection Payment')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\BillCollectionPaymentController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SWM Bill Collection Payment History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\BillCollectionPaymentController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SWM Bill Collection Payment')) {
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
        $site = PrimaryCollectionSite::query()
            ->whereKey($data['primary_collection_site_id'] ?? null)
            ->whereNull('deleted_at')
            ->first();

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

        $payment->primary_collection_site_id = (int) $site->id;
        $payment->holding_number = $site->holding_number ?? '';
        $payment->customer_id = $site->customer_id;
        $payment->amount = $data['amount'] ?? 0;
        $payment->payment_for_month = Carbon::parse($data['payment_for_month'] ?? null)->startOfMonth();
        $payment->payment_time = isset($data['payment_time']) ? Carbon::parse($data['payment_time']) : now();
        $payment->payment_method = $data['payment_method'] ?? '';
        $payment->received_by_user_id = $data['received_by_user_id'] ?? Auth::id();
        if (array_key_exists('receipt_copy_path', $data)) {
            $payment->receipt_copy_path = $data['receipt_copy_path'];
        }
        $payment->save();

        return $payment->id;
    }

    public function download(array $data): void
    {
        $holdingNumber = $data['holding_number'] ?? null;
        $customerId = $data['customer_id'] ?? null;
        $paymentForMonth = $data['payment_for_month'] ?? null;

        $columns = [
            __('Holding Number'),
            __('Customer ID'),
            __('Customer Name'),
            __('Amount'),
            __('Payment For Month'),
            __('Payment Time'),
            __('Payment Method'),
            __('Received By'),
        ];

        $query = $this->baseQuery();

        if (! empty($holdingNumber)) {
            $query->where('swm.bill_collection_payments.holding_number', 'ILIKE', '%'.trim((string) $holdingNumber).'%');
        }
        if (! empty($customerId)) {
            $query->where('swm.bill_collection_payments.customer_id', 'ILIKE', '%'.trim((string) $customerId).'%');
        }
        if (! empty($paymentForMonth)) {
            $query->whereDate('swm.bill_collection_payments.payment_for_month', Carbon::parse($paymentForMonth)->startOfMonth());
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('SWM Bill Collection Payments.csv')
            ->addRowWithStyle($columns, $style);

        $query->orderBy('swm.bill_collection_payments.id')->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $methods = config('bill_collection.payment_methods', []);
                $methodLabel = $methods[$row->payment_method] ?? $row->payment_method;
                $writer->addRow([
                    $row->holding_number,
                    $row->customer_id,
                    $row->site_customer_name,
                    $row->amount,
                    $row->payment_for_month?->format('Y-m-d'),
                    $row->payment_time?->format('Y-m-d H:i:s'),
                    $methodLabel,
                    $row->received_by_name,
                ]);
            }
        });

        $writer->close();
    }
}
