<?php

namespace App\Services\Swm;

use App\Enums\SwmOrganizationStatus;
use App\Models\LayerInfo\Ward;
use App\Models\Swm\Organization;
use App\Models\Swm\OrganizationType;
use App\Support\Swm\SwmExcelColumns;
use App\Support\Swm\SwmExcelFilename;
use App\Support\Swm\SwmExcelTemplateWriter;
use App\Support\Swm\SwmImportTemplateOptions;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Yajra\DataTables\DataTables;

class OrganizationService
{
    public function getAllOrganizations(array $data)
    {
        $query = Organization::query()
            ->with('organizationType')
            ->whereNull('deleted_at');

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['name'] ?? null)) {
                    $q->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
                }
                if (! empty($data['email'] ?? null)) {
                    $q->where('email', 'ILIKE', '%'.trim((string) $data['email']).'%');
                }
                if (! empty($data['address'] ?? null)) {
                    $q->where('address', 'ILIKE', '%'.trim((string) $data['address']).'%');
                }
                if (! empty($data['contact_person_name'] ?? null)) {
                    $q->where('contact_person_name', 'ILIKE', '%'.trim((string) $data['contact_person_name']).'%');
                }
                if (! empty($data['organization_type_id'] ?? null)) {
                    $q->where('organization_type_id', (int) $data['organization_type_id']);
                }
                if (array_key_exists('status', $data) && $data['status'] !== '' && $data['status'] !== null) {
                    $q->where('status', filter_var($data['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['status']);
                }
            })
            ->editColumn('organization_type', function ($model) {
                return $model->organization_type_label;
            })
            ->addColumn('service_wards_text', function ($model) {
                $wardLabels = Ward::getInAscOrder();

                return collect($model->service_wards ?? [])
                    ->map(fn ($wardId) => $wardLabels[$wardId] ?? $wardId)
                    ->implode(', ');
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.organizations.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Organization')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\OrganizationController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Organization')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\OrganizationController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Organization History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\OrganizationController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Organization')) {
                    $content .= '<a href="#" title="'.__('Delete').'" class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }

                $content .= \Form::close();

                return $content;
            })
            ->editColumn('status', function ($model) {
                return SwmOrganizationStatus::getDescription($model->status);
            })
            ->make(true);
    }

    public function storeOrUpdate(?int $id, array $data): ?int
    {
        if (is_null($id)) {
            $organization = new Organization();
        } else {
            $organization = Organization::find($id);
            if (! $organization) {
                return null;
            }
        }

        $organization->name = $data['name'] ?? null;
        $organization->email = $data['email'] ?? null;
        $organization->address = $data['address'] ?? null;
        $organization->contact_person_name = $data['contact_person_name'] ?? null;
        $organization->contact_number = $data['contact_number'] ?? null;
        $organization->organization_type_id = $data['organization_type_id'] ?? null;
        $organization->service_wards = $data['service_wards'] ?? null;
        $organization->remarks = $data['remarks'] ?? null;
        $organization->status = isset($data['status']) ? (bool) $data['status'] : false;
        $organization->save();

        return $organization->id;
    }

    public function download(array $data): void
    {
        $name = $data['name'] ?? null;
        $email = $data['email'] ?? null;
        $address = $data['address'] ?? null;
        $contactPersonName = $data['contact_person_name'] ?? null;
        $organizationTypeId = $data['organization_type_id'] ?? null;
        $status = $data['status'] ?? null;

        $columns = SwmExcelColumns::exportHeaders($this->excelColumnDefinitions());

        $query = Organization::query()
            ->with('organizationType')
            ->select(
                'name',
                'email',
                'address',
                'contact_person_name',
                'contact_number',
                'organization_type_id',
                'service_wards',
                'remarks',
                'status'
            )
            ->whereNull('deleted_at');

        if (! empty($name)) {
            $query->where('name', 'ILIKE', '%'.trim((string) $name).'%');
        }
        if (! empty($email)) {
            $query->where('email', 'ILIKE', '%'.trim((string) $email).'%');
        }
        if (! empty($address)) {
            $query->where('address', 'ILIKE', '%'.trim((string) $address).'%');
        }
        if (! empty($contactPersonName)) {
            $query->where('contact_person_name', 'ILIKE', '%'.trim((string) $contactPersonName).'%');
        }
        if (! empty($organizationTypeId)) {
            $query->where('organization_type_id', (int) $organizationTypeId);
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $status);
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToBrowser(SwmExcelFilename::export('organizations'))
            ->addRowWithStyle($columns, $style);

        $query->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $writer->addRow([
                    $row->name,
                    $row->email,
                    $row->address,
                    $row->contact_person_name,
                    $row->contact_number,
                    $row->organization_type_label,
                    is_array($row->service_wards) ? implode(', ', $row->service_wards) : '',
                    $row->remarks,
                    SwmOrganizationStatus::getDescription($row->status),
                ]);
            }
        });

        $writer->close();
    }

    public function downloadTemplate(): void
    {
        (new SwmExcelTemplateWriter())->download(
            SwmExcelFilename::importTemplate('organizations'),
            SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions())
        );
    }

    /** @return array<int, array{key: string, label: string, export?: bool, import?: bool, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}> */
    protected function excelColumnDefinitions(): array
    {
        $orgTypes = OrganizationType::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        return [
            ['key' => 'name', 'label' => __('Organization Name'), 'required' => true],
            ['key' => 'email', 'label' => __('Email'), 'required' => true],
            ['key' => 'address', 'label' => __('Address'), 'required' => true],
            ['key' => 'contact_person_name', 'label' => __('Contact Person Name'), 'required' => true],
            ['key' => 'contact_number', 'label' => __('Contact Number'), 'required' => true],
            ['key' => 'organization_type', 'label' => __('Organization Type'), 'required' => true, 'dropdown' => $orgTypes, 'export' => false],
            ['key' => 'organization_type_label', 'label' => __('Organization Type'), 'import' => false],
            [
                'key' => 'service_wards',
                'label' => __('Service Wards'),
                'multiselect' => true,
                'dropdown' => SwmImportTemplateOptions::wardNumberStrings(),
                'reference_key' => 'service_wards',
            ],
            ['key' => 'remarks', 'label' => __('Remarks')],
            ['key' => 'status', 'label' => __('Status'), 'required' => true, 'dropdown' => [
                SwmOrganizationStatus::getDescription(true),
                SwmOrganizationStatus::getDescription(false),
            ]],
        ];
    }

    /** @return array<int, array{key: string, label: string, required?: bool, dropdown?: array<int, string>}> */
    public function importTemplateColumns(): array
    {
        return SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions());
    }

    /** @return array<int, string> */
    public function requiredImportLabels(): array
    {
        return SwmExcelColumns::requiredImportLabels($this->excelColumnDefinitions());
    }
}
