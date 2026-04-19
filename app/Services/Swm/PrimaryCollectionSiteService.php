<?php

namespace App\Services\Swm;

use App\Models\BuildingInfo\Building;
use App\Models\Swm\PrimaryCollectionSite;
use App\Models\Swm\Worker;
use App\Models\UtilityInfo\Roadline;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Yajra\DataTables\DataTables;

class PrimaryCollectionSiteService
{
    public function getAllPrimaryCollectionSites(array $data)
    {
        $query = PrimaryCollectionSite::query()->whereNull('deleted_at');

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['customer_id'] ?? null)) {
                    $q->where('customer_id', 'ILIKE', '%'.trim((string) $data['customer_id']).'%');
                }
                if (! empty($data['customer_name'] ?? null)) {
                    $q->where('customer_name', 'ILIKE', '%'.trim((string) $data['customer_name']).'%');
                }
                if (! empty($data['contact_number'] ?? null)) {
                    $q->where('contact_number', 'ILIKE', '%'.trim((string) $data['contact_number']).'%');
                }
                if (! empty($data['bin'] ?? null)) {
                    $q->where('bin', 'ILIKE', '%'.trim((string) $data['bin']).'%');
                }
                if (array_key_exists('is_lic', $data) && $data['is_lic'] !== '' && $data['is_lic'] !== null) {
                    $q->where('is_lic', filter_var($data['is_lic'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['is_lic']);
                }
                if (! empty($data['lic_id'] ?? null)) {
                    $q->where('lic_id', 'ILIKE', '%'.trim((string) $data['lic_id']).'%');
                }
                if (! empty($data['survey_date'] ?? null)) {
                    $q->whereDate('survey_date', $data['survey_date']);
                }
            })
            ->editColumn('is_owner', fn ($m) => $m->is_owner ? __('Yes') : __('No'))
            ->editColumn('is_lic', fn ($m) => $m->is_lic ? __('Yes') : __('No'))
            ->editColumn('segregation_practiced', fn ($m) => $m->segregation_practiced ? __('Yes') : __('No'))
            ->editColumn('waste_bin_provided', fn ($m) => $m->waste_bin_provided ? __('Yes') : __('No'))
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.primary-collection-sites.destroy', $model->id]]);

                if (Auth::user()->can('Edit SWM Primary Collection Site')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\PrimaryCollectionSiteController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SWM Primary Collection Site')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\PrimaryCollectionSiteController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SWM Primary Collection Site History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\PrimaryCollectionSiteController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SWM Primary Collection Site')) {
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
        $building = Building::query()->with(['functionalUse'])->find($data['bin']);
        if (! $building) {
            return null;
        }
        $roadCode = trim((string) ($building->road_code ?? ''));
        $roadName = null;
        if ($roadCode !== '' && $roadCode !== '0') {
            $roadName = Roadline::query()
                ->where('code', $roadCode)
                ->whereNull('deleted_at')
                ->value('name');
        }

        if (is_null($id)) {
            $site = new PrimaryCollectionSite();
        } else {
            $site = PrimaryCollectionSite::find($id);
            if (! $site) {
                return null;
            }
        }

        $vanPuller = null;
        if (! empty($data['van_puller_id'] ?? null)) {
            $vanPuller = Worker::query()->whereNull('deleted_at')->find($data['van_puller_id']);
        }

        $site->customer_id = $data['customer_id'] ?? null;
        $site->customer_name = $data['customer_name'] ?? null;
        $site->contact_number = $data['contact_number'] ?? null;
        $site->area_mohalla_name = $data['area_mohalla_name'] ?? null;
        $site->bin = $building->bin;
        $site->ward = $building->ward;
        $site->road_no_name = $roadName;
        $site->holding_number = $building->house_number;
        $site->tax_id = $data['tax_id'] ?? $building->tax_code;
        $site->waste_charge = $data['waste_charge'] ?? null;
        $site->is_owner = (bool) ($data['is_owner'] ?? false);
        $site->functional_use = $data['functional_use'] ?? optional($building->functionalUse)->name;
        $site->is_lic = (bool) ($data['is_lic'] ?? false);
        $site->lic_id = $data['lic_id'] ?? null;
        $site->number_of_family_members = $data['number_of_family_members'] ?? null;
        $site->using_this_service_since = $data['using_this_service_since'] ?? null;
        $site->segregation_practiced = (bool) ($data['segregation_practiced'] ?? false);
        $site->waste_bin_provided = (bool) ($data['waste_bin_provided'] ?? false);
        $site->daily_waste_volume = $data['daily_waste_volume'] ?? null;
        $site->remarks = $data['remarks'] ?? null;
        $site->survey_date = $data['survey_date'] ?? null;
        $site->van_puller_id = $data['van_puller_id'] ?? null;
        $site->van_puller_name = $vanPuller ? $vanPuller->name : null;
        $site->save();

        return $site->id;
    }

    public function download(array $data): void
    {
        $columns = [
            __('Customer ID'),
            __('Customer Name'),
            __('Contact Number'),
            __('Ward'),
            __('Area / Mohalla Name'),
            __('Road No./Name'),
            __('Holding Number'),
            __('Tax ID'),
            __('BIN'),
            __('Waste Charge'),
            __('Owner'),
            __('Functional Use'),
            __('LIC'),
            __('LIC ID'),
            __('Number of Family Members'),
            __('Using This Service Since'),
            __('Segregation Practiced'),
            __('Waste Bin Provided'),
            __('Total Waste Volume (Daily Avg)'),
            __('Remarks'),
            __('Survey Date'),
            __('Van Puller ID'),
            __('Van Puller Name'),
        ];

        $query = PrimaryCollectionSite::query()->whereNull('deleted_at');

        if (! empty($data['customer_id'] ?? null)) {
            $query->where('customer_id', 'ILIKE', '%'.trim((string) $data['customer_id']).'%');
        }
        if (! empty($data['customer_name'] ?? null)) {
            $query->where('customer_name', 'ILIKE', '%'.trim((string) $data['customer_name']).'%');
        }
        if (! empty($data['contact_number'] ?? null)) {
            $query->where('contact_number', 'ILIKE', '%'.trim((string) $data['contact_number']).'%');
        }
        if (! empty($data['bin'] ?? null)) {
            $query->where('bin', 'ILIKE', '%'.trim((string) $data['bin']).'%');
        }
        if (array_key_exists('is_lic', $data) && $data['is_lic'] !== '' && $data['is_lic'] !== null) {
            $query->where('is_lic', filter_var($data['is_lic'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['is_lic']);
        }
        if (! empty($data['lic_id'] ?? null)) {
            $query->where('lic_id', 'ILIKE', '%'.trim((string) $data['lic_id']).'%');
        }
        if (! empty($data['survey_date'] ?? null)) {
            $query->whereDate('survey_date', $data['survey_date']);
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('SWM Primary Collection Sites.csv')
            ->addRowWithStyle($columns, $style);

        $query->orderBy('id')->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $writer->addRow([
                    $row->customer_id,
                    $row->customer_name,
                    $row->contact_number,
                    $row->ward,
                    $row->area_mohalla_name,
                    $row->road_no_name,
                    $row->holding_number,
                    $row->tax_id,
                    $row->bin,
                    $row->waste_charge,
                    $row->is_owner ? __('Yes') : __('No'),
                    $row->functional_use,
                    $row->is_lic ? __('Yes') : __('No'),
                    $row->lic_id,
                    $row->number_of_family_members,
                    $row->using_this_service_since,
                    $row->segregation_practiced ? __('Yes') : __('No'),
                    $row->waste_bin_provided ? __('Yes') : __('No'),
                    $row->daily_waste_volume,
                    $row->remarks,
                    $row->survey_date,
                    $row->van_puller_id,
                    $row->van_puller_name,
                ]);
            }
        });

        $writer->close();
    }
}
