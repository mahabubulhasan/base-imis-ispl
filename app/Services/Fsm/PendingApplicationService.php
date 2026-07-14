<?php
// Last Modified: April 8, 2026
// Developed By: Streams Tech Ltd.
// Description: Provides pending application form metadata, validation, persistence, listing, and detail helper methods.

namespace App\Services\Fsm;

use App\Classes\FormField;
use App\Models\Fsm\Application;
use App\Models\LayerInfo\Ward;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PendingApplicationService
{
    protected string $indexRoute;
    protected string $createFormAction;
    protected array $createFormFields;

    public function __construct()
    {
        $this->indexRoute = route('pending-application.index');
        $this->createFormAction = route('pending-application.store');
        $this->createFormFields = [
            [
                'title' => __('Tax Information'),
                'fields' => [
                    new FormField(
                        label: __('Do you have a Tax Code?'),
                        labelFor: 'has_tax_id',
                        inputType: 'select',
                        inputId: 'has_tax_id',
                        selectValues: ['yes' => __('Yes'), 'no' => __('No')],
                        selectedValue: old('has_tax_id'),
                        placeholder: __('Please select'),
                        required: true,
                    ),
                    new FormField(
                        label: __('Tax Code'),
                        labelFor: 'tax_id',
                        inputType: 'text',
                        inputId: 'tax_id',
                        inputValue: old('tax_id'),
                        placeholder: __('ww-rrr-hhhh-xx'),
                        hidden: old('has_tax_id') !== 'yes',
                        oninput: 'formatPendingTaxId(this)',
                    ),
                ],
            ],
            [
                'title' => __('Applicant Information'),
                'fields' => [
                    new FormField(
                        label: __('Applicant Name'),
                        labelFor: 'customer_name',
                        inputType: 'text',
                        inputId: 'customer_name',
                        inputValue: old('customer_name'),
                        placeholder: __('Applicant Name'),
                        required: true,
                    ),
                    new FormField(
                        label: __('Contact No.'),
                        labelFor: 'customer_contact',
                        inputType: 'text',
                        inputId: 'customer_contact',
                        inputValue: old('customer_contact'),
                        placeholder: __('01#########'),
                        required: true,
                        oninput: 'formatPendingContact(this)',
                    ),
                    new FormField(
                        label: __('Holding Owner Name'),
                        labelFor: 'holding_owner_name',
                        inputType: 'text',
                        inputId: 'holding_owner_name',
                        inputValue: old('holding_owner_name'),
                        placeholder: __('Holding Owner Name'),
                    ),
                    new FormField(
                        label: __('Ward'),
                        labelFor: 'ward',
                        inputType: 'select',
                        inputId: 'ward',
                        selectValues: Ward::orderBy('ward')->pluck('ward', 'ward')->toArray(),
                        selectedValue: old('ward'),
                        placeholder: __('Select Ward'),
                        required: true,
                    ),
                    new FormField(
                        label: __('Address'),
                        labelFor: 'address',
                        inputType: 'textarea',
                        inputId: 'address',
                        inputValue: old('address'),
                        placeholder: __('Holding Number, Road Name, Ward'),
                        required: true,
                    ),
                ],
            ],
            [
                'title' => __('Service Info'),
                'fields' => [
                    new FormField(
                        label: __('Proposed Emptying Date'),
                        labelFor: 'proposed_emptying_date',
                        inputType: 'date',
                        inputId: 'proposed_emptying_date',
                        inputValue: old('proposed_emptying_date'),
                        placeholder: __('Proposed Emptying Date'),
                        required: true,
                    ),
                    new FormField(
                        label: __('Notes / Comments'),
                        labelFor: 'notes',
                        inputType: 'textarea',
                        inputId: 'notes',
                        inputValue: old('notes'),
                        placeholder: __('Additional Notes/Comments'),
                    ),
                ],
            ],
            [
                'title' => __('Payment Information'),
                'fields' => [
                    new FormField(
                        label: __('Amount (BDT)'),
                        labelFor: 'amount',
                        inputType: 'text',
                        inputId: 'amount',
                        inputValue: old('amount'),
                        placeholder: __('Amount (BDT)'),
                    ),
                ],
            ],
        ];
    }

    public function getIndexRoute(): string
    {
        return $this->indexRoute;
    }

    public function getCreateFormAction(): string
    {
        return $this->createFormAction;
    }

    public function getCreateFormFields(): array
    {
        return $this->createFormFields;
    }

    public function getPendingApplicationValidator(Request $request)
    {
        return Validator::make($request->all(), [
            'has_tax_id' => 'required|in:yes,no',
            'customer_name' => 'required|string|max:255',
            'customer_contact' => 'required|string|max:20',
            'holding_owner_name' => 'nullable|string|max:255',
            'ward' => 'required|string|max:50',
            'tax_id' => [
                'required_if:has_tax_id,yes',
                'nullable',
                'string',
                'max:50',
                'regex:/^\d{2}-\d{3}-\d{4}-\d{2}$/',
            ],
            'address' => 'required|string|max:500',
            'proposed_emptying_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'amount' => 'numeric',
        ], [
            'has_tax_id.required' => __('Please select if you have a Tax Code.'),
            'has_tax_id.in' => __('The Tax Code selection is invalid.'),
            'customer_name.required' => __('Applicant Name is required.'),
            'customer_contact.required' => __('Contact No. is required.'),
            'ward.required' => __('Ward is required.'),
            'tax_id.required_if' => __('Tax Code is required when you indicate you have one.'),
            'tax_id.regex' => __('Tax Code format must be 00-000-0000-00.'),
            'address.required' => __('Address is required.'),
            'proposed_emptying_date.required' => __('Proposed Emptying Date is required.'),
            'proposed_emptying_date.after_or_equal' => __('Proposed Emptying Date must be today or a future date.'),
            'latitude.numeric' => __('Latitude must be a valid number.'),
            'latitude.between' => __('Latitude must be between -90 and 90.'),
            'longitude.numeric' => __('Longitude must be a valid number.'),
            'longitude.between' => __('Longitude must be between -180 and 180.'),
            'amount.numeric' => __('Amount must be a valid number.'),
        ]);
    }

    public function createPendingApplication(Request $request, string $successRoute, string $successMessage): Redirector|RedirectResponse
    {
        $validator = $this->getPendingApplicationValidator($request);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $application = $this->storePendingApplication($validator->validated());
            // Store application ID in session for later retrieval
            session(['created_application_id' => $application->id]);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('Error! Application couldn\'t be created.'));
        }

        return redirect()->route($successRoute)->with('success', $successMessage);
    }

    public function storePendingApplication(array $validated): Application
    {
        DB::beginTransaction();
        $application = Application::create([
            'tax_code' => ($validated['has_tax_id'] ?? 'no') === 'yes' ? ($validated['tax_id'] ?? null) : null,
            'applicant_name' => $validated['customer_name'],
            'applicant_contact' => $validated['customer_contact'],
            'customer_name' => $validated['holding_owner_name'] ?? null,
            'ward' => $validated['ward'],
            'address' => $validated['address'],
            'proposed_emptying_date' => $validated['proposed_emptying_date'],
            'note' => $validated['notes'] ?? null,
            'approved_status' => false,
            'application_date' => now()->format('Y-m-d H:i:s'),
        ]);

        // Create payment record only if cash_in_hand payment method is selected
        if(!empty($validated['payment_method']) && $validated['payment_method'] === 'cash_in_hand') {
            Payment::cashInHandPayment(
                $validated['customer_name'],
                $validated['customer_contact'],
                $validated['holding_owner_name'] ?? null,
                $validated['address'],
                $validated['tax_id'] ?? null,
                $application->id,
                $validated['proposed_emptying_date'],
                (int)($validated['amount'] ?? 1500)
            );
        }

        DB::commit();
        return $application;
    }

    public function getPendingApplicationsQuery(Request $request): Builder
    {
        $query = Application::with('payment');

        if ($request->filled('tax_id')) {
            $query->where('tax_code', 'ILIKE', '%' . trim($request->tax_id) . '%');
        }

        if ($request->filled('customer_name')) {
            $query->where('applicant_name', 'ILIKE', '%' . trim($request->customer_name) . '%');
        }

        if ($request->filled('ward')) {
            $query->where('ward', 'ILIKE', '%' . trim($request->ward) . '%');
        }

        if ($request->filled('customer_contact')) {
            $query->where('applicant_contact', 'ILIKE', '%' . trim($request->customer_contact) . '%');
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

                if($pendingApplication->payment) {
                    $actions .= '<a href="' . route('payment.receipt', $pendingApplication->payment->transaction_id) . '" class="btn btn-primary btn-sm" title="' . __('View Receipt') . '"><i class="fas fa-receipt"></i></a>';
                }

                return $actions;
            })
            ->addColumn('payment_status', function (Application $pendingApplication) {
                return $pendingApplication->payment ? ucfirst($pendingApplication->payment->transaction_status) : __('Not Paid');
            })
            ->editColumn('applicant_contact', function (Application $pendingApplication) {
                $contact = $pendingApplication->applicant_contact ?: '-';
                if ($contact !== '-' && strlen($contact) === 10 && is_numeric($contact)) {
                    $contact = '0' . $contact;
                }
                return $contact;
            })
            ->editColumn('proposed_emptying_date', function (Application $pendingApplication) {
                return $pendingApplication->proposed_emptying_date ? $pendingApplication->proposed_emptying_date->format('Y-m-d') : '-';
            })
            ->editColumn('application_date', function (Application $pendingApplication) {
                return $pendingApplication->application_date ? $pendingApplication->application_date->format('Y-m-d') : '-';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getShowData(Application $pendingApplication): array
    {
        return [
            __('ID') => $pendingApplication->id,
            __('Tax Code') => $pendingApplication->tax_code ?: '-',
            __('Applicant Name') => $pendingApplication->applicant_name ?: '-',
            __('Applicant Contact') => $this->formatContact($pendingApplication->applicant_contact),
            __('Holding Owner Name') => $pendingApplication->customer_name ?: '-',
            __('Ward') => $pendingApplication->ward ?: '-',
            __('Address') => $pendingApplication->address ?: '-',
            __('Proposed Emptying Date') => $pendingApplication->proposed_emptying_date ? $pendingApplication->proposed_emptying_date->format('Y-m-d') : '-',
            __('Notes') => $pendingApplication->note ?: '-',
            __('Approval Status') => $pendingApplication->approved_status ? __('Approved') : __('Pending'),
            __('Application Date') => optional($pendingApplication->application_date)->format('Y-m-d') ?: '-',
        ];
    }

    private function formatContact(?string $contact): string
    {
        if (!$contact) {
            return '-';
        }

        if (strlen($contact) === 10 && is_numeric($contact)) {
            return '0' . $contact;
        }

        return $contact;
    }
}
