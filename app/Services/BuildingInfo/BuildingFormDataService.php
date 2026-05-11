<?php

namespace App\Services\BuildingInfo;

use App\Helpers\KeywordMatcher;
use App\Models\BuildingInfo\BuildContain;
use App\Models\BuildingInfo\Building;
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

class BuildingFormDataService
{
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
        $building = Building::find($bin);
        if (!$building) {
            return null;
        }

        if (!empty($building->Owners)) {
            $building->owner_name = $building->Owners->owner_name;
            $building->owner_gender = $building->Owners->owner_gender;
            $building->owner_contact = $building->Owners->owner_contact;
            $building->nid = $building->Owners->nid;
        }

        $building->main_building = $building->building_associated_to ? false : true;
        $building->lic_status = $building->lic_id ? "1" : "0";
        if (in_array($building->sanitation_system_id, [9, 10, 12])) {
            $building->defecation_place = $building->sanitation_system_id;
        }
        $building->ctpt_name = $building->sharedToilets->pluck('id') ?? null;

        $drain_status = false;
        $sewer_status = false;
        if ($building->containments()->exists()) {
            foreach ($building->containments as $connectedContainment) {
                if (KeywordMatcher::matchKeywords($connectedContainment->containmentType->type, ["drain"])) {
                    $drain_status = true;
                }
                if (KeywordMatcher::matchKeywords($connectedContainment->containmentType->type, ["sewer"])) {
                    $sewer_status = true;
                }
            }
        }

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
}

