<?php

namespace App\Services\Swm;

use App\Enums\SwmOrganizationStatus;
use App\Models\Swm\Organization;
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
        $query = Organization::query()->whereNull('deleted_at');

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
                if (! empty($data['organization_category'] ?? null)) {
                    $q->where('organization_category', trim((string) $data['organization_category']));
                }
                if (! empty($data['organization_category_other'] ?? null)) {
                    $q->where('organization_category_other', 'ILIKE', '%'.trim((string) $data['organization_category_other']).'%');
                }
                if (array_key_exists('status', $data) && $data['status'] !== '' && $data['status'] !== null) {
                    $q->where('status', filter_var($data['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['status']);
                }
            })
            ->editColumn('organization_category', function ($model) {
                return $model->organization_category_label;
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
        $organization->organization_category = $data['organization_category'] ?? null;
        $organization->organization_category_other = ($data['organization_category'] ?? null) === Organization::CATEGORY_OTHER
            ? ($data['organization_category_other'] ?? null)
            : null;
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
        $organizationCategory = $data['organization_category'] ?? null;
        $organizationCategoryOther = $data['organization_category_other'] ?? null;
        $status = $data['status'] ?? null;

        $columns = [
            __('Organization Name'),
            __('Email'),
            __('Address'),
            __('Contact Person Name'),
            __('Contact Number'),
            __('Organization Category'),
            __('Organization Category (Others specify)'),
            __('Status'),
        ];

        $query = Organization::query()
            ->select(
                'name',
                'email',
                'address',
                'contact_person_name',
                'contact_number',
                'organization_category',
                'organization_category_other',
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
        if (! empty($organizationCategory)) {
            $query->where('organization_category', trim((string) $organizationCategory));
        }
        if (! empty($organizationCategoryOther)) {
            $query->where('organization_category_other', 'ILIKE', '%'.trim((string) $organizationCategoryOther).'%');
        }
        if ($status !== '' && $status !== null) {
            $query->where('status', filter_var($status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $status);
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('SW Organizations.csv')
            ->addRowWithStyle($columns, $style);

        $query->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $writer->addRow([
                    $row->name,
                    $row->email,
                    $row->address,
                    $row->contact_person_name,
                    $row->contact_number,
                    $row->organization_category_label,
                    $row->organization_category_other,
                    SwmOrganizationStatus::getDescription($row->status),
                ]);
            }
        });

        $writer->close();
    }
}
