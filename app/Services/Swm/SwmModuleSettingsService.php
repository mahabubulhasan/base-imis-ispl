<?php

namespace App\Services\Swm;

use App\Models\BuildingInfo\Household;
use App\Models\LayerInfo\Lic;
use App\Models\Swm\ModuleSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SwmModuleSettingsService
{
    public const DEFAULT_PER_CAPITA_KG_PER_DAY = 0.52;

    public function get(): ModuleSetting
    {
        return ModuleSetting::query()->firstOrCreate([], [
            'per_capita_sw_generation_kg_per_day' => self::DEFAULT_PER_CAPITA_KG_PER_DAY,
        ]);
    }

    public function perCapitaKgPerDay(): float
    {
        return (float) $this->get()->per_capita_sw_generation_kg_per_day;
    }

    /**
     * Municipality population: LIC community totals plus active non-LIC household members.
     */
    public function totalPopulationAsOf(Carbon $asOfEnd): float
    {
        $licPopulationTotal = (float) Lic::query()->sum('population_total');

        $nonLicActiveMembers = (float) DB::table('building_info.households')
            ->whereNull('deleted_at')
            ->where('created_at', '<=', $asOfEnd)
            ->selectRaw(
                'SUM(CASE WHEN status = ? AND (is_lic = false OR is_lic IS NULL) THEN COALESCE(number_of_family_members, 0) ELSE 0 END) as total',
                [Household::STATUS_ACTIVE]
            )
            ->value('total');

        return $licPopulationTotal + $nonLicActiveMembers;
    }

    public function update(array $data): ModuleSetting
    {
        $setting = $this->get();
        $setting->fill($data);
        $setting->save();

        return $setting;
    }

    /** @return array<string, string> */
    public function validationAttributeLabels(): array
    {
        return [
            'per_capita_sw_generation_kg_per_day' => __('Per Capita Waste Generation (Kg/day)'),
            'description' => __('Description'),
        ];
    }
}
