<?php

namespace App\Services\BuildingInfo;

use App\Helpers\KeywordMatcher;
use App\Models\BuildingInfo\BuildContain;
use App\Models\BuildingInfo\Building;
use App\Models\BuildingInfo\BuildingSurvey;
use App\Models\BuildingInfo\FunctionalUse;
use App\Models\BuildingInfo\SanitationSystem;
use App\Models\BuildingInfo\StructureType;
use App\Models\BuildingInfo\UseCategory;
use App\Models\BuildingInfo\WaterSource;
use App\Models\Fsm\Containment;
use App\Models\Fsm\ContainmentType;
use App\Models\Fsm\Ctpt;
use App\Models\LayerInfo\Lic;
use App\Models\LayerInfo\Ward;
use App\Models\UtilityInfo\Drain;
use App\Models\UtilityInfo\Roadline;
use App\Models\UtilityInfo\SewerLine;
use App\Models\UtilityInfo\WaterSupplys;
use Illuminate\Support\Facades\File;

class BuildingFormDataService
{
    public const SURVEY_KNOWN_COLUMNS = [
        'temp_building_code',
        'tax_code',
        'collected_date',
        'road_code',
        'house_number',
        'functional_use_id',
        'use_category_id',
        'water_source_id',
        'sanitation_system_id',
        'sewer_code',
        'drain_code',
        'ward',
    ];

    public function getFormMetadata(): array
    {
        $options = $this->getCommonFormOptions();

        $ctpt = !empty($options['capitalizedctpt'])
            ? $options['capitalizedctpt']
            : $options['ctpt'];

        return [
            'ward' => $this->toArray($options['ward']),
            'road_code' => $this->toArray($options['road_code']),
            'structure_type' => $this->toArray($options['structure_type']),
            'functional_use' => $this->toArray($options['functional_use']),
            'usecatgs_json' => $this->toArray($options['use_category_id']),
            'water_source' => $this->toArray($options['water_source']),
            'toilet_connection' => $this->toArray($options['toiletConnection']),
            'defecation_place' => $this->toArray($options['defecationPlace']),
            'lic_names' => $this->toArray($options['licNames']),
            'ctpt' => $this->toArray($ctpt),
            'sewer_code' => $this->toArray($options['sewer_code']),
            'drain_code' => $this->toArray($options['drain_code']),
            'building_bin' => $this->toArray($options['buildingBin']),
            'preconnected_bin' => $this->toArray($options['bin']),
            'water_supply' => $this->toArray($options['waterSupply']),
        ];
    }

    public function getApiEditData(string $bin): ?array
    {
        $building = $this->findBuildingForEdit($bin);
        if (!$building) {
            return null;
        }

        $this->prepareBuildingForEdit($building);

        return [
            'building' => $this->serializeBuildingForApi($building),
            'buildingSurvey' => null,
            'containment' => $this->getBuildingContainments($building),
        ];
    }

    public function getCreateFormData(): array
    {
        return array_merge(
            $this->getCommonFormOptions(),
            [
                'building' => null,
                'buildingSurvey' => null,
                'containment_type' => ContainmentType::pluck('type', 'id')->all(),
                'drain_status' => false,
                'sewer_status' => false,
            ]
        );
    }

    public function getEditFormData(string $bin): ?array
    {
        $building = $this->findBuildingForEdit($bin);
        if (!$building) {
            return null;
        }

        $this->prepareBuildingForEdit($building);

        ['drain_status' => $drain_status, 'sewer_status' => $sewer_status] = $this->getDrainSewerStatus($building);

        return array_merge(
            $this->getCommonFormOptions(),
            [
                'building' => $building,
                'buildingSurvey' => null,
                'containment_type' => ContainmentType::pluck('type', 'id')->all(),
                'drain_status' => $drain_status,
                'sewer_status' => $sewer_status,
            ]
        );
    }

    public function getApproveFormData(int $id): ?array
    {
        $survey = BuildingSurvey::where('id', $id)
            ->where('is_enabled', true)
            ->whereNull('deleted_at')
            ->first();

        if (!$survey || !$survey->kml) {
            return null;
        }

        $kmlPath = storage_path('app/public/building-survey-kml/' . $survey->kml);
        if (!File::exists($kmlPath)) {
            return null;
        }

        return array_merge(
            $this->getCommonFormOptions(),
            [
                'building' => null,
                'buildingSurvey' => $survey,
                'surveyForm' => $this->buildSurveyFormData($survey),
                'containment_type' => ContainmentType::pluck('type', 'id')->all(),
                'drain_status' => false,
                'sewer_status' => false,
            ]
        );
    }

    public function buildSurveyFormData(BuildingSurvey $survey): object
    {
        return $this->prepareSurveyForApprove($this->flattenSurveyToFormData($survey), $survey);
    }

    public function flattenSurveyToFormData(BuildingSurvey $survey): array
    {
        $payload = $this->normalizeSurveyPayload($survey->payload_json);

        $columns = array_filter(
            $survey->only(self::SURVEY_KNOWN_COLUMNS),
            fn ($value) => $value !== null && $value !== ''
        );

        return array_merge($payload, $columns);
    }

    public function splitSurveyRequestData(array $input): array
    {
        $nestedPayload = [];
        if (isset($input['payload_json']) && is_array($input['payload_json'])) {
            $nestedPayload = $this->normalizeSurveyPayload($input['payload_json']);
        }

        $columns = [];
        foreach (self::SURVEY_KNOWN_COLUMNS as $column) {
            if (array_key_exists($column, $input) && $input[$column] !== null && $input[$column] !== '') {
                $columns[$column] = $input[$column];
            } elseif (array_key_exists($column, $nestedPayload) && $nestedPayload[$column] !== null && $nestedPayload[$column] !== '') {
                $columns[$column] = $nestedPayload[$column];
            }
        }

        $excludedFromPayload = array_merge(self::SURVEY_KNOWN_COLUMNS, ['kml', 'house_image', 'payload_json']);
        $flatPayload = collect($input)->except($excludedFromPayload)->toArray();
        $payloadData = array_merge($nestedPayload, $flatPayload);

        foreach (self::SURVEY_KNOWN_COLUMNS as $column) {
            unset($payloadData[$column]);
        }

        return [
            'columns' => $columns,
            'payload_json' => $payloadData,
        ];
    }

    private function normalizeSurveyPayload($payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        if (isset($payload['payload_json']) && is_array($payload['payload_json'])) {
            $nested = $payload['payload_json'];
            unset($payload['payload_json']);
            $payload = array_merge($payload, $nested);
        }

        return $payload;
    }

    private function prepareSurveyForApprove(array $data, BuildingSurvey $survey): object
    {
        if (empty($data['surveyed_date']) && $survey->collected_date) {
            $data['surveyed_date'] = $survey->collected_date->format('Y-m-d');
        } elseif (!empty($data['surveyed_date'])) {
            $data['surveyed_date'] = $this->formatDateValue($data['surveyed_date']);
        }

        if (array_key_exists('main_building', $data)) {
            $data['main_building'] = $this->coerceToFormBoolean($data['main_building']);
        } else {
            $data['main_building'] = empty($data['building_associated_to']);
        }

        if (array_key_exists('lic_status', $data)) {
            $data['lic_status'] = $this->coerceToLicStatus($data['lic_status']);
        } else {
            $data['lic_status'] = !empty($data['lic_id']) ? '1' : '0';
        }

        if (!empty($data['lic_id'])) {
            $data['low_income_hh'] = true;
        }

        foreach (['toilet_status', 'low_income_hh', 'well_presence_status', 'desludging_vehicle_accessible'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->coerceToFormBoolean($data[$field]);
            }
        }

        if (!$this->isTruthy($data['toilet_status'] ?? null)) {
            $sanitationId = isset($data['sanitation_system_id']) ? (int) $data['sanitation_system_id'] : null;
            if (in_array($sanitationId, [9, 10, 12], true)) {
                $data['defecation_place'] = $sanitationId;
            }
        }

        if (!empty($data['construction_year'])) {
            $data['construction_year'] = $this->formatDateValue($data['construction_year']);
        }

        return (object) $data;
    }

    private function formatDateValue($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return (string) $value;
    }

    private function coerceToFormBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function coerceToLicStatus($value): string
    {
        return $this->isTruthy($value) ? '1' : '0';
    }

    private function isTruthy($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array($value, [1, '1', true, 'true'], true);
    }

    private function findBuildingForEdit(string $bin): ?Building
    {
        return Building::find($bin);
    }

    private function prepareBuildingForEdit(Building $building): void
    {
        if (!empty($building->Owners)) {
            $building->owner_name = $building->Owners->owner_name;
            $building->owner_gender = $building->Owners->owner_gender;
            $building->owner_contact = $building->Owners->owner_contact;
            $building->nid = $building->Owners->nid;
        }

        $building->main_building = $building->building_associated_to ? false : true;
        $building->lic_status = $building->lic_id ? '1' : '0';

        if (in_array($building->sanitation_system_id, [9, 10, 12])) {
            $building->defecation_place = $building->sanitation_system_id;
        }

        $building->ctpt_name = $this->loadSharedToiletIds($building);
    }

    private function serializeBuildingForApi(Building $building): array
    {
        $data = $building->getAttributes();

        $data['owner_name'] = $building->owner_name ?? ($building->Owners->owner_name ?? null);
        $data['owner_gender'] = $building->owner_gender ?? ($building->Owners->owner_gender ?? null);
        $data['owner_contact'] = $building->owner_contact ?? ($building->Owners->owner_contact ?? null);
        $data['nid'] = $building->nid ?? ($building->Owners->nid ?? null);
        $data['main_building'] = $building->building_associated_to ? '0' : '1';
        $data['lic_status'] = $building->lic_status ?? ($building->lic_id ? '1' : '0');

        if (in_array($building->sanitation_system_id, [9, 10, 12])) {
            $data['defecation_place'] = (string) $building->sanitation_system_id;
        }

        $ctptIds = $this->loadSharedToiletIds($building)->filter()->values();
        $data['ctpt_name'] = $ctptIds->isNotEmpty() ? $ctptIds->implode(',') : null;

        $data['house_number'] = $data['house_number'] ?? $building->bin;

        unset($data['geom']);

        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $data[$key] = $value ? '1' : '0';
            }
        }

        return $data;
    }

    private function getBuildingContainments(Building $building): array
    {
        return $this->loadBuildingContainments($building)->map(function ($containment) {
            $type = $containment->containmentType;

            return [
                'containment_id' => $containment->id,
                'toilet_name' => $type->type ?? null,
                'sanitation_system' => $type->sanitation_system_id ?? null,
                'containment_volume' => $containment->size,
                'containment_location' => $containment->location,
            ];
        })->values()->all();
    }

    private function getDrainSewerStatus(Building $building): array
    {
        $drain_status = false;
        $sewer_status = false;

        foreach ($this->loadBuildingContainments($building) as $connectedContainment) {
            $type = $connectedContainment->containmentType->type ?? '';
            if (KeywordMatcher::matchKeywords($type, ['drain'])) {
                $drain_status = true;
            }
            if (KeywordMatcher::matchKeywords($type, ['sewer'])) {
                $sewer_status = true;
            }
        }

        return [
            'drain_status' => $drain_status,
            'sewer_status' => $sewer_status,
        ];
    }

    /**
     * Query-builder loads avoid eager-load key cast issues (bin is a string PK).
     */
    private function loadBuildingContainments(Building $building)
    {
        return $building->containments()->with('containmentType')->get();
    }

    private function loadSharedToiletIds(Building $building)
    {
        return $building->sharedToilets()->pluck('fsm.toilets.id');
    }

    private function getCommonFormOptions(): array
    {
        $structure_type = array_map('ucwords', StructureType::orderBy('type', 'asc')->pluck('type', 'id')->all());
        $water_source = moveOthersToEnd(array_map('ucwords', WaterSource::orderBy('source', 'asc')->pluck('source', 'id')->all()));
        $toiletConnection = SanitationSystem::whereNotIn('id', [9, 10, 12])->pluck('sanitation_system', 'id')->all();
        $defecationPlace = SanitationSystem::whereIn('id', [9, 10, 12])->pluck('sanitation_system', 'id')->all();
        $buildingBin = Building::distinct('bin')->pluck('bin', 'bin')->whereNull('building_associated_to')->whereNull('deleted_at');
        $bin = BuildContain::distinct('bin')->pluck('bin', 'bin')->whereNull('deleted_at');
        $ward = Ward::orderBy('ward')->pluck('ward', 'ward');
        $road_code = Roadline::get(['code', 'name'])->mapWithKeys(function ($item) {
            return [$item->code => ($item->name ? $item->code . ' - ' . $item->name : $item->code)];
        })->toArray();
        $sewer_code = SewerLine::pluck('code', 'code')->whereNull('deleted_at')->all();
        $containment_id = Containment::distinct('id')->pluck('id', 'id')->whereNull('deleted_at');
        $ctpt = Ctpt::where('status', true)
            ->where('type', 'Community Toilet')
            ->get(['id', 'name'])
            ->mapWithKeys(function ($item) {
                return [$item->id => ($item->name ? $item->id . ' - ' . $item->name : $item->id)];
            })
            ->toArray();
        $capitalizedctpt = array_map(function ($value) {
            return ucwords($value);
        }, $ctpt);
        $drain_code = Drain::pluck('code', 'code')->all();
        $licNames = Lic::whereNull('deleted_at')->orderBy('community_name')->pluck('community_name', 'id');
        $models = UseCategory::select('id', 'name', 'functional_use_id')->orderBy('name')->get();
        $functional_use = FunctionalUse::orderBy('name')->pluck('name', 'id')->all();
        $use_category_id = [];
        foreach ($models as $model) {
            $use_category_id[$model->functional_use_id][$model->id] = $model->name;
        }

        return [
            'buildingBin' => $buildingBin,
            'bin' => $bin,
            'containment_id' => $containment_id,
            'water_source' => $water_source,
            'structure_type' => $structure_type,
            'functional_use' => $functional_use,
            'usecatgsJson' => json_encode($use_category_id),
            'containment' => [],
            'road_code' => $road_code,
            'ward' => $ward,
            'sewer_code' => $sewer_code,
            'ctpt' => $ctpt,
            'drain_code' => $drain_code,
            'use_category_id' => $use_category_id,
            'licNames' => $licNames,
            'capitalizedctpt' => $capitalizedctpt,
            'toiletConnection' => $toiletConnection,
            'defecationPlace' => $defecationPlace,
            'waterSupply' => WaterSupplys::pluck('code', 'code'),
        ];
    }

    private function toArray($value): array
    {
        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->all();
        }

        return is_array($value) ? $value : (array) $value;
    }
}
